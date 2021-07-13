<?php
/**
 * @filesource modules/booking/models/booking.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Booking;

use Gcms\Login;
use Kotchasan\Database\Sql;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=booking
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านข้อมูลรายการที่เลือก
     * ถ้า $id = 0 หมายถึงรายการใหม่
     * คืนค่าข้อมูล object ไม่พบคืนค่า null
     *
     * @param int   $id
     * @param int   $room_id
     * @param array $login
     *
     * @return object|null
     */
    public static function get($id, $room_id, $login)
    {
        if ($login) {
            if (empty($id)) {
                // ใหม่
                return (object) array(
                    'id' => 0,
                    'room_id' => $room_id,
                    'status' => 0,
                    'today' => 0,
                    'name' => $login['name'],
                    'member_id' => $login['id'],
                    'phone' => $login['phone'],
                );
            } else {
                // แก้ไข อ่านรายการที่เลือก
                $sql = Sql::create('(CASE WHEN NOW() BETWEEN V.`begin` AND V.`end` THEN 1 WHEN NOW() > V.`end` THEN 2 ELSE 0 END) AS `today`');
                $query = static::createQuery()
                    ->from('reservation V')
                    ->join('user U', 'LEFT', array('U.id', 'V.member_id'))
                    ->where(array('V.id', $id));
                $select = array('V.*', 'U.name', 'U.phone', $sql);
                $n = 1;
                foreach (Language::get('BOOKING_SELECT', array()) + Language::get('BOOKING_OPTIONS', array()) as $key => $label) {
                    $query->join('reservation_data M'.$n, 'LEFT', array(array('M'.$n.'.reservation_id', 'V.id'), array('M'.$n.'.name', $key)));
                    $select[] = 'M'.$n.'.value '.$key;
                    ++$n;
                }
                return $query->first($select);
            }
        }
        // ไม่ได้เข้าระบบ
        return null;
    }

    /**
     * บันทึกข้อมูลที่ส่งมาจากฟอร์ม (booking.php)
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = array();
        // session, token, สมาชิก
        if ($request->initSession() && $request->isSafe()) {
            if ($login = Login::isMember()) {
                try {
                    // ค่าที่ส่งมา
                    $save = array(
                        'room_id' => $request->post('room_id')->toInt(),
                        'attendees' => $request->post('attendees')->toInt(),
                        'topic' => $request->post('topic')->topic(),
                        'comment' => $request->post('comment')->textarea(),
                    );
                    $begin_date = $request->post('begin_date')->date();
                    $begin_time = $request->post('begin_time')->time(true);
                    $end_date = $request->post('end_date')->date();
                    $end_time = $request->post('end_time')->time(true);
                    $user = array(
                        'phone' => $request->post('phone')->topic(),
                    );
                    $datas = array();
                    // ตัวแปรสำหรับตรวจสอบการแก้ไข
                    $options_check = array();
                    foreach (Language::get('BOOKING_SELECT', array()) as $key => $label) {
                        $options_check[] = $key;
                        $value = $request->post($key)->toInt();
                        if ($value > 0) {
                            $datas[$key] = $value;
                        }
                    }
                    foreach (Language::get('BOOKING_TEXT', array()) as $key => $label) {
                        $options_check[] = $key;
                        $value = $request->post($key)->topic();
                        if ($value != '') {
                            $datas[$key] = $value;
                        }
                    }
                    foreach (Language::get('BOOKING_OPTIONS', array()) as $key => $label) {
                        $options_check[] = $key;
                        $values = $request->post($key, array())->toInt();
                        if (!empty($values)) {
                            $datas[$key] = implode(',', $values);
                        }
                    }
                    // ตรวจสอบรายการที่เลือก
                    $index = self::get($request->post('id')->toInt(), 0, $login);
                    // ใหม่, เจ้าของ ยังไม่ได้อนุมัติ และ ไม่ใช่วันนี้
                    if ($index && ($index->id == 0 || ($login['id'] == $index->member_id && $index->status == 0 && $index->today == 0))) {
                        if ($save['attendees'] == 0) {
                            // ไม่ได้กรอก attendees
                            $ret['ret_attendees'] = 'Please fill in';
                        }
                        if ($save['topic'] == '') {
                            // ไม่ได้กรอก topic
                            $ret['ret_topic'] = 'Please fill in';
                        }
                        if (empty($begin_date)) {
                            // ไม่ได้กรอก begin_date
                            $ret['ret_begin_date'] = 'Please fill in';
                        }
                        if (preg_match('/^([0-9]{2,2}:[0-9]{2,2}):[0-9]{2,2}$/', $begin_time, $match)) {
                            $begin_time = $match[1].':01';
                        } else {
                            // ไม่ได้กรอก begin_time
                            $ret['ret_begin_time'] = 'Please fill in';
                        }
                        if (empty($end_date)) {
                            // ไม่ได้กรอก end_date
                            $ret['ret_end_date'] = 'Please fill in';
                        }
                        if (empty($end_time)) {
                            // ไม่ได้กรอก end_time
                            $ret['ret_end_time'] = 'Please fill in';
                        }
                        if ($end_date.$end_time > $begin_date.$begin_time) {
                            $save['begin'] = $begin_date.' '.$begin_time;
                            $save['end'] = $end_date.' '.$end_time;
                            // ตรวจสอบห้องว่าง
                            if (!\Booking\Checker\Model::availability($save)) {
                                $ret['ret_begin_date'] = Language::get('Meeting room are not available at select time');
                            }
                        } else {
                            // วันที่ ไม่ถูกต้อง
                            $ret['ret_end_date'] = Language::get('End date must be greater than begin date');
                        }
                        // ตาราง
                        $reservation_table = $this->getTableName('reservation');
                        $reservation_data = $this->getTableName('reservation_data');
                        // Database
                        $db = $this->db();
                        if (empty($ret)) {
                            if ($index->id == 0) {
                                // ใหม่
                                $save['status'] = self::$cfg->booking_status;
                                $save['member_id'] = $login['id'];
                                $save['create_date'] = date('Y-m-d H:i:s');
                                $index->id = $db->insert($reservation_table, $save);
                                // ใหม่ ส่งอีเมลเสมอ
                                $changed = true;
                            } else {
                                // แก้ไข
                                $db->update($reservation_table, $index->id, $save);
                                // สถานะปัจจุบัน
                                $save['status'] = $index->status;
                                // ตรวจสอบการแก้ไข
                                $changed = false;
                                if (self::$cfg->booking_notifications == 1) {
                                    if (!$changed) {
                                        foreach ($save as $key => $value) {
                                            if ($value != $index->{$key}) {
                                                $changed = true;
                                                break;
                                            }
                                        }
                                    }
                                    if (!$changed) {
                                        foreach ($options_check as $key) {
                                            if ($datas[$key] != $index->{$key}) {
                                                $changed = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            if ($index->phone != $user['phone']) {
                                if (self::$cfg->booking_notifications) {
                                    $changed = true;
                                }
                                // อัปเดตเบอร์โทรสมาชิก
                                $db->update($this->getTableName('user'), $login['id'], $user);
                            }
                            // รายละเอียดการจอง
                            $db->delete($reservation_data, array('reservation_id', $index->id), 0);
                            foreach ($datas as $key => $value) {
                                if ($value != '') {
                                    $db->insert($reservation_data, array(
                                        'reservation_id' => $index->id,
                                        'name' => $key,
                                        'value' => $value,
                                    ));
                                }
                            }
                            if (empty($ret) && $changed) {
                                // ใหม่ ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
                                $save['id'] = $index->id;
                                $ret['alert'] = \Booking\Email\Model::send($login['username'], $login['name'], $save);
                            } else {
                                // คืนค่า
                                $ret['alert'] = Language::get('Saved successfully');
                            }
                            $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'booking'));
                            // เคลียร์
                            $request->removeToken();
                        }
                    } else {
                        // ไม่พบ
                        $ret['alert'] = Language::get('Sorry, Item not found It&#39;s may be deleted');
                    }
                } catch (\Kotchasan\InputItemException $e) {
                    $ret['alert'] = $e->getMessage();
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่าเป็น JSON
        echo json_encode($ret);
    }
}
