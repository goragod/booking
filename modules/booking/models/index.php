<?php
/**
 * @filesource modules/booking/models/index.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Index;

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
     * Query ข้อมูลสำหรับส่งให้กับ DataTable
     *
     * @param array $params
     *
     * @return \Kotchasan\Database\QueryBuilder
     */
    public static function toDataTable($params)
    {
        $where = array(
            array('V.member_id', $params['member_id']),
        );
        if ($params['status'] > -1) {
            $where[] = array('V.status', $params['status']);
        }
        $sql = Sql::create('(CASE WHEN NOW() BETWEEN V.`begin` AND V.`end` THEN 1 WHEN NOW() > V.`end` THEN 2 ELSE 0 END) AS `today`');
        $select = array('V.id', 'V.topic', 'V.room_id', 'R.name');
        $query = static::createQuery()
            ->from('reservation V')
            ->join('rooms R', 'LEFT', array('R.id', 'V.room_id'));
        $n = 1;
        foreach (Language::get('BOOKING_SELECT', array()) as $key => $label) {
            $on = array(
                array('M'.$n.'.reservation_id', 'V.id'),
                array('M'.$n.'.name', $key),
            );
            if (!empty($params[$key])) {
                $where[] = array('M'.$n.'.value', $params[$key]);
            }
            $query->join('reservation_data M'.$n, 'LEFT', $on);
            $select[] = 'M'.$n.'.value '.$label;
            ++$n;
        }
        $select = array_merge($select, array('V.begin', 'V.end', 'V.status', 'V.reason', $sql, 'R.color'));
        return $query->select($select)->where($where);
    }

    /**
     * รับค่าจาก action (index.php)
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        $ret = array();
        // session, referer
        if ($request->initSession() && $request->isReferer()) {
            $action = $request->post('action')->toString();
            if ($action === 'cancel' && $login = Login::isMember()) {
                // ยกเลิกการจอง
                $reservation_table = $this->getTableName('reservation');
                $search = $this->db()->first($reservation_table, $request->post('id')->toInt());
                if ($search && $search->status == 0 && $login['id'] == $search->member_id) {
                    // ยกเลิกการจองโดยผู้จอง
                    $search->status = 3;
                    // อัปเดต
                    $this->db()->update($reservation_table, $search->id, array('status' => $search->status));
                    // ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
                    $ret['alert'] = \Booking\Email\Model::send($login['username'], $login['name'], (array) $search);
                    // reload
                    $ret['location'] = 'reload';
                }
            } elseif ($action === 'detail') {
                // แสดงรายละเอียดการจอง
                $search = $this->bookDetail($request->post('id')->toInt());
                if ($search) {
                    $ret['modal'] = \Booking\Detail\View::create()->booking($search);
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่า JSON
        echo json_encode($ret);
    }

    /**
     * อ่านข้อมูลรายการที่เลือก
     * คืนค่าข้อมูล object ไม่พบคืนค่า null
     *
     * @param int $id
     *
     * @return object|null
     */
    public function bookDetail($id)
    {
        $query = $this->db()->createQuery()
            ->from('reservation V')
            ->join('rooms R', 'LEFT', array('R.id', 'V.room_id'))
            ->join('user U', 'LEFT', array('U.id', 'V.member_id'))
            ->where(array('V.id', $id));
        $select = array('V.*', 'R.name', 'U.name contact', 'U.phone', 'R.color');
        $n = 1;
        foreach (Language::get('ROOM_CUSTOM_TEXT', array()) as $key => $label) {
            $query->join('rooms_meta M'.$n, 'LEFT', array(array('M'.$n.'.room_id', 'R.id'), array('M'.$n.'.name', $key)));
            $select[] = 'M'.$n.'.value '.$key;
            ++$n;
        }
        foreach (Language::get('BOOKING_SELECT', array()) + Language::get('BOOKING_OPTIONS', array()) + Language::get('BOOKING_TEXT', array()) as $key => $label) {
            $query->join('reservation_data M'.$n, 'LEFT', array(array('M'.$n.'.reservation_id', 'V.id'), array('M'.$n.'.name', $key)));
            $select[] = 'M'.$n.'.value '.$key;
            ++$n;
        }
        return $query->first($select);
    }
}
