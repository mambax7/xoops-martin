<?php

namespace XoopsModules\Xmartin;

/**
 * Module:martin
 * Licence: GNU
 */
defined('XOOPS_ROOT_PATH') || exit('Restricted access');

require_once XOOPS_ROOT_PATH . '/modules/xmartin/include/common.php';

/**
 * @method: orderHandler
 * @license   http://www.blags.org/
 * @created   :2010年05月21日 20时40分
 * @copyright 1997-2010 The Martin Group
 * @author    Martin <china.codehome@gmail.com>
 * */
class OrderHandler extends \XoopsPersistableObjectHandler
{

    /**
     * @var Helper
     */
    public $helper;
    public $isAdmin;

    /**
     * @param \XoopsDatabase $db
     * @param null|\XoopsModules\Xmartin\Helper           $helper
     */
    public function __construct(\XoopsDatabase $db = null, \XoopsModules\Xmartin\Helper $helper = null)
    {
        /** @var \XoopsModules\Xmartin\Helper $this->helper */
        if (null === $helper) {
            $this->helper = \XoopsModules\Xmartin\Helper::getInstance();
        } else {
            $this->helper = $helper;
        }
        $isAdmin = $this->helper->isUserAdmin();
        parent::__construct($db, 'xmartin_order', Order::class, 'order_id', 'order_id');
    }

    /**
     * create a new hotel order
     * @param bool $isNew flag the new objects as "new"?
     * @return object order
     */
    public function &create($isNew = true)
    {
        $order = new Order();
        if ($isNew) {
            $order->setNew();
        }

        return $order;
    }

    /**
     * retrieve a hotel order
     *
     * @param int        $id orderid of the order
     * @param null|mixed $fields
     * @return mixed reference to the {@link order} object, FALSE if failed
     */
    public function get($id = null, $fields = null)
    {
        if ((int)$id <= 0) {
            return false;
        }

        $criteria = new \CriteriaCompo(new \Criteria('order_id', $id));
        $criteria->setLimit(1);
        $obj_array = $this->getObjects($criteria);

        if (1 != count($obj_array)) {
            $obj = $this->create();

            return $obj;
        }

        //var_dump($obj_array);
        //get order roooms
        $obj_array[0]->rooms  = $this->getOrderRooms($id);
        $obj_array[0]->qrooms = $this->getOrderQueryRooms($id);
        //not query room
        //$obj_array[0]->rooms = empty($obj_array[0]->rooms) ? $this->getOrderRooms($id) : $obj_array[0]->rooms;

        return $obj_array[0];
    }

    /**
     * @得到列表
     * @license   http://www.blags.org/
     * @created   :2010年05月23日 14时59分
     * @copyright 1997-2010 The Martin Group
     * @author    Martin <china.codehome@gmail.com>
     * @param         $Data
     * @param int     $limit
     * @param int     $start
     * @param string  $sort
     * @param string  $order
     * @param bool    $id_as_key
     * @return array
     */
    public function &getOrders($Data, $limit = 0, $start = 0, $sort = 'order_id', $order = 'ASC', $id_as_key = true)
    {
        $criteria = new \CriteriaCompo();

        $criteria->setSort($sort);
        $criteria->setOrder($order);

        $criteria->setStart($start);
        $criteria->setLimit($limit);

        return $this->getObjects($criteria, $id_as_key, true, $Data);
    }

    /**
     * insert a new order in the database
     *
     * @param \XoopsObject $order       reference to the {@link order}
     *                                  object
     * @param bool         $force
     * @return bool FALSE if failed, TRUE if already present and unchanged or successful
     */
    public function insert(\XoopsObject $order, $force = false)
    {
        $moduleDirName = basename(dirname(__DIR__));
        if ("xoopsmodules\\$moduleDirName\\order" !== mb_strtolower(get_class($order))) {
            return false;
        }

        if (!$order->cleanVars()) {
            return false;
        }

        foreach ($order->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        if ($order->isNew()) {
            $sql = sprintf(
                'INSERT INTO `%s` (
                                order_type_id,
                                order_type_name
                            ) VALUES (
                                NULL,
                                %s
                            )',
                $this->db->prefix('xmartin_hotel_order_type'),
                $this->db->quoteString($order_type_name)
            );
        } else {
            $sql = sprintf(
                'UPDATE `%s` SET
                                order_type_name = %s
                            WHERE order_type_id = %u',
                $this->db->prefix('xmartin_hotel_order_type'),
                $this->db->quoteString($order_type_name),
                $order_type_id
            );
        }
        //echo $sql;exit;
        if (false !== $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            $order->setErrors('The query returned an error. ' . $this->db->error());

            return false;
        }

        return $order_id > 0 ? $order_id : $this->db->getInsertId();
    }

    /**
     * @ update order
     * @license   http://www.blags.org/
     * @created   :2010年06月09日 21时46分
     * @copyright 1997-2010 The Martin Group
     * @author    Martin <china.codehome@gmail.com>
     * @param $OrderObj
     * @param $room_id
     * @return bool
     */
    public function updateOrder($OrderObj, $room_id)
    {
        $sql = 'UPDATE ' . $this->db->prefix('xmartin_order') . ' set order_status = ' . $OrderObj->order_status() . ' WHERE order_id = ' . $OrderObj->order_id();
        //echo $sql;exit;
        //update Status
        $result = true;
        if (!$this->db->queryF($sql)) {
            $result = false;
        }
        //update room price

        if (is_array($room_id)) {
            foreach ($room_id as $key => $room_price) {
                list($id, $room_date) = explode('-', $key);
                $sql = 'UPDATE ' . $this->db->prefix('xmartin_order_query_room') . ' set room_price = ' . $room_price . ' WHERE order_id = ' . $OrderObj->order_id() . " AND room_id = $id AND room_date = $room_date";
                if (!$this->db->queryF($sql)) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * @删除一个城市
     * @method:delete(order_id)
     * @license   http://www.blags.org/
     * @created   :2010年05月21日 20时40分
     * @copyright 1997-2010 The Martin Group
     * @author    Martin <china.codehome@gmail.com>
     * @param \XoopsObject $order
     * @param bool         $force
     * @return bool|void
     */
    public function delete(\XoopsObject $order, $force = false)
    {
        $moduleDirName = basename(dirname(__DIR__));
        if ("xoopsmodules\\$moduleDirName\\order" !== mb_strtolower(get_class($order))) {
            return false;
        }

        global $xoopsDB;
        //delete order room relation
        $sql = 'DELETE FROM ' . $xoopsDB->prefix('xmartin_order_room') . ' WHERE order_id = ' . $order->order_id();
        $xoopsDB->queryF($sql);
        //delete order query room relation
        $sql = 'DELETE FROM ' . $xoopsDB->prefix('xmartin_order_query_room') . ' WHERE order_id = ' . $order->order_id();
        $xoopsDB->queryF($sql);
        //delete order service
        $sql = 'DELETE FROM ' . $xoopsDB->prefix('xmartin_order_service') . ' WHERE order_id = ' . $order->order_id();
        $xoopsDB->queryF($sql);

        $sql = 'DELETE FROM ' . $xoopsDB->prefix('xmartin_order') . ' WHERE order_id = ' . $order->order_id();

        if (false !== $force) {
            $result = $xoopsDB->queryF($sql);
        } else {
            $result = $xoopsDB->query($sql);
        }

        if (!$result) {
            return false;
        }

        return true;
    }

    /**
     * delete hotel cities matching a set of conditions
     *
     * @param \CriteriaElement $criteria {@link CriteriaElement}
     * @param mixed            $force
     * @param mixed            $asObject
     * @return bool   FALSE if deletion failed
     */
    public function deleteAll(\CriteriaElement $criteria = null, $force = true, $asObject = false)
    {
        $sql = 'DELETE FROM ' . $this->db->prefix('xmartin_order');
        if (isset($criteria) && $criteria instanceof \CriteriaElement) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            return false;
        }

        return true;
    }

    /**
     * count hotel cities matching a condition
     *
     * @param \CriteriaElement|null $criteria
     * @param                       $Data
     * @return int count of categories
     * @internal param object $criteria <a href='psi_element://CriteriaElement'>CriteriaElement</a> to match to match
     */
    public function getCount(\CriteriaElement $criteria = null, $Data = null)
    {
        if (is_array($Data)) {
            foreach ($Data as $key => $value) {
                ${$key} = (int)$value;
            }
        }
        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix('xmartin_order') . ' WHERE 1 = 1 ';
        $sql .= $order_mode > 0 ? "AND order_mode = $order_mode " : ' ';
        $sql .= $order_pay_method > 0 ? "AND order_pay_method = $order_pay_method " : ' ';
        $sql .= $order_status > 0 ? "AND order_status = $order_status " : ' ';
        $sql .= $order_type > 0 ? "AND order_type = $order_type " : ' ';
        $sql .= $hotel_id > 0 ? 'AND  order_id IN (
                SELECT order_id FROM ' . $this->db->prefix('xmartin_order_room') . ' WHERE room_id IN (
                SELECT room_id FROM ' . $this->db->prefix('xmartin_room') . " WHERE hotel_id = $hotel_id
                )) " : ' ';
        //echo $sql;
        $result = $this->db->query($sql);
        if (!$result) {
            return 0;
        }
        list($count) = $this->db->fetchRow($result);

        return $count;
    }

    /**
     * @得到城市
     * @license   http://www.blags.org/
     * @created   :2010年05月21日 20时40分
     * @copyright 1997-2010 The Martin Group
     * @author    Martin <china.codehome@gmail.com>
     * @param \CriteriaElement $criteria
     * @param bool             $id_as_key
     * @param mixed            $as_object
     * @param                  $Data
     * @return array
     */
    public function &getObjects(\CriteriaElement $criteria = null, $id_as_key = false, $as_object = true, $Data = null)
    {
        if (is_array($Data)) {
            foreach ($Data as $key => $value) {
                ${$key} = (int)$value;
            }
        }

        $ret   = [];
        $limit = $start = 0;
        $sql   = 'SELECT o.*,u.* FROM ' . $this->db->prefix('xmartin_order') . ' o ';
        $sql   .= 'LEFT JOIN ' . $this->db->prefix('users') . ' u ON ( o.order_uid = u.uid ) ';
        if (isset($criteria) && $criteria instanceof \CriteriaElement && empty($Data)) {
            $sql .= ' ' . $criteria->renderWhere();
            /*if ($criteria->getSort() != '') {
                $sql .= ' ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
            }*/
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $sql .= $Data ? ' WHERE 1 = 1 ' : ' ';
        $sql .= $order_mode > 0 ? "AND order_mode = $order_mode " : ' ';
        $sql .= $order_pay_method > 0 ? "AND order_pay_method = $order_pay_method " : ' ';
        $sql .= $order_status > 0 ? "AND order_status = $order_status " : ' ';
        $sql .= $order_type > 0 ? "AND order_type = $order_type " : ' ';
        $sql .= $hotel_id > 0 ? 'AND  o.order_id IN (
                SELECT order_id FROM ' . $this->db->prefix('xmartin_order_room') . ' WHERE room_id IN (
                SELECT room_id FROM ' . $this->db->prefix('xmartin_room') . " WHERE hotel_id = $hotel_id
                )) " : ' ';
        $sql .= ' ORDER BY o.order_status ASC , o.order_submit_time DESC ';
        //echo $sql;exit;

        $result = $this->db->query($sql, $limit, $start);

        if (!$result) {
            return $ret;
        }

        $theObjects = [];

        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $order = new Order();
            $order->assignVars($myrow);
            $theObjects[$myrow['order_id']] = &$order;
            //var_dump($order);
            unset($order);
        }
        //var_dump($theObjects);

        foreach ($theObjects as $theObject) {
            if (!$id_as_key) {
                $ret[] = &$theObject;
            } else {
                $ret[$theObject->order_id()] = &$theObject;
            }
            unset($theObject);
        }

        //var_dump($ret);
        return $ret;
    }

    /**
     * @get       order rooms
     * @method:
     * @license   http://www.blags.org/
     * @created   :2010年06月07日 20时25分
     * @copyright 1997-2010 The Martin Group
     * @author    Martin <china.codehome@gmail.com>
     * @param $order_id
     * @return array
     */
    public function getOrderRooms($order_id)
    {
        if (!$order_id) {
            return $order_id;
        }
        global $xoopsDB;
        $sql = 'SELECT mor.room_id , mor.room_count ,mor.room_date , r.room_name , h.hotel_id ,
            rp.room_is_today_special,rp.room_price,rp.room_advisory_range_small,rp.room_advisory_range_max ,
            h.hotel_name,hotel_city_id ';
        $sql .= 'FROM ' . $xoopsDB->prefix('xmartin_order_room') . ' mor ';

        $sql .= ' LEFT JOIN ' . $xoopsDB->prefix('xmartin_room') . ' r ON ( r.room_id = mor.room_id ) ';
        $sql .= ' LEFT JOIN ' . $xoopsDB->prefix('xmartin_room_price') . ' rp ON ( r.room_id = rp.room_id AND mor.room_date = rp.room_date ) ';
        $sql .= ' LEFT JOIN ' . $xoopsDB->prefix('xmartin_hotel') . ' h ON ( r.hotel_id = h.hotel_id ) ';
        $sql .= ' WHERE 1 = 1 ';
        $sql .= $order_id > 0 ? " AND mor.order_id = $order_id " : ' ';
        $sql .= ' Group by mor.room_date order by mor.room_id DESC ';
        //echo '<pre>'.$sql;exit;
        $result = $xoopsDB->query($sql);
        $rows   = [];
        while (false !== ($row = $xoopsDB->fetchArray($result))) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @get       order query rooms
     * @method:
     * @license   http://www.blags.org/
     * @created   :2010年06月07日 20时25分
     * @copyright 1997-2010 The Martin Group
     * @author    Martin <china.codehome@gmail.com>
     * @param $order_id
     * @return array
     */
    public function getOrderQueryRooms($order_id)
    {
        if (!$order_id) {
            return $order_id;
        }
        global $xoopsDB;
        $sql = 'SELECT mor.room_id , mor.room_count ,mor.room_date , mor.room_price , r.* ,
            rp.room_is_today_special,rp.room_advisory_range_small,rp.room_advisory_range_max ,
            h.hotel_name,hotel_city_id ';
        $sql .= 'FROM ' . $xoopsDB->prefix('xmartin_order_query_room') . ' mor ';

        $sql .= ' LEFT JOIN ' . $xoopsDB->prefix('xmartin_room') . ' r ON ( r.room_id = mor.room_id ) ';
        $sql .= ' LEFT JOIN ' . $xoopsDB->prefix('xmartin_room_price') . ' rp ON ( r.room_id = rp.room_id AND mor.room_date = rp.room_date ) ';
        $sql .= ' LEFT JOIN ' . $xoopsDB->prefix('xmartin_hotel') . ' h ON ( r.hotel_id = h.hotel_id ) ';
        $sql .= ' WHERE 1 = 1 ';
        $sql .= $order_id > 0 ? " AND mor.order_id = $order_id " : ' ';
        $sql .= ' Group by mor.room_date order by mor.room_id DESC ';
        //echo '<pre>'.$sql;exit;
        $result = $xoopsDB->query($sql);
        $rows   = [];
        while (false !== ($row = $xoopsDB->fetchArray($result))) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @得到类别列表
     * @license   http://www.blags.org/
     * @created   :2010年05月30日 20时48分
     * @copyright 1997-2010 The Martin Group
     * @author    Martin <china.codehome@gmail.com>
     * @param \CriteriaElement|null $criteria
     * @param mixed                 $limit
     * @param mixed                 $start
     *
     * @return array
     */
    public function getList(\CriteriaElement $criteria = null, $limit = 0, $start = 0)
    {
        $sql    = 'SELECT * FROM ' . $this->db->prefix('xmartin_order');
        $result = $this->db->query($sql);
        $rows   = [];
        while (false !== ($row = $this->db->fetchArray($result))) {
            $rows[$row['order_id']] = $row;
        }

        return $rows;
    }

    /**
     * @get       hotel list
     * @license   http://www.blags.org/
     * @created   :2010年06月10日 21时25分
     * @copyright 1997-2010 The Martin Group
     * @author    Martin <china.codehome@gmail.com>
     * @param $hotel_city_id
     * @param $hotel_star
     * @return array
     */
    public function getSearchHotelList($hotel_city_id, $hotel_star)
    {
        global $xoopsDB;
        $sql      = "select hotel_id ,hotel_name FROM {$xoopsDB->prefix('xmartin_hotel')} WHERE 1 = 1 ";
        $sql      .= $hotel_city_id > 0 ? " and hotel_city_id = $hotel_city_id " : ' ';
        $sql      .= $hotel_star > 0 ? " and hotel_star = $hotel_star " : ' ';
        $result   = $xoopsDB->query($sql);
        $hotelArr = [];
        while (false !== ($row = $xoopsDB->fetchArray($result))) {
            $hotelArr[$row['hotel_id']] = $row['hotel_name'];
        }

        return $hotelArr;
    }
}
