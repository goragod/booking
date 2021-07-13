<?php
/**
 * @filesource modules/index/models/login.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Login;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * Controller หลัก สำหรับแสดง frontend ของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * ฟังก์ชั่นตรวจสอบการ Login
     *
     * @param Request $request
     */
    public function chklogin(Request $request)
    {
        if ($request->initSession() && $request->isSafe()) {
            // ตรวจสอบการ login
            Login::create();
            // ตรวจสอบสมาชิก
            $login = Login::isMember();
            if ($login) {
                $ret = array(
                    'alert' => Language::replace('Welcome %s, login complete', array('%s' => $login['name'])),
                    'url' => $request->post('login_action')->toString(),
                );
                // เคลียร์
                $request->removeToken();
            } else {
                $ret = array(
                    'ret_'.Login::$login_input => Login::$login_message,
                );
            }
            // คืนค่า JSON
            echo json_encode($ret);
        }
    }
}
