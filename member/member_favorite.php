<?php
/**
 * @member    favorite hotel
 * @license   http://www.blags.org/
 * @created   :2010年07月15日 20时25分
 * @copyright 1997-2010 The Martin Group
 * @author    Martin <china.codehome@gmail.com>
 * */

use XoopsModules\Xmartin;

/** @var Xmartin\Helper $helper */
$helper = Xmartin\Helper::getInstance();

$hotelHandler  = $helper->getHandler('Hotel');
$searchHandler = $helper->getHandler('Search');
$memberHandler = $helper->getHandler('Member');

$xoopsOption['xoops_pagetitle'] = '我的收藏 - 用户中心';

$hotels = $memberHandler->getHotelList($start);
//var_dump($hotels);
$Count = $hotels['count'];
unset($hotels['count']);
//分页
require_once XOOPS_ROOT_PATH . '/class/pagenav.php';
$pagenav = new \XoopsPageNav($Count, $helper->getConfig('front_perpage'), $start, 'start', '');

$xoopsTpl->assign('hotels', $hotels);
$xoopsTpl->assign('pagenav', $pagenav->MartinNav(4, MEMBER_URL . "?$action&amp;start="));
