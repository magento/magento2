<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductManagementInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductManagement implements ProductManagementInterface
{
    /**
     * @var CollectionFactory
     */
    protected $productsFactory;

    /**
     * @param CollectionFactory $productsFactory
     */
    public function __construct(CollectionFactory $productsFactory)
    {
        $this->productsFactory = $productsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($status = null)
    {
        $products = $this->productsFactory->create();
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $products */
        switch ($status) {
            case Status::STATUS_ENABLED:
                $products->addAttributeToFilter('status', Status::STATUS_ENABLED);
                break;
            case Status::STATUS_DISABLED:
                $products->addAttributeToFilter('status', Status::STATUS_DISABLED);
                break;
        }
        return $products->getSize();
    }
}
