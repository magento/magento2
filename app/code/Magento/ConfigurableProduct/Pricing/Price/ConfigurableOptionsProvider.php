<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestSafetyInterface;

class ConfigurableOptionsProvider implements ConfigurableOptionsProviderInterface
{
    /** @var Configurable */
    private $configurable;

    /**
     * @var ProductInterface[]
     */
    private $products;

    /**
     * @param Configurable $configurable
     * @param ResourceConnection $resourceConnection
     * @param LinkedProductSelectBuilderInterface $linkedProductSelectBuilder
     * @param CollectionFactory $collectionFactory
     * @param RequestSafetyInterface $requestSafety
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Configurable $configurable,
        ResourceConnection $resourceConnection,
        LinkedProductSelectBuilderInterface $linkedProductSelectBuilder,
        CollectionFactory $collectionFactory,
        RequestSafetyInterface $requestSafety
    ) {
        $this->configurable = $configurable;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts(ProductInterface $product)
    {
        if (!isset($this->products[$product->getId()])) {
            $this->products[$product->getId()] = $this->configurable->getUsedProducts($product);
        }
        return $this->products[$product->getId()];
    }
}
