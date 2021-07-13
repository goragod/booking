<?php
/**
 * @filesource modules/booking/models/report.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Report;

use Gcms\Login;
use Kotchasan\Database\Sql;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=booking-report
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
        $where = array();
        if ($params['status'] > -1) {
            $where[] = array('V.status', $params['status']);
        }
        if ($params['room_id'] > 0) {
            $where[] = array('V.room_id', $params['room_id']);
        }
        if ($params['from'] != '') {
            $where[] = Sql::BETWEEN($params['from'], Sql::DATE('V.begin'), Sql::DATE('V.end'));
        }
        $select = array('V.id', 'V.topic', 'R.name', 'R.color');
        $query = static::createQuery()
            ->from('reservation V')
            ->join('rooms R', 'LEFT', array('R.id', 'V.room_id'))
            ->join('user U', 'LEFT', array('U.id', 'V.member_id'));
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
        $today = date('Y-m-d H:i:s');
        $select = array_merge($select, array(
            'U.name contact',
            'U.phone',
            'V.begin',
            'V.end',
            'V.create_date',
            'V.status',
            'V.reason',
            Sql::create('(CASE WHEN "'.$today.'" BETWEEN V.`begin` AND V.`end` THEN 1 WHEN "'.$today.'" > V.`end` THEN 2 ELSE 0 END) AS `today`'),
            Sql::create('TIMESTAMPDIFF(MINUTE,"'.$today.'",V.`begin`) AS `remain`'),
        ));
        return $query->select($select)->where($where);
    }

    /**
     * รับค่าจาก action (report.php)
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        $ret = array();
        // session, referer, สามารถอนุมัติได้
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_approve_room')) {
                // รับค่าจากการ POST
                $action = $request->post('action')->toString();
                // id ที่ส่งมา
                if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->toString(), $match)) {
                    if ($action === 'delete') {
                        // ลบ
                        $this->db()->delete($this->getTableName('reservation'), array('id', $match[1]), 0);
                        $this->db()->delete($this->getTableName('reservation_data'), array('reservation_id', $match[1]), 0);
                        // reload
                        $ret['location'] = 'reload';
                    }
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่า JSON
        echo json_encode($ret);
    }
}
