<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider;

/**
 * Add configurable sub-products to reindex
 */
class ProductRuleReindex
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ConfigurableProductsProvider
     */
    private $configurableProductsProvider;

    /**
     * @param Configurable $configurable
     * @param ConfigurableProductsProvider $configurableProductsProvider
     */
    public function __construct(
        Configurable $configurable,
        ConfigurableProductsProvider $configurableProductsProvider
    ) {
        $this->configurable = $configurable;
        $this->configurableProductsProvider = $configurableProductsProvider;
    }

    /**
     * Reindex configurable product with sub-products
     *
     * @param ProductRuleIndexer $subject
     * @param \Closure $proceed
     * @param int $id
     * @return void
     */
    public function aroundExecuteRow(ProductRuleIndexer $subject, \Closure $proceed, $id)
    {
        $isReindexed = false;

        $configurableProductIds = $this->configurableProductsProvider->getIds([$id]);
        if ($configurableProductIds) {
            $subProducts = array_values($this->configurable->getChildrenIds($id)[0]);
            if ($subProducts) {
                $subject->executeList(array_merge([$id], $subProducts));
                $isReindexed = true;
            }
        }

        if (!$isReindexed) {
            $proceed($id);
        }
    }

    /**
     * Add sub-products to reindex
     *
     * @param ProductRuleIndexer $subject
     * @param array $ids
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecuteList(ProductRuleIndexer $subject, array $ids): array
    {
        $configurableProductIds = $this->configurableProductsProvider->getIds($ids);
        if ($configurableProductIds) {
            $subProducts = array_values($this->configurable->getChildrenIds($configurableProductIds)[0]);
            $ids = array_unique(array_merge($ids, $subProducts));
        }

        return [$ids];
    }
}
