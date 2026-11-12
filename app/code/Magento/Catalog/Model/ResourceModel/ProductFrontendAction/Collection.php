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

    /**
     * Include products without saved actions to validate them in the existing collection query.
     *
     * @param array $productIds
     * @return $this
     */
    public function includeExistingProducts(array $productIds)
    {
        $products = $this->getConnection()->select()
            ->distinct()
            ->from($this->getTable('catalog_product_entity'), ['existing_product_id' => 'entity_id'])
            ->where('entity_id IN (?)', $productIds);
        $actions = clone $this->getSelect();
        if ($this->isLoaded() && $this->_totalRecords === 0) {
            $actions->where('1 = 0');
            $this->_setIsLoaded(false);
            $this->_totalRecords = null;
        }

        $this->getSelect()->reset()
            ->from(['products' => $products], ['existing_product_id'])
            ->joinLeft(
                ['main_table' => $actions],
                'main_table.product_id = products.existing_product_id',
                ['main_table.*']
            );

        return $this;
    }

    /**
     * Keep product rows without saved actions distinct without changing action identifiers.
     *
     * @param \Magento\Framework\DataObject $item
     * @return mixed
     */
    protected function _getItemId(\Magento\Framework\DataObject $item)
    {
        return $item->getData('existing_product_id') ?? parent::_getItemId($item);
    }
}
