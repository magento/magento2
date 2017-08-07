<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestSafetyInterface;

/**
 * Class \Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProvider
 *
 * @since 2.1.1
 */
class ConfigurableOptionsProvider implements ConfigurableOptionsProviderInterface
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     * @since 2.1.1
     */
    private $configurable;

    /**
     * @var ProductInterface[]
     * @since 2.1.1
     */
    private $products;

    /**
     * @param Configurable $configurable
     * @param ResourceConnection $resourceConnection
     * @param LinkedProductSelectBuilderInterface $linkedProductSelectBuilder
     * @param CollectionFactory $collectionFactory
     * @param RequestSafetyInterface $requestSafety
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.1
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
     * @since 2.1.1
     */
    public function getProducts(ProductInterface $product)
    {
        if (!isset($this->products[$product->getId()])) {
            $this->products[$product->getId()] = $this->configurable->getUsedProducts($product);
        }
        return $this->products[$product->getId()];
    }
}
