<?php

namespace XoopsModules\Xmartin;

/**
 * Module:martin
 * Licence: GNU
 */
defined('XOOPS_ROOT_PATH') || exit('Restricted access');

require_once XOOPS_ROOT_PATH . '/modules/xmartin/include/common.php';

/**
 * @method: PromotionHandler
 * @license   http://www.blags.org/
 * @created   :2010年05月21日 20时40分
 * @copyright 1997-2010 The Martin promotion
 * @author    Martin <china.codehome@gmail.com>
 * */
class PromotionHandler extends \XoopsPersistableObjectHandler
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
        parent::__construct($db, 'xmartin_promotion', Promotion::class, 'promotion_id', 'hotel_name');
    }
    /**
     * create a new hotel city
     * @param bool $isNew flag the new objects as "new"?
     * @return object promotion
     */
    public function &create($isNew = true)
    {
        $promotion = new Promotion();
        if ($isNew) {
            $promotion->setNew();
        }

        return $promotion;
    }

    /**
     * retrieve a hotel city
     *
     * @param int        $id promotionid of the hotel promotion
     * @param null|mixed $fields
     * @return mixed reference to the {@link promotion} object, FALSE if failed
     */
    public function get($id = null, $fields = null)
    {
        if ((int)$id <= 0) {
            return false;
        }

        $criteria = new \CriteriaCompo(new \Criteria('promotion_id', $id));
        $criteria->setLimit(1);
        $obj_array = $this->getObjects($criteria);
        if (1 != count($obj_array)) {
            $obj = $this->create();

            return $obj;
        }

        return $obj_array[0];
    }

    /**
     * @得到列表
     * @method:
     * @license   http://www.blags.org/
     * @created   :2010年05月23日 14时59分
     * @copyright 1997-2010 The Martin promotion
     * @author    Martin <china.codehome@gmail.com>
     * @param int    $limit
     * @param int    $start
     * @param string $sort
     * @param string $order
     * @param bool   $id_as_key
     * @return array
     */
    public function &getPromotions(
        $limit = 0,
        $start = 0,
        $sort = 'promotion_add_time',
        $order = 'DESC',
        $id_as_key = true
    ) {
        $criteria = new \CriteriaCompo();

        $criteria->setSort($sort);
        $criteria->setOrder($order);

        $criteria->setStart($start);
        $criteria->setLimit($limit);

        return $this->getObjects($criteria, $id_as_key);
    }

    /**
     * insert a new promotion in the database
     *
     * @param \XoopsObject $promotion       reference to the {@link hotel promotion}
     *                                      object
     * @param bool         $force
     * @return bool FALSE if failed, TRUE if already present and unchanged or successful
     */
    public function insert(\XoopsObject $promotion, $force = false)
    {
        $moduleDirName = basename(dirname(__DIR__));
        if ("xoopsmodules\\$moduleDirName\\promotion" !== mb_strtolower(get_class($promotion))) {
            return false;
        }

        if (!$promotion->cleanVars()) {
            return false;
        }

        foreach ($promotion->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        if ($promotion->isNew()) {
            $sql = sprintf(
                'INSERT INTO `%s` (
                                promotion_id,
                                promotion_description,
                                hotel_id,
                                promotion_start_date,
                                promotion_end_date,
                                promotion_add_time
                            ) VALUES (
                                NULL,
                                %s,%u,%u,%u,%u
                            )',
                $this->db->prefix('xmartin_hotel_promotions'),
                $this->db->quoteString($promotion_description),
                $hotel_id,
                $promotion_start_date,
                $promotion_end_date,
                $promotion_add_time
            );
        } else {
            $sql = sprintf(
                'UPDATE `%s` SET
                                promotion_description = %s,
                                hotel_id = %u,
                                promotion_start_date = %u,
                                promotion_end_date = %u
                            WHERE promotion_id = %u',
                $this->db->prefix('xmartin_hotel_promotions'),
                $this->db->quoteString($promotion_description),
                $hotel_id,
                $promotion_start_date,
                $promotion_end_date,
                $promotion_id
            );
        }
        //echo $sql;exit;
        if (false !== $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }

        return $promotion_id > 0 ? $promotion_id : $this->db->getInsertId();
    }

    /**
     * @删除一个城市
     * @method:delete(promotion_id)
     * @license   http://www.blags.org/
     * @created   :2010年05月21日 20时40分
     * @copyright 1997-2010 The Martin promotion
     * @author    Martin <china.codehome@gmail.com>
     * @param \XoopsObject $promotion
     * @param bool         $force
     * @return bool|void
     */
    public function delete(\XoopsObject $promotion, $force = false)
    {
        $moduleDirName = basename(dirname(__DIR__));
        if ("xoopsmodules\\$moduleDirName\\promotion" !== mb_strtolower(get_class($promotion))) {
            return false;
        }

        $sql = 'DELETE FROM ' . $this->db->prefix('xmartin_hotel_promotions') . ' WHERE promotion_id = ' . $promotion->promotion_id();

        if (false !== $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
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
        $sql = 'DELETE FROM ' . $this->db->prefix('xmartin_hotel_promotions');
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
     * @param \CriteriaElement $criteria {@link CriteriaElement} to match
     * @return int    count of categories
     */
    public function getCount(\CriteriaElement $criteria = null)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix('xmartin_hotel_promotions');
        if (isset($criteria) && $criteria instanceof \CriteriaElement) {
            $sql .= ' ' . $criteria->renderWhere();
        }
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
     * @copyright 1997-2010 The Martin promotion
     * @author    Martin <china.codehome@gmail.com>
     * @param \CriteriaElement $criteria
     * @param bool             $id_as_key
     * @param mixed            $as_object
     * @return array
     */
    public function &getObjects(\CriteriaElement $criteria = null, $id_as_key = false, $as_object = true)
    {
        $ret   = [];
        $limit = $start = 0;
        $sql   = 'SELECT p.*,h.hotel_name FROM ' . $this->db->prefix('xmartin_hotel_promotions') . ' p ';
        $sql   .= ' left join ' . $this->db->prefix('xmartin_hotel') . ' h on ( h.hotel_id = p.hotel_id ) ';
        if (isset($criteria) && $criteria instanceof \CriteriaElement) {
            $sql .= ' ' . $criteria->renderWhere();
            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $sql .= ' order by p.promotion_id DESC ';
        //echo "<br>" . $sql . "<br>";
        $result = $this->db->query($sql, $limit, $start);

        if (!$result) {
            return $ret;
        }

        $theObjects = [];

        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $promotion = new Promotion();
            $promotion->assignVars($myrow);
            $theObjects[$myrow['promotion_id']] = &$promotion;
            //var_dump($promotion);
            unset($promotion);
        }
        //var_dump($theObjects);

        foreach ($theObjects as $theObject) {
            if (!$id_as_key) {
                $ret[] = &$theObject;
            } else {
                $ret[$theObject->promotion_id()] = &$theObject;
            }
            unset($theObject);
        }

        return $ret;
    }

    /**
     * @get       hotel promotion
     * @license   http://www.blags.org/
     * @created   :2010年06月14日 20时47分
     * @copyright 1997-2010 The Martin Group
     * @author    Martin <china.codehome@gmail.com>
     * @param $hotel_id
     * @return string
     */
    public function getHotelPromotion($hotel_id)
    {
        global $xoopsDB;
        if (!$hotel_id > 0) {
            return '';
        }
        $sql = 'SELECT * FROM ' . $xoopsDB->prefix('xmartin_hotel_promotions') . ' WHERE
            ' . time() . ' BETWEEN  promotion_start_date AND promotion_end_date  AND hotel_id = ' . $hotel_id . '  LIMIT 1 ';

        return $xoopsDB->fetchArray($xoopsDB->query($sql));
    }
}
