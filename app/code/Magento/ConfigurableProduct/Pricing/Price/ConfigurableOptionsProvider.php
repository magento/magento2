<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Provide configurable child products for price calculation
 */
class ConfigurableOptionsProvider implements ConfigurableOptionsProviderInterface
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ProductInterface[]
     */
    private $products;

    /**
     * @param Configurable $configurable
     */
    public function __construct(
        Configurable $configurable
    ) {
        $this->configurable = $configurable;
    }

    /**
     * @inheritdoc
     */
    public function getProducts(ProductInterface $product)
    {
        if (!isset($this->products[$product->getId()])) {
            $this->products[$product->getId()] = $this->configurable->getUsedProducts($product);
        }
        return $this->products[$product->getId()];
    }
}
