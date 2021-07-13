<?php
/**
 * @filesource modules/booking/views/tools.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Tools;

use Kotchasan\Date;

/**
 * ฟังก์ชั่นแสดงผล Booking
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View
{
    /**
     * คืนค่าช่วงเวลาจอง
     *
     * @param sting $begin
     * @param sting $end
     *
     * @return string
     */
    public static function toDate($begin, $end)
    {
        $begin_time = strtotime($begin);
        $end_time = strtotime($end);
        if (date('d M Y', $begin_time) == date('d M Y', $end_time)) {
            return Date::format($begin_time).' {LNG_To} '.Date::format($end_time, 'TIME_FORMAT');
        } else {
            return Date::format($begin_time).' {LNG_To} '.Date::format($end_time);
        }
    }
}
