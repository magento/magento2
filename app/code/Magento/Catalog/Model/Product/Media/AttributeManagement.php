<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Media;

use Magento\Catalog\Api\ProductMediaAttributeManagementInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;

class AttributeManagement implements ProductMediaAttributeManagementInterface
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($attributeSetName)
    {
        /** @var \Magento\Catalog\Model\Resource\Product\Attribute\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setAttributeSetFilterBySetName($attributeSetName, Product::ENTITY);
        $collection->setFrontendInputTypeFilter('media_image');
        $collection->addStoreLabel($this->storeManager->getStore()->getId());

        return $collection->getItems();
    }
}
