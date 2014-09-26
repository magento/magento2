<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Resource\Order\Rss;

/**
 * Order Rss Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class OrderStatus
{
    /**
     * @var \Magento\Framework\App\Resource
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(\Magento\Framework\App\Resource $resource)
    {
        $this->_resource = $resource;
    }

    /**
     * Retrieve order comments
     *
     * @param int $orderId
     * @return array
     */
    public function getAllCommentCollection($orderId)
    {
        /** @var $resource \Magento\Framework\App\Resource */
        $resource = $this->_resource;
        $read = $resource->getConnection('core_read');

        $fields = array('notified' => 'is_customer_notified', 'comment', 'created_at');
        $commentSelects = array();
        foreach (array('invoice', 'shipment', 'creditmemo') as $entityTypeCode) {
            $mainTable = $resource->getTableName('sales_flat_' . $entityTypeCode);
            $slaveTable = $resource->getTableName('sales_flat_' . $entityTypeCode . '_comment');
            $select = $read->select()->from(
                array('main' => $mainTable),
                array('entity_id' => 'order_id', 'entity_type_code' => new \Zend_Db_Expr("'{$entityTypeCode}'"))
            )->join(
                array('slave' => $slaveTable),
                'main.entity_id = slave.parent_id',
                $fields
            )->where(
                'main.order_id = ?',
                $orderId
            );
            $commentSelects[] = '(' . $select . ')';
        }
        $select = $read->select()->from(
            $resource->getTableName('sales_flat_order_status_history'),
            array('entity_id' => 'parent_id', 'entity_type_code' => new \Zend_Db_Expr("'order'")) + $fields
        )->where(
            'parent_id = ?',
            $orderId
        )->where(
            'is_visible_on_front > 0'
        );
        $commentSelects[] = '(' . $select . ')';

        $commentSelect = $read->select()->union($commentSelects, \Zend_Db_Select::SQL_UNION_ALL);

        $select = $read->select()->from(
            array('orders' => $resource->getTableName('sales_flat_order')),
            array('increment_id')
        )->join(
            array('t' => $commentSelect),
            't.entity_id = orders.entity_id'
        )->order(
            'orders.created_at desc'
        );

        return $read->fetchAll($select);
    }
}
