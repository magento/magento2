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
    /** @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable */
    private $configurable;

    /** @var ConfigurableProductsProvider */
    private $configurableProductsProvider;

    /** @var array */
    private $subProductsValidationResults = [];

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
     */
    public function afterGetMatchingProductIds(\Magento\CatalogRule\Model\Rule $rule, array $productIds)
    {
        $configurableProductIds = $this->configurableProductsProvider->getIds(array_keys($productIds));
        foreach ($configurableProductIds as $productId) {
            $subProductsIds = $this->configurable->getChildrenIds($productId)[0];
            $parentValidationResult = $productIds[$productId];
            foreach ($subProductsIds as $subProductsId) {
                $productIds[$subProductsId] = $this->getSubProductValidationResult(
                    $rule->getId(),
                    $subProductsId,
                    $parentValidationResult
                );
            }
            unset($productIds[$productId]);
        }
        return $productIds;
    }

    /**
     * Return validation result for sub-product.
     * If any of configurable product is valid for current rule, then their sub-product must be valid too
     *
     * @param int $urlId
     * @param int $subProductsId
     * @param array $parentValidationResult
     * @return array
     */
    private function getSubProductValidationResult($urlId, $subProductsId, $parentValidationResult)
    {
        if (!isset($this->subProductsValidationResults[$urlId][$subProductsId])) {
            $this->subProductsValidationResults[$urlId][$subProductsId] = array_filter($parentValidationResult);
        } else {
            $parentValidationResult = array_intersect_key(
                $this->subProductsValidationResults[$urlId][$subProductsId] + $parentValidationResult,
                $parentValidationResult
            );
            $this->subProductsValidationResults[$urlId][$subProductsId] = $parentValidationResult;
        }
        return $parentValidationResult;
    }
}
