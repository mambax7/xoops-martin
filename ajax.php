<?php

require_once __DIR__ . '/header.php';

if (!defined('MODULE_URL')) {
    define('MODULE_URL', XOOPS_URL . '/modules/xmartin/');
}

$hotelHandler     = $helper->getHandler('Hotel');
$roomHandler      = $helper->getHandler('Room');
$serviceHandler   = $helper->getHandler('HotelService');
$promotionHandler = $helper->getHandler('Promotion');

$action = isset($_GET['action']) ? trim(mb_strtolower($_GET['action'])) : null;
$action = isset($_POST['action']) ? trim(mb_strtolower($_POST['action'])) : $action;

/**
 * ajax
 * @access    public
 * @copyright 1997-2010 The Martin Group
 * @author    Martin <china.codehome@gmail.com>
 * @created   time :2010-07-03 15:30:35
 * */
switch ($action) {
    case 'saveuser':
        global $xoopsUser;
        $document       = \Xmf\Request::getInt('document', 0, 'POST');
        $document_value = isset($_POST['document_value']) ? trim($_POST['document_value']) : '';
        $name           = isset($_POST['name']) ? trim($_POST['name']) : '';
        $phone          = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $telephone      = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
        $memberHandler  = xoops_getHandler('member');
        $edituser       = $memberHandler->getUser($xoopsUser->uid());
        $edituser->setVar('name', $name);
        $edituser->setVar('document', $document);
        $edituser->setVar('document_value', $document_value);
        $edituser->setVar('phone', $phone);
        $edituser->setVar('telephone', $telephone);
        if (!$memberHandler->insertUser($edituser)) {
            echo _US_PROFUPDATED;
        } else {
            echo _US_NOPERMISS;
        }
        break;
    default:
        redirect_header(XOOPS_URL, 2, _US_NOPERMISS);
        break;
}
exit();
