<?php
/**
 * @filesource modules/booking/views/home.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Home;

use Kotchasan\Html;

/**
 * หน้า Home
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * หน้า Home
     *
     * @param object $index
     * @param array  $login
     *
     * @return string
     */
    public function render($index, $login)
    {
        $section = Html::create('section');
        $section->add('h3', array(
            'innerHTML' => '<span class="icon-calendar">{LNG_Booking calendar} {LNG_Room}</span>',
        ));
        $div = $section->add('div', array(
            'class' => 'setup_frm',
        ));
        $div->add('div', array(
            'id' => 'booking-calendar',
            'class' => 'margin-left-right-bottom-top',
        ));
        // สีของห้องทั้งหมด
        $query = \Booking\Rooms\Model::toDataTable()->cacheOn();
        $rooms = '';
        foreach ($query->execute() as $item) {
            $rooms .= '<a id=room_'.$item->id.' style="background-color:'.$item->color.'">'.$item->name.'</a>';
        }
        $div->add('div', array(
            'id' => 'room_links',
            'class' => 'calendar_links clear',
            'innerHTML' => $rooms,
        ));
        /* Javascript */
        $section->script('initBookingCalendar();');
        // คืนค่า HTML
        return $section->render();
    }
}
