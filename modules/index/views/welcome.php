<?php
/**
 * @filesource modules/index/views/welcome.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Welcome;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Http\Uri;
use Kotchasan\Language;
use Kotchasan\Template;

/**
 * Login, Forgot, Register
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\View
{
    /**
     * ฟอร์มเข้าระบบ
     *
     * @param Request $request
     *
     * @return object
     */
    public static function login(Request $request)
    {
        $login_action = $request->request('ret')->url();
        if ($login_action == '') {
            $ret_uri = Uri::createFromUri(WEB_URL.'index.php');
        } else {
            $ret_uri = Uri::createFromUri($login_action);
        }
        if (is_file(ROOT_PATH.DATA_FOLDER.'images/logo.png')) {
            $logo_image = '<img src="'.WEB_URL.DATA_FOLDER.'images/logo.png" alt="{WEBTITLE}">';
            $logo = $logo_image.'<span class=mobile>&nbsp;{WEBTITLE}</span>';
        } else {
            $logo_image = '';
            $logo = '<span class="'.self::$cfg->default_icon.'">{WEBTITLE}</span>';
        }
        $fields = array();
        foreach (Language::get('LOGIN_FIELDS') as $k => $label) {
            if (in_array($k, self::$cfg->login_fields)) {
                $fields[] = $label;
            }
        }
        // loginfrm.html
        $template = Template::create('', '', 'loginfrm');
        $template->add(array(
            '/{LOGO}/' => $logo,
            '/<FACEBOOK>(.*)<\/FACEBOOK>/s' => empty(self::$cfg->facebook_appId) ? '' : '\\1',
            '/<GOOGLE>(.*)<\/GOOGLE>/s' => empty(self::$cfg->google_client_id) ? '' : '\\1',
            '/{PLACEHOLDER}/' => implode('/', $fields),
            '/{TOKEN}/' => $request->createToken(),
            '/{EMAIL}/' => Login::$login_params['username'],
            '/{PASSWORD}/' => isset(Login::$login_params['password']) ? Login::$login_params['password'] : '',
            '/{MESSAGE}/' => Login::$login_message,
            '/{CLASS}/' => empty(Login::$login_message) ? 'hidden' : (empty(Login::$login_input) ? 'message' : 'error'),
            '/{URL}/' => $request->getUri()->withoutParams('action')->withoutQuery(array('module' => 'welcome')),
            '/{LOGINMENU}/' => self::menus('login'),
            '/{LOGIN_ACTION}/' => $ret_uri->withoutParams('action'),
        ));
        return (object) array(
            'detail' => $template->render(),
            'title' => self::$cfg->web_title.' - '.Language::get('Login with an existing account'),
            'bodyClass' => 'welcomepage',
        );
    }

    /**
     * ฟอร์มขอรหัสผ่านใหม่
     *
     * @param Request $request
     *
     * @return object
     */
    public static function forgot(Request $request)
    {
        // forgotfrm.html
        $template = Template::create('', '', 'forgotfrm');
        $template->add(array(
            '/{TOKEN}/' => $request->createToken(),
            '/{EMAIL}/' => Login::$login_params['username'],
            '/{MESSAGE}/' => Login::$login_message,
            '/{CLASS}/' => empty(Login::$login_message) ? 'hidden' : (empty(Login::$login_input) ? 'message' : 'error'),
            '/{LOGINMENU}/' => self::menus('forgot'),
        ));
        return (object) array(
            'detail' => $template->render(),
            'title' => self::$cfg->web_title.' - '.Language::get('Get new password'),
            'bodyClass' => 'welcomepage',
        );
    }

    /**
     * ฟอร์มสมัครสมาชิก
     *
     * @param Request $request
     *
     * @return object
     */
    public static function register(Request $request)
    {
        // registerfrm.html
        $template = Template::create('', '', 'registerfrm');
        $template->add(array(
            '/{Terms of Use}/' => '<a href="{WEBURL}index.php?module=page&amp;src=terms">{LNG_Terms of Use}</a>',
            '/{Privacy Policy}/' => '<a href="{WEBURL}index.php?module=page&amp;src=policy">{LNG_Privacy Policy}</a>',
            '/{TOKEN}/' => $request->createToken(),
            '/{LOGINMENU}/' => self::menus('register'),
        ));
        return (object) array(
            'detail' => $template->render(),
            'title' => self::$cfg->web_title.' - '.Language::get('Register'),
            'bodyClass' => 'welcomepage',
        );
    }

    /**
     * เมนูหน้าเข้าระบบ
     *
     * @param  $from
     *
     * @return string
     */
    public static function menus($from)
    {
        $menus = array();
        if (in_array($from, array('register', 'forgot'))) {
            $menus[] = '<a href="index.php?module=welcome&amp;action=login" target=_self>{LNG_Login}</a>';
        }
        if (in_array($from, array('forgot', 'login')) && !empty(self::$cfg->user_register)) {
            $menus[] = '<a href="index.php?module=welcome&amp;action=register" target=_self>{LNG_Register}</a>';
        }
        if (in_array($from, array('register', 'login')) && !empty(self::$cfg->user_forgot)) {
            $menus[] = '<a href="index.php?module=welcome&amp;action=forgot" target=_self>{LNG_Forgot}</a>';
        }
        return empty($menus) ? '' : implode('&nbsp;/&nbsp;', $menus);
    }
}
