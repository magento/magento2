<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider;

/**
 * Add configurable sub products to catalog rule indexer on full reindex
 * @since 2.0.0
 */
class ConfigurableProductHandler
{
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     * @since 2.0.0
     */
    private $configurable;

    /**
     * @var \Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider
     * @since 2.0.0
     */
    private $configurableProductsProvider;

    /**
     * @var array
     * @since 2.1.0
     */
    private $childrenProducts = [];

    /**
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurable
     * @param ConfigurableProductsProvider $configurableProductsProvider
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function afterGetMatchingProductIds(\Magento\CatalogRule\Model\Rule $rule, array $productIds)
    {
        $configurableProductIds = $this->configurableProductsProvider->getIds(array_keys($productIds));
        foreach ($configurableProductIds as $productId) {
            if (!isset($this->childrenProducts[$productId])) {
                $this->childrenProducts[$productId] = $this->configurable->getChildrenIds($productId)[0];
            }
            $subProductIds = $this->childrenProducts[$productId];
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
