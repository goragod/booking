<?php
/**
 * @filesource modules/booking/views/booking.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Booking;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=booking-booking
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มสร้าง/แก้ไข การจอง
     *
     * @param object $index
     * @param array  $login
     *
     * @return string
     */
    public function render($index, $login)
    {
        $form = Html::create('form', array(
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/booking/model/booking/submit',
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
            'value' => isset($index->room_id) ? $index->room_id : 0,
        ));
        // attendees
        $groups->add('number', array(
            'id' => 'attendees',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'width50',
            'label' => '{LNG_Attendees number}',
            'value' => isset($index->attendees) ? $index->attendees : null,
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
        // member_id
        $fieldset->add('hidden', array(
            'id' => 'member_id',
            'value' => $index->member_id,
        ));
        // phone
        $groups->add('text', array(
            'id' => 'phone',
            'labelClass' => 'g-input icon-phone',
            'itemClass' => 'width50',
            'label' => '{LNG_Phone}',
            'maxlength' => 32,
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
            'min' => date('Y-m-d'),
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
            'min' => date('Y-m-d'),
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
            'value' => isset($index->comment) ? $index->comment : '',
        ));
        $fieldset = $form->add('fieldset', array(
            'class' => 'submit',
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button ok large icon-save',
            'value' => '{LNG_Save}',
        ));
        // id
        $fieldset->add('hidden', array(
            'id' => 'id',
            'value' => $index->id,
        ));
        // Javascript
        $form->script('initBooking();');
        // คืนค่า HTML
        return $form->render();
    }
}
