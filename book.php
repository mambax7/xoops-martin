<?php

use XoopsModules\Xmartin;

require_once __DIR__ . '/header.php';

if (!defined('MODULE_URL')) {
    define('MODULE_URL', XOOPS_URL . '/modules/xmartin/');
}
/** @var Xmartin\Helper $helper */
$helper = Xmartin\Helper::getInstance();

//测试阶段
//redirect_header('http://chat.53kf.com/company.php?arg=gjlmo&style=1',1,'客户接入中....');

$hotelHandler     = $helper->getHandler('Hotel');
$roomHandler      = $helper->getHandler('Room');
$serviceHandler   = $helper->getHandler('HotelService');
$promotionHandler = $helper->getHandler('Promotion');
//paramerters
$hotel_id       = \Xmf\Request::getInt('hotel_id', 0, 'GET');
$room_id        = \Xmf\Request::getInt('room_id', 0, 'GET');
$isFind         = isset($_GET['isFind']) ? trim($_GET['isFind']) : false;
$isFind         = 'true' === $isFind ? true : $isFind;
$isFind         = 'false' === $isFind ? false : $isFind;
$check_in_date  = isset($_GET['check_in_date']) ? strtotime($_GET['check_in_date']) : 0;
$check_in_date  = !$check_in_date ? \Xmf\Request::getInt('check_in_date', 0, 'GET') : (int)$check_in_date;
$check_out_date = isset($_GET['check_out_date']) ? strtotime($_GET['check_out_date']) : 0;
$check_out_date = !$check_out_date ? \Xmf\Request::getInt('check_out_date', 0, 'GET') : (int)$check_out_date;
//时间处理
//paramerters

$hotel_obj = $hotelHandler->get($hotel_id);
foreach ($hotel_obj->vars as $key => $var) {
    $hotel_data[$key] = $hotel_obj->$key();
}

$check_date_count = (int)(($check_out_date - $check_in_date) / (3600 * 24));
$check_arr        = getCheckDateArr($check_in_date, $check_out_date);
$room_price       = $roomHandler->getRoomDatePrie($room_id, $check_in_date, $check_out_date);
$this_url         = MODULE_URL . 'book.php?' . $_SERVER['QUERY_STRING'];
$this_url         = str_replace('&check_in_date=' . $check_in_date, '', $this_url);
$this_url         = str_replace('&check_in_date=' . date('Y-m-d', $check_in_date), '', $this_url);
$this_url         = str_replace('&check_out_date=' . $check_out_date, '', $this_url);
$this_url         = str_replace('&check_out_date=' . date('Y-m-d', $check_out_date), '', $this_url);

if (!$xoopsUser) {
    redirect_header(XOOPS_URL . '/user.php?xoops_redirect=/' . $_SERVER['REQUEST_URI'], 1, '您还没有登录.');
}

$xoopsUser->cleanVars();
$user = &$xoopsUser->cleanVars;
//var_dump($user);

//得到酒店相关信息
$hotel_service = $serviceHandler->getHotelService($hotel_id);
//var_dump($hotel_service);
$hotelrank               = getModuleArray('hotelrank', 'hotelrank', true);
$hotel_data['promotion'] = $promotionHandler->getHotelPromotion($hotel_id);

$GLOBALS['xoopsOption']['template_main'] = $isFind ? 'martin_hotel_find_book.tpl' : 'martin_hotel_book.tpl';
$select_title                            = '您选择了 ' . $hotel_data['hotel_name'];

require_once XOOPS_ROOT_PATH . '/header.php';
require_once XOOPS_ROOT_PATH . '/modules/xmartin/HotelSearchLeft.php';

$xoopsOption['xoops_pagetitle'] = $select_title . ' - 酒店预定'; // - '.$xoopsConfig['sitename'];
$xoopsTpl->assign('check_date_count', $check_date_count);
$xoopsTpl->assign('xoops_pagetitle', $xoopsOption['xoops_pagetitle']);
$xoopsTpl->assign('hotel_static_prefix', $helper->getConfig('hotel_static_prefix'));
$xoopsTpl->assign('module_url', MODULE_URL);
$xoopsTpl->assign('this_url', $this_url);
$xoopsTpl->assign('hotelrank', $hotelrank);
$xoopsTpl->assign('order_document_type', getModuleArray('order_document_type', 'order_document_type', true));
$xoopsTpl->assign('hotel', $hotel_data);
$xoopsTpl->assign('hotel_service', $hotel_service);
$xoopsTpl->assign('room_price', $room_price);
$xoopsTpl->assign('rooms', $rooms);
$xoopsTpl->assign('check_arr', $check_arr);
$xoopsTpl->assign('user', $user);
$xoopsTpl->assign('room_id', $room_id);
$xoopsTpl->assign('hotel_id', $hotel_id);
$xoopsTpl->assign('check_in_date', $check_in_date);
$xoopsTpl->assign('check_out_date', $check_out_date);
$xoopsTpl->assign('person_exchange_price', $xoopsUser->total_coupon());

if (false === @session_start()) {
    throw new \RuntimeException('Session could not start.');
}
//防止重复提交
$Form_Validate             = md5(mt_rand(1000, 10000));
$_SESSION['Form_Validate'] = $Form_Validate;
$xoopsTpl->assign('Form_Validate', $Form_Validate);

require_once XOOPS_ROOT_PATH . '/footer.php';
