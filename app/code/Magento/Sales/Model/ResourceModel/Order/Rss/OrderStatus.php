<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Rss;

use Magento\Framework\App\ResourceConnection;

/**
 * Order Rss Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class OrderStatus
{
    /**
     * @var Resource
     */
    protected $_resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
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
        /** @var $resource \Magento\Framework\App\ResourceConnection */
        $resource = $this->_resource;
        $read = $resource->getConnection();

        $fields = ['notified' => 'is_customer_notified', 'comment', 'created_at'];
        $commentSelects = [];
        foreach (['invoice', 'shipment', 'creditmemo'] as $entityTypeCode) {
            $mainTable = $resource->getTableName('sales_' . $entityTypeCode);
            $slaveTable = $resource->getTableName('sales_' . $entityTypeCode . '_comment');
            $select = $read->select()->from(
                ['main' => $mainTable],
                ['entity_id' => 'order_id', 'entity_type_code' => new \Zend_Db_Expr("'{$entityTypeCode}'")]
            )->join(
                ['slave' => $slaveTable],
                'main.entity_id = slave.parent_id',
                $fields
            )->where(
                'main.order_id = ?',
                $orderId
            );
            $commentSelects[] = '(' . $select . ')';
        }
        $select = $read->select()->from(
            $resource->getTableName('sales_order_status_history'),
            ['entity_id' => 'parent_id', 'entity_type_code' => new \Zend_Db_Expr("'order'")] + $fields
        )->where(
            'parent_id = ?',
            $orderId
        )->where(
            'is_visible_on_front > 0'
        );
        $commentSelects[] = '(' . $select . ')';

        $commentSelect = $read->select()->union($commentSelects, \Magento\Framework\DB\Select::SQL_UNION_ALL);

        $select = $read->select()->from(
            ['orders' => $resource->getTableName('sales_order')],
            ['increment_id']
        )->join(
            ['t' => $commentSelect],
            't.entity_id = orders.entity_id'
        )->order(
            'orders.created_at desc'
        );

        return $read->fetchAll($select);
    }
}
