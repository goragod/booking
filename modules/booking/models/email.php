<?php
/**
 * @filesource modules/booking/models/email.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Booking\Email;

use Kotchasan\Date;
use Kotchasan\Language;

/**
 * ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * ส่งอีเมลแจ้งการทำรายการ
     *
     * @param string $mailto อีเมล
     * @param string $name   ชื่อ
     * @param array  $order ข้อมูล
     */
    public static function send($mailto, $name, $order)
    {
        $ret = array();
        // สถานะการจอง
        $status = Language::find('BOOKING_STATUS', '', $order['status']);
        // ข้อความ
        $msg = array(
            '{LNG_Book a meeting} ['.self::$cfg->web_title.']',
            '{LNG_Contact name}: '.$name,
            '{LNG_Topic}: '.$order['topic'],
            '{LNG_Date}: '.Date::format($order['begin']).' - '.Date::format($order['end']),
            '{LNG_Status}: '.$status,
        );
        if (!empty($order['reason'])) {
            $msg[] = '{LNG_Reason}: '.$order['reason'];
        }
        $msg[] = 'URL: '.WEB_URL.'index.php?module=booking';
        // ข้อความของ user
        $msg = Language::trans(implode("\n", $msg));
        // ข้อความของแอดมิน
        $admin_msg = $msg.'-order&id='.$order['id'];
        if (!empty(self::$cfg->line_api_key)) {
            // ส่ง Line
            $err = \Gcms\Line::send($admin_msg, self::$cfg->line_api_key);
            if ($err != '') {
                $ret[] = $err;
            }
        }
        if (self::$cfg->noreply_email != '') {
            // email subject
            $subject = '['.self::$cfg->web_title.'] '.Language::get('Book a meeting').' '.$status;
            // ส่งอีเมลไปยังผู้ทำรายการเสมอ
            $err = \Kotchasan\Email::send($name.'<'.$mailto.'>', self::$cfg->noreply_email, $subject, nl2br($msg));
            if ($err->error()) {
                // คืนค่า error
                $ret[] = strip_tags($err->getErrorMessage());
            }
            // ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
            $query = \Kotchasan\Model::createQuery()
                ->select('username', 'name')
                ->from('user')
                ->where(array(
                    array('social', 0),
                    array('active', 1),
                    array('username', '!=', $mailto),
                ))
                ->andWhere(array(
                    array('status', 1),
                    array('permission', 'LIKE', '%,can_approve_room,%'),
                ), 'OR')
                ->cacheOn();
            // รายละเอียดในอีเมล
            $admin_msg = nl2br($admin_msg);
            foreach ($query->execute() as $item) {
                // ส่งอีเมล
                $err = \Kotchasan\Email::send($item->name.'<'.$item->username.'>', self::$cfg->noreply_email, $subject, $admin_msg);
                if ($err->error()) {
                    // คืนค่า error
                    $ret[] = strip_tags($err->getErrorMessage());
                }
            }
        }
        // คืนค่า
        return empty($ret) ? Language::get('Your message was sent successfully') : implode("\n", $ret);
    }
}
