<?php
/**
 * @filesource modules/booking/models/home.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Home;

use Kotchasan\Database\Sql;

/**
 * โมเดลสำหรับอ่านข้อมูลแสดงในหน้า  Home.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * อ่านรายการจองวันนี้
     *
     * @return int
     */
    public static function getNew()
    {
        $search = static::createQuery()
            ->selectCount()
            ->from('reservation')
            ->where(array(
                array('status', 1),
                Sql::BETWEEN(date('Y-m-d'), Sql::DATE('begin'), Sql::DATE('end')),
            ))
            ->execute();
        if (!empty($search)) {
            return $search[0]->count;
        }
        return 0;
    }

    /**
     * จำนวนห้องทั้งหมดที่เปิดใช้งาน
     *
     * @return int
     */
    public static function rooms()
    {
        $search = static::createQuery()
            ->selectCount()
            ->from('rooms')
            ->where(array('published', 1))
            ->execute();
        if (!empty($search)) {
            return $search[0]->count;
        }
        return 0;
    }
}
