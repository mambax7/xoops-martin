<?php
/**
 * @用户订单
 * @license   http://www.blags.org/
 * @created   :2010年07月15日 20时25分
 * @copyright 1997-2010 The Martin Group
 * @author    Martin <china.codehome@gmail.com>
 * */

//构造对象

use XoopsModules\Xmartin;

$searchHandler = $helper->getHandler('Search');
$orderHandler  = $helper->getHandler('Order');
$memberHandler = $helper->getHandler('Member');
//array
$OrderType      = getModuleArray('order_type', 'order_type', true);
$OrderMode      = getModuleArray('order_mode', 'order_mode', true);
$OrderPayMethod = getModuleArray('order_pay_method', 'order_pay_method', true);
$OrderStatus    = getModuleArray('order_status', 'order_status', true);

$xoopsOption['xoops_pagetitle'] = $xoopsOption['xoops_pagetitle'] ?: '我的订单 - 用户中心';
$xoopsOption['template_main']   = 'martin_member_index.tpl';
$orderHandler->create();
$orderHandler->__set('uid', (int)$xoopsUser->uid());
//显示全部订单
if ('order' !== $action) {
    $orderHandler->__set('order_status', 14);
}

//echo $action;

//echo $orderHandler->order_status;

/** @var Xmartin\Helper $helper */
$helper = Xmartin\Helper::getInstance();

$Count        = $orderHandler->getCount();
$OrderObjects = $Count > 0 ? $orderHandler->getOrders([], $helper->getConfig('front_perpage'), $start, 0) : null;

$order_ids = array_keys($OrderObjects);

$hotels     = $memberHandler->getOrderHotels($order_ids);
$hotelAlias = $searchHandler->getCityAlias();

//分页
require_once XOOPS_ROOT_PATH . '/class/pagenav.php';
$pagenav = new \XoopsPageNav($Count, $helper->getConfig('front_perpage'), $start, 'start', '');

//var_dump($OrderType);
//处理
$orders = [];
if (is_array($OrderObjects)) {
    foreach ($OrderObjects as $key => $order) {
        $orderArr = [];
        foreach ($order->vars as $k => $v) {
            $orderArr[$k] = $v['value'];
        }
        $orderArr['hotel_name']       = $hotels[$key]['hotel_name'];
        $orderArr['room_type_info']   = $hotels[$key]['room_type_info'];
        $orderArr['hotel_url']        = XOOPS_URL . '/hotel/' . $hotelAlias[$hotels[$key]['hotel_city']] . '/' . $hotels[$key]['hotel_alias'] . $helper->getConfig('hotel_static_prefix');
        $orderArr['order_type']       = $OrderType[$orderArr['order_type']];
        $orderArr['order_mode']       = $OrderMode[$orderArr['order_mode']];
        $orderArr['order_status_int'] = $orderArr['order_status'];
        $orderArr['order_status']     = $OrderStatus[$orderArr['order_status']];
        $orders[]                     = $orderArr;
        unset($orderArr);
    }
}
unset($OrderObjects);
//var_dump($orders);

$xoopsTpl->assign('orders', $orders);
$xoopsTpl->assign('order_type', $OrderType);
$xoopsTpl->assign('order_mode', $OrderMode);
$xoopsTpl->assign('pagenav', $pagenav->MartinNav(4, MEMBER_URL . "?$action&amp;start="));
