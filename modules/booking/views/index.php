<?php
/**
 * @filesource modules/booking/views/index.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Index;

use Kotchasan\DataTable;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=booking-index
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * @var array
     */
    private $status;
    /**
     * @var object
     */
    private $category;
    /**
     * @var array
     */
    private $topic = array('topic' => '');

    /**
     * รายการจอง (ผู้จอง)
     *
     * @param Request $request
     * @param array   $login
     *
     * @return string
     */
    public function render(Request $request, $login)
    {
        $this->category = \Booking\Category\Model::init();
        $this->status = Language::get('BOOKING_STATUS');
        $hideColumns = array('id', 'today', 'end', 'phone', 'begin', 'color', 'room_id');
        // ค่าที่ส่งมา
        $params = array(
            'member_id' => $login['id'],
            'status' => $request->request('status', -1)->toInt(),
        );
        // filter
        $filters = array();
        foreach (Language::get('BOOKING_SELECT', array()) as $key => $label) {
            $params[$key] = $request->request($key)->toInt();
            $filters[] = array(
                'name' => $key,
                'text' => $label,
                'options' => array(0 => '{LNG_all items}') + $this->category->toSelect($key),
                'value' => $params[$key],
            );
            $this->topic[] = $label;
            $this->topic[$key] = '';
            $hideColumns[] = $label;
        }
        $filters[] = array(
            'name' => 'status',
            'text' => '{LNG_Status}',
            'options' => array(-1 => '{LNG_all items}') + $this->status,
            'value' => $params['status'],
        );
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable(array(
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Booking\Index\Model::toDataTable($params),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('bookingIndex_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => 'begin DESC',
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => array($this, 'onRow'),
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => $hideColumns,
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => array('name', 'topic'),
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/booking/model/index/action',
            'actionCallback' => 'dataTableActionCallback',
            /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
            'filters' => $filters,
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => array(
                'topic' => array(
                    'text' => '{LNG_Topic}',
                ),
                'name' => array(
                    'text' => '{LNG_Room name}',
                ),
                'begin' => array(
                    'text' => '{LNG_Begin date}',
                    'class' => 'center',
                ),
                'end' => array(
                    'text' => '{LNG_End date}',
                    'class' => 'center',
                ),
                'status' => array(
                    'text' => '{LNG_Status}',
                    'class' => 'center',
                ),
                'reason' => array(
                    'text' => '{LNG_Reason}',
                ),
            ),
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => array(
                'topic' => array(
                    'class' => 'topic',
                ),
                'name' => array(
                    'class' => 'topic',
                ),
                'begin' => array(
                    'class' => 'center',
                ),
                'end' => array(
                    'class' => 'center',
                ),
                'status' => array(
                    'class' => 'center',
                ),
            ),
            /* ฟังก์ชั่นตรวจสอบการแสดงผลปุ่มในแถว */
            'onCreateButton' => array($this, 'onCreateButton'),
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => array(
                'cancel' => array(
                    'class' => 'icon-warning button cancel',
                    'id' => ':id',
                    'text' => '{LNG_Cancel}',
                ),
                'edit' => array(
                    'class' => 'icon-edit button green',
                    'href' => $uri->createBackUri(array('module' => 'booking-booking', 'id' => ':id')),
                    'text' => '{LNG_Edit}',
                ),
                'detail' => array(
                    'class' => 'icon-info button orange',
                    'id' => ':id',
                    'text' => '{LNG_Detail}',
                ),
            ),
            /* ปุ่มเพิ่ม */
            'addNew' => array(
                'class' => 'float_button icon-addtocart',
                'href' => 'index.php?module=booking-booking',
                'title' => '{LNG_Book a meeting}',
            ),
        ));
        // save cookie
        setcookie('bookingIndex_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        // คืนค่า HTML
        return $table->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว
     *
     * @param array  $item ข้อมูลแถว
     * @param int    $o    ID ของข้อมูล
     * @param object $prop กำหนด properties ของ TR
     *
     * @return array
     */
    public function onRow($item, $o, $prop)
    {
        if ($item['today'] == 1) {
            $prop->class = 'bg3';
        }
        $item['name'] = '<span class="term" style="background-color:'.$item['color'].'">'.$item['name'].'</span><br>';
        $item['name'] .= \Booking\Tools\View::toDate($item['begin'], $item['end']);
        $this->topic['topic'] = '<b>'.$item['topic'].'</b>';
        foreach ($this->category->items() as $k => $v) {
            if (isset($item[$v])) {
                $this->topic[$k] = $this->category->get($k, $item[$v]);
            }
        }
        $item['topic'] = implode(' ', $this->topic);
        $item['status'] = '<span class="term'.$item['status'].'">'.$this->status[$item['status']].'</span>';
        return $item;
    }

    /**
     * ฟังกชั่นตรวจสอบว่าสามารถสร้างปุ่มได้หรือไม่
     *
     * @param array $item
     *
     * @return array
     */
    public function onCreateButton($btn, $attributes, $item)
    {
        if ($btn == 'edit') {
            return $item['status'] == 0 && $item['today'] == 0 ? $attributes : false;
        } elseif ($btn == 'cancel') {
            return $item['status'] == 0 ? $attributes : false;
        } else {
            return $attributes;
        }
    }
}
