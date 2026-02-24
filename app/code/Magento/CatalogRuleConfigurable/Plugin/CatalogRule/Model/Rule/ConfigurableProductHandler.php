<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule;

use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProductsResourceModel;

/**
 * Add configurable sub products to catalog rule indexer on full reindex
 */
class ConfigurableProductHandler
{
    /**
     * @var ConfigurableProductsResourceModel
     */
    private ConfigurableProductsResourceModel $configurable;

    /**
     * @var ConfigurableProductsProvider
     */
    private ConfigurableProductsProvider $configurableProductsProvider;

    /**
     * @var array
     */
    private array $childrenProducts = [];

    /**
     * @param ConfigurableProductsResourceModel $configurable
     * @param ConfigurableProductsProvider $configurableProductsProvider
     */
    public function __construct(
        ConfigurableProductsResourceModel $configurable,
        ConfigurableProductsProvider     $configurableProductsProvider
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function aroundGetMatchingProductIds(
        \Magento\CatalogRule\Model\Rule $rule,
        \Closure $proceed
    ): array {
        $productsFilter = $rule->getProductsFilter() ? (array) $rule->getProductsFilter() : [];
        if ($productsFilter) {
            $rule->setProductsFilter(
                array_unique(
                    array_merge(
                        $productsFilter,
                        $this->configurable->getParentIdsByChild($productsFilter)
                    )
                )
            );
        }

        $productIds = $proceed();
        foreach ($productIds as $productId => $productData) {
            if ($this->hasAntecedentRule((int) $productId)) {
                $productIds[$productId]['has_antecedent_rule'] = true;
            }
        }

        foreach ($this->configurableProductsProvider->getIds(array_keys($productIds)) as $configurableProductId) {
            if (!isset($this->childrenProducts[$configurableProductId])) {
                $this->childrenProducts[$configurableProductId] =
                    $this->configurable->getChildrenIds($configurableProductId)[0];
            }

            $parentValidationResult = isset($productIds[$configurableProductId])
                ? array_filter($productIds[$configurableProductId])
                : [];
            $processAllChildren = !$productsFilter || in_array($configurableProductId, $productsFilter);
            foreach ($this->childrenProducts[$configurableProductId] as $childrenProductId) {
                if ($processAllChildren || in_array($childrenProductId, $productsFilter)) {
                    $childValidationResult = isset($productIds[$childrenProductId])
                        ? array_filter($productIds[$childrenProductId])
                        : [];
                    $productIds[$childrenProductId] = $parentValidationResult + $childValidationResult;
                }
            }
            unset($productIds[$configurableProductId]);
        }

        return $productIds;
    }

    /**
     * Check if simple product has previously applied rule.
     *
     * @param int $productId
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function hasAntecedentRule(int $productId): bool
    {
        foreach ($this->childrenProducts as $parent => $children) {
            if (in_array($productId, $children)) {
                return true;
            }
        }

        return false;
    }
}
