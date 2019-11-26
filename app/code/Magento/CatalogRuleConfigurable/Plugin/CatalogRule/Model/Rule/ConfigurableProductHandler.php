<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule;

use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider;

/**
 * Add configurable sub products to catalog rule indexer on reindex
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
     * Add configurable products during setting product ids for filtering
     *
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @param int|array $productIds
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetProductsFilter(\Magento\CatalogRule\Model\Rule $rule, $productIds)
    {
        if ($productIds) {
            $configurableProductIds = $this->configurable->getParentIdsByChild($productIds);
            if ($configurableProductIds) {
                $productIds = array_merge((array) $productIds, $configurableProductIds);

            }
        }

        return [
            $productIds,
        ];
    }

    /**
     * Add configurable products for matched products
     *
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @param array $productIds
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
