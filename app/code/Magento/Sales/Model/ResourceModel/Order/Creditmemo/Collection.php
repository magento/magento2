<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel\Order\Creditmemo;

use Magento\Sales\Api\Data\CreditmemoSearchResultInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection\AbstractCollection;

/**
 * Flat sales order creditmemo collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractCollection implements CreditmemoSearchResultInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_creditmemo_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'order_creditmemo_collection';

    /**
     * Order field for setOrderFilter
     *
     * @var string
     */
    protected $_orderField = 'order_id';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Sales\Model\Order\Creditmemo::class,
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo::class
        );
    }

    /**
     * Used to emulate after load functionality for each item without loading them
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->walk('afterLoad');
        return $this;
    }

    /**
     * Add filtration conditions
     *
     * @param array|null $filter
     * @return $this
     */
    public function getFiltered($filter = null)
    {
        if (is_array($filter)) {
            foreach ($filter as $field => $value) {
                $this->addFieldToFilter($field, $value);
            }
        }
        return $this;
    }
}
