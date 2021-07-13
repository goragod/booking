<?php
/**
 * @filesource modules/index/controllers/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Settings;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Http\Uri;
use Kotchasan\Language;

/**
 * module=settings
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * ตั้งค่าเว็บไซต์
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ข้อความ title bar
        $this->title = Language::get('General site settings');
        // เลือกเมนู
        $this->menu = 'settings';
        // สมาชิก
        if ($login = Login::isMember()) {
            // เมนู settings
            $top_menu = self::$menus->getTopLvlMenu('settings');
            if (isset($top_menu['submenus'])) {
                if (count($top_menu['submenus']) == 1) {
                    $menu = reset($top_menu['submenus']);
                    if (isset($menu['url'])) {
                        $query = Uri::createFromUri($menu['url'])->parseQueryParams();
                        if (isset($query['module'])) {
                            $className = \Index\Main\Controller::parseModule($query['module']);
                            return createClass($className)->render($request->withQueryParams($query));
                        }
                    }
                } else {
                    // แสดง settings menu
                    return $this->tabMenus($request, $login);
                }
            }
        }
        // 404
        return \Index\Error\Controller::execute($this, $request->getUri());
    }

    /**
     * แสดงเมนู settings
     *
     * @param Request $request
     * @param array $login
     *
     * @return string
     */
    public function tabMenus(Request $request, $login)
    {
        // แสดงผล
        $section = Html::create('section', array(
            'class' => 'content_bg',
        ));
        // breadcrumbs
        $breadcrumbs = $section->add('div', array(
            'class' => 'breadcrumbs',
        ));
        $ul = $breadcrumbs->add('ul');
        $ul->appendChild('<li><span class="icon-menus">{LNG_Settings}</span></li>');
        $section->add('header', array(
            'innerHTML' => '<h2 class="icon-settings">'.$this->title.'</h2>',
        ));
        // menu
        $section->appendChild(\Index\Tabmenus\View::render($request, 'settings', 'settings'));
        // คืนค่า HTML
        return $section->render();
    }
}
