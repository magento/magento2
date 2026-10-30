<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\ResourceModel\ProductFrontendAction;

/**
 * Collection of Product Frontend Actions
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initializes Product Frontend Actions collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Catalog\Model\ProductFrontendAction::class,
            \Magento\Catalog\Model\ResourceModel\ProductFrontendAction::class
        );
    }

    /**
     * Adds filtering by customer or visitor to collection
     *
     * @param int $customerId
     * @param int $visitorId
     * @return $this
     */
    public function addFilterByUserIdentities($customerId, $visitorId)
    {
        if ($customerId) {
            $this->addFieldToFilter('customer_id', $customerId);
        } elseif ($visitorId) {
            $this->addFieldToFilter('visitor_id', $visitorId);
        } else {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        }

        return $this;
    }
}
