<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Tax;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Sales order tax resource model
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var ItemsPreloader
     */
    private $itemsPreloader;

    /**
     * @param Context $context
     * @param string|null $connectionName
     * @param ItemsPreloader|null $itemsPreloader
     */
    public function __construct(
        Context $context,
        $connectionName = null,
        ?ItemsPreloader $itemsPreloader = null
    ) {
        parent::__construct($context, $connectionName);
        $this->itemsPreloader = $itemsPreloader ?: ObjectManager::getInstance()->get(ItemsPreloader::class);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_tax_item', 'tax_item_id');
    }

    /**
     * Get Tax Items with order tax information
     *
     * @param int $orderId
     * @return array
     */
    public function getTaxItemsByOrderId($orderId)
    {
        $preloadedItems = $this->itemsPreloader->take((int)$orderId);
        if ($preloadedItems !== null) {
            return $preloadedItems;
        }

        $select = $this->getTaxItemsSelect()->where('tax.order_id = ?', $orderId);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Get Tax Items with order tax information of several orders
     *
     * @param int[] $orderIds
     * @return array
     */
    public function getTaxItemsByOrderIds(array $orderIds): array
    {
        $select = $this->getTaxItemsSelect()->where('tax.order_id IN (?)', $orderIds);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Build the select of tax items joined with their order tax
     *
     * @return Select
     */
    private function getTaxItemsSelect(): Select
    {
        return $this->getConnection()->select()->from(
            ['item' => $this->getTable('sales_order_tax_item')],
            [
                'tax_id',
                'tax_percent',
                'item_id',
                'taxable_item_type',
                'associated_item_id',
                'real_amount',
                'real_base_amount',
            ]
        )->join(
            ['tax' => $this->getTable('sales_order_tax')],
            'item.tax_id = tax.tax_id',
            ['code', 'title', 'order_id']
        );
    }
}
