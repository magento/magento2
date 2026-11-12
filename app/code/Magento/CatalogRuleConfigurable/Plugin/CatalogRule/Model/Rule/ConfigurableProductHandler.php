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
     * Inherited child IDs grouped by rule and website.
     *
     * @var array<int, array<int, array<int, bool>>>
     */
    private array $inheritedProductsByRule = [];

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
        $ruleId = (int)$rule->getId();
        unset($this->inheritedProductsByRule[$ruleId]);
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
            $antecedentRuleIds = $this->getAntecedentRuleIds((int)$productId, (array)$productData);
            if ($antecedentRuleIds) {
                $productIds[$productId]['has_antecedent_rule'] = true;
                $productIds[$productId]['antecedent_rule_ids'] = $antecedentRuleIds;
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
                    foreach (array_keys($parentValidationResult) as $websiteId) {
                        if (empty($childValidationResult[$websiteId])) {
                            $this->inheritedProductsByRule[$ruleId][$websiteId][$childrenProductId] = true;
                        }
                    }
                    $productIds[$childrenProductId] = $parentValidationResult + $childValidationResult;
                }
            }
            unset($productIds[$configurableProductId]);
        }

        return $productIds;
    }

    /**
     * Identify rules matched only through a configurable parent on each website.
     *
     * @param int $productId
     * @param array $validationByWebsite
     * @return array
     */
    private function getAntecedentRuleIds(int $productId, array $validationByWebsite): array
    {
        $ruleIds = [];
        foreach ($this->inheritedProductsByRule as $ruleId => $productsByWebsite) {
            foreach ($validationByWebsite as $websiteId => $isValid) {
                if ($isValid && isset($productsByWebsite[$websiteId][$productId])) {
                    $ruleIds[$websiteId][] = $ruleId;
                }
            }
        }

        return $ruleIds;
    }
}
