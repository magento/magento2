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
 * @since 2.0.0
 */
class FilterableAttributeList implements FilterableAttributeListInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     * @since 2.0.0
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * FilterableAttributeList constructor
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _prepareAttributeCollection($collection)
    {
        $collection->addIsFilterableFilter();
        return $collection;
    }
}
