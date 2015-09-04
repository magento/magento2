<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Plugin\CatalogRule\Model\Indexer;

use \Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class ReindexProduct. Add configurable sub-products to reindex
 */
class ProductRuleReindex
{
    /** @var Configurable */
    private $configurable;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface */
    private $connection;

    /**
     * @param Configurable $configurable
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(
        Configurable $configurable,
        \Magento\Framework\App\Resource $resource
    ) {
        $this->configurable = $configurable;
        $this->connection = $resource->getConnection();
    }

    /**
     * @param \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject
     * @param \Closure $proceed
     * @param int $id
     */
    public function aroundExecuteRow(
        \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject,
        \Closure $proceed,
        $id
    ) {
        $configurableProductIds = $this->getConfigurableIds([$id]);
        $this->reindexSubProducts($configurableProductIds, $subject);
        if (!$configurableProductIds) {
            $proceed($id);
        }
    }

    /**
     * @param \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject
     * @param \Closure $proceed
     * @param array $ids
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecuteList(
        \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject,
        \Closure $proceed,
        array $ids
    ) {
        $configurableProductIds = $this->getConfigurableIds($ids);
        $subProducts = $this->reindexSubProducts($configurableProductIds, $subject);
        $ids = array_diff($ids, $configurableProductIds, $subProducts);
        if ($ids) {
            $proceed($ids);
        }
    }

    /**
     * @param array $ids
     * @return array
     */
    private function getConfigurableIds(array $ids)
    {
        return $this->connection->fetchCol(
            $this->connection
                ->select()
                ->from(['e' => $this->connection->getTableName('catalog_product_entity')], ['e.entity_id'])
                ->where('e.type_id = ?', Configurable::TYPE_CODE)
                ->where('e.entity_id IN (?)', $ids)
        );
    }

    /**
     * @param array $configurableIds
     * @param \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject
     *
     * @return array
     */
    private function reindexSubProducts(
        array $configurableIds,
        \Magento\CatalogRule\Model\Indexer\Product\ProductRuleIndexer $subject
    ) {
        $subProducts = [];
        if ($configurableIds) {
            $subProducts = array_values($this->configurable->getChildrenIds($configurableIds)[0]);
            if ($subProducts) {
                $subject->executeList($subProducts);
            }
        }
        return $subProducts;
    }
}
