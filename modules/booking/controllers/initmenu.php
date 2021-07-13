<?php
/**
 * @filesource modules/booking/controllers/initmenu.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Initmenu;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * Init Menu
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{
    /**
     * ฟังก์ชั่นเริ่มต้นการทำงานของโมดูลที่ติดตั้ง
     * และจัดการเมนูของโมดูล
     *
     * @param Request                $request
     * @param \Index\Menu\Controller $menu
     * @param array                  $login
     */
    public static function execute(Request $request, $menu, $login)
    {
        $menu->addTopLvlMenu('rooms', '{LNG_Book a meeting}', 'index.php?module=booking-rooms', null, 'module');
        if ($login) {
            $menu->addTopLvlMenu('booking', '{LNG_My Booking}', null, null, 'module');
            $menu->add('booking', '{LNG_Room}', 'index.php?module=booking', null, 'booking');
        }
        $submenus = array();
        // สามารถตั้งค่าระบบได้
        if (Login::checkPermission($login, 'can_config')) {
            $submenus['settings'] = array(
                'text' => '{LNG_Settings}',
                'url' => 'index.php?module=booking-settings',
            );
        }
        // สามารถจัดการห้องประชุมได้
        if (Login::checkPermission($login, 'can_manage_room')) {
            $submenus['setup'] = array(
                'text' => '{LNG_List of} {LNG_Room}',
                'url' => 'index.php?module=booking-setup',
            );
            foreach (Language::get('BOOKING_OPTIONS', array()) as $type => $text) {
                $submenus[] = array(
                    'text' => $text,
                    'url' => 'index.php?module=booking-categories&amp;type='.$type,
                );
            }
            foreach (Language::get('BOOKING_SELECT', array()) as $type => $text) {
                $submenus[] = array(
                    'text' => $text,
                    'url' => 'index.php?module=booking-categories&amp;type='.$type,
                );
            }
        }
        if (!empty($submenus)) {
            $menu->add('settings', '{LNG_Room}', null, $submenus, 'booking');
        }
        if (Login::checkPermission($login, 'can_approve_room')) {
            $menu->add('report', '{LNG_Book a meeting}', 'index.php?module=booking-report', null, 'booking');
        }
    }
}
