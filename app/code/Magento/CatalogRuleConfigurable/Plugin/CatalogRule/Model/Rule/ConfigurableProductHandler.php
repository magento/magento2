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
 */
class ConfigurableProductHandler
{
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    private $configurable;

    /**
     * @var \Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider
     */
    private $configurableProductsProvider;

    /**
     * @var array
     */
    private $childrenProducts = [];

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
     * Match configurable child products if configurable product match the condition
     *
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @param \Closure $proceed
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundGetMatchingProductIds(
        \Magento\CatalogRule\Model\Rule $rule,
        \Closure $proceed
    ) {
        $productsFilter = $rule->getProductsFilter() ? (array) $rule->getProductsFilter() : [];
        if ($productsFilter) {
            $parentProductIds = $this->configurable->getParentIdsByChild($productsFilter);
            $rule->setProductsFilter(array_unique(array_merge($productsFilter, $parentProductIds)));
        }

        $productIds = $proceed();

        $configurableProductIds = $this->configurableProductsProvider->getIds(array_keys($productIds));
        foreach ($configurableProductIds as $productId) {
            if (!isset($this->childrenProducts[$productId])) {
                $this->childrenProducts[$productId] = $this->configurable->getChildrenIds($productId)[0];
            }
            $subProductIds = $this->childrenProducts[$productId];
            $parentValidationResult = isset($productIds[$productId])
                ? array_filter($productIds[$productId])
                : [];
            $processAllChildren = !$productsFilter || in_array($productId, $productsFilter);
            foreach ($subProductIds as $subProductId) {
                if ($processAllChildren || in_array($subProductId, $productsFilter)) {
                    $childValidationResult = isset($productIds[$subProductId])
                        ? array_filter($productIds[$subProductId])
                        : [];
                    $productIds[$subProductId] = $parentValidationResult + $childValidationResult;
                }

            }
            unset($productIds[$productId]);
        }
        return $productIds;
    }
}
