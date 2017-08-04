<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Layer\Category;

use Magento\Catalog\Model\Layer\FilterableAttributeListInterface;

/**
 * Class \Magento\Catalog\Model\Layer\Category\FilterableAttributeList
 *
 */
class FilterableAttributeList implements FilterableAttributeListInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * FilterableAttributeList constructor
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve list of filterable attributes
     *
     * @return array|\Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getList()
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection */
        $collection = $this->collectionFactory->create();
        $collection->setItemObjectClass(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->addStoreLabel($this->storeManager->getStore()->getId())
            ->setOrder('position', 'ASC');
        $collection = $this->_prepareAttributeCollection($collection);
        $collection->load();

        return $collection;
    }

    /**
     * Add filters to attribute collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected function _prepareAttributeCollection($collection)
    {
        $collection->addIsFilterableFilter();
        return $collection;
    }
}
