<?php
/**
 * @filesource modules/booking/models/calendar.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Calendar;

use Kotchasan\Database\Sql;
use Kotchasan\Date;
use Kotchasan\Http\Request;
use Kotchasan\Text;

/**
 * คืนค่าข้อมูลปฏิทิน
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * คืนค่าข้อมูลปฏิทินเป็น JSON
     *
     * @param Request $request
     *
     * @return \static
     */
    public function toJSON(Request $request)
    {
        if ($request->initSession() && $request->isReferer() && $request->isAjax()) {
            // ค่าที่ส่งมา
            $year = $request->post('year')->toInt();
            $month = $request->post('month')->toInt();
            if ($month == 12) {
                $next = ($year + 1).'-1-1';
            } else {
                $next = $year.'-'.($month + 1).'-1';
            }
            // Query เดือนที่เลือก
            $query = \Kotchasan\Model::createQuery()
                ->select('V.id', 'V.topic', 'V.begin', 'V.end', 'R.color')
                ->from('reservation V')
                ->join('rooms R', 'INNER', array('R.id', 'V.room_id'))
                ->where(array('V.status', 1))
                ->andWhere(array(
                    Sql::create("(DATE(V.`begin`)<='$year-$month-1' AND DATE(V.`end`)>'$next')"),
                    Sql::create("(YEAR(V.`begin`)='$year' AND MONTH(V.`begin`)='$month')"),
                    Sql::create("(YEAR(V.`end`)='$year' AND MONTH(V.`end`)='$month')"),
                ), 'OR')
                ->order('V.begin')
                ->cacheOn();
            $events = array();
            foreach ($query->execute() as $item) {
                $events[] = array(
                    'id' => $item->id,
                    'title' => Date::format($item->begin, 'H:i').' '.Text::unhtmlspecialchars($item->topic),
                    'start' => $item->begin,
                    'end' => $item->end,
                    'color' => $item->color,
                );
            }
            // คืนค่า JSON
            echo json_encode($events);
        }
    }
}
