<?php
/**
 * @filesource modules/booking/models/write.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Write;

use Gcms\Login;
use Kotchasan\File;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=booking-write
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
     * คืนค่าข้อมูล object ไม่พบคืนค่า null.
     *
     * @param int  $id     ID
     * @param bool $new_id true คืนค่า ID ของรายการใหม่ (สำหรับการบันทึก), false คืนค่า ID หากเป็นรายการใหม่
     *
     * @return object|null
     */
    public static function get($id, $new_id = false)
    {
        // Model
        $model = new static();
        if (empty($id)) {
            // ใหม่

            return (object) array(
                'id' => $id,
                'new_id' => $new_id ? $model->db()->getNextId($model->getTableName('rooms')) : 0,
            );
        } else {
            // แก้ไข อ่านรายการที่เลือก
            $query = $model->db()->createQuery()
                ->from('rooms R')
                ->where(array('R.id', $id));
            $select = array('R.*');
            $n = 1;
            foreach (Language::get('ROOM_CUSTOM_TEXT', array()) as $key => $label) {
                $query->join('rooms_meta M'.$n, 'LEFT', array(array('M'.$n.'.room_id', 'R.id'), array('M'.$n.'.name', $key)));
                $select[] = 'M'.$n.'.value '.$key;
                ++$n;
            }

            return $query->first($select);
        }
    }

    /**
     * บันทึกข้อมูลที่ส่งมาจากฟอร์ม (write.php)
     *
     * @param Request $request
     */
    public function submit(Request $request)
    {
        $ret = array();
        // session, token, can_manage_room
        if ($request->initSession() && $request->isSafe() && $login = Login::isMember()) {
            if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_manage_room')) {
                try {
                    // ค่าที่ส่งมา
                    $save = array(
                        'name' => $request->post('name')->topic(),
                        'color' => $request->post('color')->filter('\#A-Z0-9'),
                        'detail' => $request->post('detail')->textarea(),
                    );
                    $metas = array();
                    foreach (Language::get('ROOM_CUSTOM_TEXT', array()) as $key => $label) {
                        $metas[$key] = $request->post($key)->topic();
                    }
                    $id = $request->post('id')->toInt();
                    // ตรวจสอบรายการที่เลือก
                    $index = self::get($id, $id == 0);
                    if (!$index) {
                        // ไม่พบ
                        $ret['alert'] = Language::get('Sorry, Item not found It&#39;s may be deleted');
                    } elseif ($save['name'] == '') {
                        // ไม่ได้กรอก name
                        $ret['ret_name'] = 'Please fill in';
                    } else {
                        if ($index->id == 0) {
                            $save['id'] = $index->new_id;
                        } else {
                            $save['id'] = $index->id;
                        }
                        // อัปโหลดไฟล์
                        foreach ($request->getUploadedFiles() as $item => $file) {
                            /* @var $file \Kotchasan\Http\UploadedFile */
                            if ($file->hasUploadFile()) {
                                $dir = ROOT_PATH.DATA_FOLDER.'booking/';
                                if (!File::makeDirectory($dir)) {
                                    // ไดเรคทอรี่ไม่สามารถสร้างได้
                                    $ret['ret_'.$item] = sprintf(Language::get('Directory %s cannot be created or is read-only.'), DATA_FOLDER.'booking/');
                                } elseif (!$file->validFileExt(array('jpg', 'jpeg', 'png'))) {
                                    // ชนิดของไฟล์ไม่ถูกต้อง
                                    $ret['ret_'.$item] = Language::get('The type of file is invalid');
                                } elseif ($item == 'picture') {
                                    try {
                                        $file->resizeImage(array('jpg', 'jpeg', 'png'), $dir, $save['id'].'.jpg', self::$cfg->booking_w);
                                    } catch (\Exception $exc) {
                                        // ไม่สามารถอัปโหลดได้
                                        $ret['ret_'.$item] = Language::get($exc->getMessage());
                                    }
                                }
                            } elseif ($file->hasError()) {
                                // ข้อผิดพลาดการอัปโหลด
                                $ret['ret_'.$item] = Language::get($file->getErrorMessage());
                            }
                        }
                        if (empty($ret)) {
                            if ($index->id == 0) {
                                // ใหม่
                                $this->db()->insert($this->getTableName('rooms'), $save);
                            } else {
                                // แก้ไข
                                $this->db()->update($this->getTableName('rooms'), $save['id'], $save);
                            }
                            // อัปเดต meta
                            $rooms_meta = $this->getTableName('rooms_meta');
                            $this->db()->delete($rooms_meta, array('room_id', $save['id']), 0);
                            foreach ($metas as $key => $value) {
                                if ($value != '') {
                                    $this->db()->insert($rooms_meta, array(
                                        'room_id' => $save['id'],
                                        'name' => $key,
                                        'value' => $value,
                                    ));
                                }
                            }
                            // คืนค่า
                            $ret['alert'] = Language::get('Saved successfully');
                            $ret['location'] = $request->getUri()->postBack('index.php', array('module' => 'booking-setup'));
                            // เคลียร์
                            $request->removeToken();
                        }
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
