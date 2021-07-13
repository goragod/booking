<?php
/**
 * @filesource modules/booking/controllers/order.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Order;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=booking-order
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * รายละเอียดการจอง (admin)
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
        $this->menu = 'report';
        // ตรวจสอบรายการที่เลือก
        $index = \Booking\Order\Model::get($request->request('id')->toInt());
        // สมาชิก
        $login = Login::isMember();
        // สามารถอนุมัติห้องประชุมได้
        if ($index && Login::checkPermission($login, 'can_approve_room')) {
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
            $ul->appendChild('<li><span>{LNG_Detail}</span></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-write">'.$this->title.'</h2>',
            ));
            // แสดงฟอร์ม
            $section->appendChild(\Booking\Order\View::create()->render($index));
            // คืนค่า HTML
            return $section->render();
        }
        // 404
        return \Index\Error\Controller::execute($this, $request->getUri());
    }
}
