<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    /** @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable */
    private $configurable;

    /** @var ConfigurableProductsProvider */
    private $configurableProductsProvider;

    /**
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     * @param ConfigurableProductsProvider $configurableProductsProvider
     */
    public function __construct(
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable,
        ConfigurableProductsProvider $configurableProductsProvider
    ) {
        $this->configurable = $configurable;
        $this->configurableProductsProvider = $configurableProductsProvider;
    }

    /**
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @param array $productIds
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMatchingProductIds(\Magento\CatalogRule\Model\Rule $rule, array $productIds)
    {
        $configurableProductIds = $this->configurableProductsProvider->getIds(array_keys($productIds));
        foreach ($configurableProductIds as $productId) {
            $subProductIds = $this->configurable->getChildrenIds($productId)[0];
            $parentValidationResult = isset($productIds[$productId])
                ? array_filter($productIds[$productId])
                : [];
            foreach ($subProductIds as $subProductId) {
                $childValidationResult = isset($productIds[$subProductId])
                    ? array_filter($productIds[$subProductId])
                    : [];
                $productIds[$subProductId] = $parentValidationResult + $childValidationResult;
            }
            unset($productIds[$productId]);
        }
        return $productIds;
    }
}
