<?php
/**
 * @filesource modules/booking/views/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Settings;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=booking-settings
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มตั้งค่า
     *
     * @return string
     */
    public function render()
    {
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/booking/model/settings/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Size of} {LNG_Image}',
        ));
        // booking_w
        $fieldset->add('text', array(
            'id' => 'booking_w',
            'labelClass' => 'g-input icon-width',
            'itemClass' => 'item',
            'label' => '{LNG_Width}',
            'comment' => '{LNG_Image size is in pixels} ({LNG_resized automatically})',
            'value' => isset(self::$cfg->booking_w) ? self::$cfg->booking_w : 500,
        ));
        // booking_status
        $fieldset->add('select', array(
            'id' => 'booking_status',
            'labelClass' => 'g-input icon-valid',
            'itemClass' => 'item',
            'label' => '{LNG_Initial booking status}',
            'options' => Language::get('BOOKING_STATUS'),
            'value' => isset(self::$cfg->booking_status) ? self::$cfg->booking_status : 0,
        ));
        // booking_approving
        $fieldset->add('select', array(
            'id' => 'booking_approving',
            'labelClass' => 'g-input icon-write',
            'itemClass' => 'item',
            'label' => '{LNG_Approving/editing reservations}',
            'options' => Language::get('APPROVING_RESERVATIONS'),
            'value' => isset(self::$cfg->booking_approving) ? self::$cfg->booking_approving : 0,
        ));
        // booking_notifications
        $fieldset->add('select', array(
            'id' => 'booking_notifications',
            'labelClass' => 'g-input icon-email',
            'itemClass' => 'item',
            'label' => '{LNG_Notification}',
            'comment' => '{LNG_Notify relevant parties when booking details are modified by customers}',
            'options' => Language::get('BOOLEANS'),
            'value' => isset(self::$cfg->booking_notifications) ? self::$cfg->booking_notifications : 0,
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}',
        ));
        // คืนค่า HTML
        return $form->render();
    }
}
