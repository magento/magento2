<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Provide configurable child products for price calculation
 */
class ConfigurableOptionsProvider implements ConfigurableOptionsProviderInterface, ResetAfterRequestInterface
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ProductInterface[]|null
     */
    private $products;

    /**
     * @var ConfigurableOptionsFilterInterface
     */
    private $configurableOptionsFilter;

    /**
     * @param Configurable $configurable
     * @param ConfigurableOptionsFilterInterface|null $configurableOptionsFilter
     */
    public function __construct(
        Configurable $configurable,
        ?ConfigurableOptionsFilterInterface $configurableOptionsFilter = null
    ) {
        $this->configurable = $configurable;
        $this->configurableOptionsFilter = $configurableOptionsFilter
            ?? ObjectManager::getInstance()->get(ConfigurableOptionsFilterInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getProducts(ProductInterface $product)
    {
        if (!isset($this->products[$product->getId()])) {
            $this->products[$product->getId()] = $this->configurableOptionsFilter->filter(
                $product,
                $this->configurable->getUsedProducts($product)
            );
        }
        return $this->products[$product->getId()];
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->products = null;
    }
}
