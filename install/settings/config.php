<?php
/* config.php */
return array(
    'version' => '4.0.2',
    'web_title' => 'E-Booking',
    'web_description' => 'ระบบจองห้องประชุม',
    'timezone' => 'Asia/Bangkok',
    'member_status' => array(
        0 => 'สมาชิก',
        1 => 'ผู้ดูแลระบบ',
        2 => 'ผู้รับผิดชอบ',
    ),
    'color_status' => array(
        0 => '#259B24',
        1 => '#FF0000',
        2 => '#0E0EDA',
    ),
    'default_icon' => 'icon-calendar',
    'user_forgot' => 0,
    'user_register' => 0,
    'welcome_email' => 0,
    'booking_w' => 600,
    'booking_status' => 0,
    'booking_notifications' => 0,
    'booking_approving' => 0,
);
