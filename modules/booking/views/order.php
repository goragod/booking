<?php
/**
 * @filesource modules/booking/views/order.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Order;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=booking-order
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มแก้ไข การจอง (admin)
     *
     * @param object $index
     *
     * @return string
     */
    public function render($index)
    {
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/booking/model/order/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true,
        ));
        $fieldset = $form->add('fieldset', array(
            'title' => '{LNG_Details of} {LNG_Booking}',
        ));
        $groups = $fieldset->add('groups');
        // room_id
        $groups->add('select', array(
            'id' => 'room_id',
            'labelClass' => 'g-input icon-office',
            'itemClass' => 'width50',
            'label' => '{LNG_Room name}',
            'options' => \Booking\Rooms\Model::toSelect(),
            'value' => $index->room_id,
        ));
        // attendees
        $groups->add('number', array(
            'id' => 'attendees',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'width50',
            'label' => '{LNG_Attendees number}',
            'value' => $index->attendees,
        ));
        // topic
        $fieldset->add('text', array(
            'id' => 'topic',
            'labelClass' => 'g-input icon-edit',
            'itemClass' => 'item',
            'label' => '{LNG_Topic}',
            'maxlength' => 150,
            'value' => isset($index->topic) ? $index->topic : '',
        ));
        $groups = $fieldset->add('groups');
        // name
        $groups->add('text', array(
            'id' => 'name',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Contact name}',
            'disabled' => true,
            'value' => $index->name,
        ));
        // phone
        $groups->add('text', array(
            'id' => 'phone',
            'labelClass' => 'g-input icon-phone',
            'itemClass' => 'width50',
            'label' => '{LNG_Phone}',
            'disabled' => true,
            'value' => $index->phone,
        ));
        // ตัวเลือก select
        $category = \Booking\Category\Model::init();
        foreach (Language::get('BOOKING_SELECT', array()) as $key => $label) {
            $fieldset->add('select', array(
                'id' => $key,
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'item',
                'label' => $label,
                'options' => $category->toSelect($key),
                'value' => isset($index->{$key}) ? $index->{$key} : 0,
            ));
        }
        // textbox
        foreach (Language::get('BOOKING_TEXT', array()) as $key => $label) {
            $fieldset->add('text', array(
                'id' => $key,
                'labelClass' => 'g-input icon-edit',
                'itemClass' => 'item',
                'label' => $label,
                'maxlength' => 250,
                'value' => isset($index->{$key}) ? $index->{$key} : '',
            ));
        }
        // begin
        if (empty($index->begin)) {
            $begin_date = null;
            $begin_time = null;
        } else {
            $time = strtotime($index->begin);
            $begin_date = date('Y-m-d', $time);
            $begin_time = date('H:i', $time);
        }
        $groups = $fieldset->add('groups', array(
            'for' => 'begin_date',
            'label' => '{LNG_Begin date}/{LNG_Begin time}',
        ));
        // begin date
        $groups->add('date', array(
            'id' => 'begin_date',
            'labelClass' => 'g-input icon-calendar',
            'itemClass' => 'width50',
            'title' => '{LNG_Begin date}',
            'value' => $begin_date,
        ));
        // begin time
        $groups->add('time', array(
            'id' => 'begin_time',
            'labelClass' => 'g-input icon-clock',
            'itemClass' => 'width50',
            'title' => '{LNG_Begin time}',
            'value' => $begin_time,
        ));
        // end
        if (empty($index->end)) {
            $end_date = null;
            $end_time = null;
        } else {
            $time = strtotime($index->end);
            $end_date = date('Y-m-d', $time);
            $end_time = date('H:i', $time);
        }
        $groups = $fieldset->add('groups', array(
            'for' => 'end_date',
            'label' => '{LNG_End date}/{LNG_End time}',
        ));
        // end date
        $groups->add('date', array(
            'id' => 'end_date',
            'labelClass' => 'g-input icon-calendar',
            'itemClass' => 'width50',
            'title' => '{LNG_End date}',
            'value' => $end_date,
        ));
        // end time
        $groups->add('time', array(
            'id' => 'end_time',
            'labelClass' => 'g-input icon-clock',
            'itemClass' => 'width50',
            'title' => '{LNG_End time}',
            'value' => $end_time,
        ));
        // ตัวเลือก checkbox
        foreach (Language::get('BOOKING_OPTIONS', array()) as $key => $label) {
            $fieldset->add('checkboxgroups', array(
                'id' => $key,
                'labelClass' => 'g-input icon-category',
                'itemClass' => 'item',
                'label' => $label,
                'options' => $category->toSelect($key),
                'value' => isset($index->{$key}) ? explode(',', $index->{$key}) : array(),
            ));
        }
        // comment
        $fieldset->add('textarea', array(
            'id' => 'comment',
            'labelClass' => 'g-input icon-file',
            'itemClass' => 'item',
            'label' => '{LNG_Other}',
            'rows' => 3,
            'value' => $index->comment,
        ));
        // status
        $fieldset->add('select', array(
            'id' => 'status',
            'labelClass' => 'g-input icon-star0',
            'itemClass' => 'item',
            'label' => '{LNG_Status}',
            'options' => Language::get('BOOKING_STATUS'),
            'value' => $index->status,
        ));
        // reason
        $fieldset->add('text', array(
            'id' => 'reason',
            'labelClass' => 'g-input icon-question',
            'itemClass' => 'item',
            'label' => '{LNG_Reason}',
            'maxlength' => 128,
            'value' => $index->reason,
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button ok large icon-save',
            'value' => '{LNG_Save}',
        ));
        if (self::$cfg->noreply_email != '') {
            $fieldset->add('checkbox', array(
                'id' => 'send_mail',
                'label' => '&nbsp;{LNG_Email the relevant person}',
                'value' => 1,
            ));
        }
        // id
        $fieldset->add('hidden', array(
            'id' => 'id',
            'value' => $index->id,
        ));
        // Javascript
        $form->script('initBookingOrder();');
        // คืนค่า HTML
        return $form->render();
    }
}
