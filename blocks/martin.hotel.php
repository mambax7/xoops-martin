<?php
/**
 * @订房搜索
 * @license   http://www.blags.org/
 * @created   :2010年05月19日 22时38分
 * @copyright 1997-2010 The Martin Group
 * @author    Martin <china.codehome@gmail.com>
 * */

use XoopsModules\Xmartin;

defined('XOOPS_ROOT_PATH') || exit('Restricted access');

/**
 * hoel search show function
 * @param $options
 * @return mixed
 */
function martin_hotel_search_show($options)
{
    global $xoopsModule, $xoopsTpl;
    /** @var Xmartin\Helper $helper */
    $helper = Xmartin\Helper::getInstance();

    //新闻
    /*if($xoopsModule->dirname() != 'martin')
    {*/ /** @var \XoopsModuleHandler $moduleHandler */ //    $moduleHandler     = xoops_getHandler('module');
    //    $configHandler     = xoops_getHandler('config');
    //    $xoopsModule       = $moduleHandler->getByDirname('martin');
    //    $xoopsModuleConfig = $configHandler->getConfigsByCat(0, $xoopsModule->getVar('mid'));
    /*}*/
    //var_dump($xoopsModuleConfig);

    require_once XOOPS_ROOT_PATH . '/modules/xmartin/include/functions.php';
    $hotelHandler   = $helper->getHandler('Hotel');
    $groupHandler   = $helper->getHandler('Group');
    $auctionHandler = $helper->getHandler('Auction');
    $newsHandler    = $helper->getHandler('Hotelnews');

    $hotel_guide         = explode(',', $helper->getConfig('hotel_guide'));
    $hotel_today_special = explode(',', $helper->getConfig('hotel_today_special'));
    $hotel_news_ids      = (is_array($hotel_guide) && is_array($hotel_today_special)) ? array_merge($hotel_guide, $hotel_today_special) : null;
    $hotelnews           = $newsHandler->getHotelNews($hotel_news_ids);

    $hotel_guide_rows         = [];
    $hotel_today_special_rows = [];
    foreach ($hotelnews as $key => $row) {
        if (in_array($key, $hotel_guide) && count($hotel_guide_rows) < 6) {
            $hotel_guide_rows[] = $row;
        }
        if (in_array($key, $hotel_today_special) && count($hotel_today_special_rows) < 6) {
            $hotel_today_special_rows[] = $row;
        }
    }

    $block['module_url']               = XOOPS_URL . '/modules/xmartin/';
    $block['hotelrank']                = getModuleArray('hotelrank', 'hotelrank', true, null, $xoopsModuleConfig); //TODO
    $block['groupList']                = $groupHandler->getGroupList();
    $block['auctionList']              = $auctionHandler->getAuctionList();
    $block['hotel_guide_rows']         = $hotel_guide_rows;
    $block['hotel_today_special_rows'] = $hotel_today_special_rows;
    $block['cityList']                 = $hotelHandler->getCityList('WHERE city_parentid = 0');
    $block['hotel_static_prefix']      = $helper->getConfig('hotel_static_prefix');

    unset($hotelHandler, $groupHandler, $auctionHandler, $newsHandler, $hotel_guide_rows, $hotel_today_special_rows);

    //var_dump($block);
    return $block;
}

/**
 * hoel search edit function
 * @param $options
 * @return string
 */
function martin_hotel_search_edit($options)
{
    return '';
}
