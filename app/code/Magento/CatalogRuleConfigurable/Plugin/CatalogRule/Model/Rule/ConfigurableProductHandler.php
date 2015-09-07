<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider;

/**
 * Add configurable sub products to catalog rule indexer on full reindex
 */
class ConfigurableProductHandler
{
    /** @var \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable */
    private $configurable;

    /** @var ConfigurableProductsProvider */
    private $configurableProductsProvider;

    /**
     * @param \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable $configurable
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable $configurable,
        ConfigurableProductsProvider $configurableProductsProvider
    ) {
        $this->configurable = $configurable;
        $this->configurableProductsProvider = $configurableProductsProvider;
    }

    /**
     * @param \Magento\CatalogRule\Model\Rule $subject
     * @param array $productIds
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMatchingProductIds(
        \Magento\CatalogRule\Model\Rule $subject,
        array $productIds
    ) {
        $configurableProductIds = $this->configurableProductsProvider->getIds(array_keys($productIds));
        foreach ($configurableProductIds as $productId) {
            $variationsIds = $this->configurable->getChildrenIds($productId);
            $websitesValid = $productIds[$productId];
            foreach ($variationsIds[0] as $variationId) {
                $productIds[$variationId] = $websitesValid;
            }
            unset($productIds[$productId]);
        }

        return $productIds;
    }
}
