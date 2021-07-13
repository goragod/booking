<?php
/**
 * @filesource modules/booking/controllers/booking.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Booking;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=booking-booking
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * จองห้องประชุม
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ข้อความ title bar
        $this->title = Language::get('Booking');
        // เลือกเมนู
        $this->menu = 'rooms';
        // สมาชิก
        $login = Login::isMember();
        // ตรวจสอบรายการที่เลือก
        $index = \Booking\Booking\Model::get($request->request('id')->toInt(), $request->request('room_id')->toInt(), $login);
        // ใหม่, เจ้าของ ยังไม่ได้อนุมัติ และ ไม่ใช่วันนี้
        if ($index && ($index->id == 0 || ($login['id'] == $index->member_id && $index->status == 0 && $index->today == 0))) {
            // ข้อความ title bar
            $title = Language::get($index->id == 0 ? 'Add New' : 'Edit');
            $this->menu = $index->id == 0 ? 'rooms' : 'booking';
            $this->title = $title.' '.$this->title;
            // แสดงผล
            $section = Html::create('section', array(
                'class' => 'content_bg',
            ));
            // breadcrumbs
            $breadcrumbs = $section->add('div', array(
                'class' => 'breadcrumbs',
            ));
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><span class="icon-calendar">{LNG_Room}</span></li>');
            $ul->appendChild('<li><a href="{BACKURL?module=booking-setup&id=0}">{LNG_Book a meeting}</a></li>');
            $ul->appendChild('<li><span>'.$title.'</span></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-write">'.$this->title.'</h2>',
            ));
            // แสดงฟอร์ม
            $section->appendChild(\Booking\Booking\View::create()->render($index, $login));
            // คืนค่า HTML
            return $section->render();
        }
        // 404
        return \Index\Error\Controller::execute($this, $request->getUri());
    }
}
