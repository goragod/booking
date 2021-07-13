<?php
/**
 * @filesource modules/booking/controllers/report.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Report;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=booking-report
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * รายงานการจอง (admin)
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ค่าที่ส่งมา
        $params = array(
            'from' => $request->request('from')->date(),
            'room_id' => $request->request('room_id')->toInt(),
            'use' => $request->request('use')->toInt(),
            'status' => $request->request('status', $request->cookie('bookingReport_status', -1)->toInt())->toInt(),
            'statuses' => Language::get('BOOKING_STATUS'),
        );
        setcookie('bookingReport_status', $params['status'], time() + 2592000, '/', HOST, HTTPS, true);
        // ข้อความ title bar
        $this->title = Language::get('Book a meeting');
        // เลือกเมนู
        $this->menu = 'report';
        // สมาชิก
        $login = Login::isMember();
        // สามารถอนุมัติห้องประชุมได้
        if (Login::checkPermission($login, 'can_approve_room')) {
            // ข้อความ title bar
            if (isset($params['statuses'][$params['status']])) {
                $title = $params['statuses'][$params['status']];
                $this->title .= ' '.$title;
            }
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
            $ul->appendChild('<li><span>{LNG_Report}</span></li>');
            if (isset($title)) {
                $ul->appendChild('<li><span>'.$title.'</span></li>');
            }
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-report">'.$this->title.'</h2>',
            ));
            // menu
            $section->appendChild(\Index\Tabmenus\View::render($request, 'report', 'booking'));
            // แสดงตาราง
            $section->appendChild(\Booking\Report\View::create()->render($request, $params));
            // คืนค่า HTML
            return $section->render();
        }
        // 404
        return \Index\Error\Controller::execute($this, $request->getUri());
    }
}
