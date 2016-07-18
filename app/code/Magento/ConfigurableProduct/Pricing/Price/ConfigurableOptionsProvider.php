<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;

class ConfigurableOptionsProvider implements ConfigurableOptionsProviderInterface
{
    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable */
    private $configurable;

    /**
     * @var \Magento\Framework\App\RequestSafetyInterface
     */
    private $requestSafety;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\ProductProviderByPriceInterface
     */
    private $productProviderByPrice;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductInterface[]
     */
    private $products;

    /**
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Catalog\Model\ResourceModel\Product\ProductProviderByPriceInterface $productProviderByPrice
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestSafetyInterface $requestSafety
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\ResourceModel\Product\ProductProviderByPriceInterface $productProviderByPrice,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestSafetyInterface $requestSafety
    ) {
        $this->configurable = $configurable;
        $this->resource = $resourceConnection;
        $this->productProviderByPrice = $productProviderByPrice;
        $this->collectionFactory = $collectionFactory;
        $this->requestSafety = $requestSafety;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts(ProductInterface $product)
    {
        if (!isset($this->products[$product->getId()])) {
            if ($this->requestSafety->isSafeMethod()) {
                $productIds = $this->resource->getConnection()->fetchCol(
                    '(' . implode(') UNION (', $this->productProviderByPrice->getSelect($product->getId())) . ')'
                );

                $this->products[$product->getId()] = $this->collectionFactory->create()
                    ->addIdFilter($productIds)
                    ->addPriceData();
            } else {
                $this->products[$product->getId()] = $this->configurable->getUsedProducts($product);
            }
        }
        return $this->products[$product->getId()];
    }
}
