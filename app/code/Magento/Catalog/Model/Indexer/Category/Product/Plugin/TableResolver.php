<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Framework\Search\Request\Dimension;

/**
 * Class that replace catalog_category_product_index table name  on  the table name segmented per store
 */
class TableResolver
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var IndexScopeResolver
     */
    private $tableResolver;

    /**
     * @param StoreManagerInterface $storeManager
     * @param IndexScopeResolver $tableResolver
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        IndexScopeResolver $tableResolver
    ) {
        $this->storeManager = $storeManager;
        $this->tableResolver = $tableResolver;
    }

    /**
     * replacing catalog_category_product_index table name on the table name segmented per store
     *
     * @param ResourceConnection $subject
     * @param string $result
     * @param string|string[] $modelEntity
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return string
     */
    public function afterGetTableName(
        \Magento\Framework\App\ResourceConnection $subject,
        string $result,
        $modelEntity
    ) {
        if (!is_array($modelEntity) && $modelEntity === AbstractAction::MAIN_INDEX_TABLE) {
            $catalogCategoryProductDimension = new Dimension(
                \Magento\Store\Model\Store::ENTITY,
                $this->storeManager->getStore()->getId()
            );

            $tableName = $this->tableResolver->resolve(
                AbstractAction::MAIN_INDEX_TABLE,
                [
                    $catalogCategoryProductDimension
                ]
            );
            return $tableName;
        }
        return $result;
    }
}
