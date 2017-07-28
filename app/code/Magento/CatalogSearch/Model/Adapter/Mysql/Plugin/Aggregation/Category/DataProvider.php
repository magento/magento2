<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Plugin\Aggregation\Category;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\Dimension;

/**
 * Class \Magento\CatalogSearch\Model\Adapter\Mysql\Plugin\Aggregation\Category\DataProvider
 *
 * @since 2.0.0
 */
class DataProvider
{
    /**
     * @var Resource
     * @since 2.0.0
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     * @since 2.0.0
     */
    private $scopeResolver;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     * @since 2.0.0
     */
    protected $categoryFactory;

    /**
     * DataProvider constructor.
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param Resolver $layerResolver
     * @since 2.0.0
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        Resolver $layerResolver
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->layer = $layerResolver->get();
    }

    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject
     * @param callable|\Closure $proceed
     * @param BucketInterface $bucket
     * @param Dimension[] $dimensions
     *
     * @param Table $entityIdsTable
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function aroundGetDataSet(
        \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider $subject,
        \Closure $proceed,
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        if ($bucket->getField() == 'category_ids') {
            $currentScopeId = $this->scopeResolver->getScope($dimensions['scope']->getValue())->getId();
            $currentCategory = $this->layer->getCurrentCategory();

            $derivedTable = $this->resource->getConnection()->select();
            $derivedTable->from(
                ['main_table' => $this->resource->getTableName('catalog_category_product_index')],
                [
                    'value' => 'category_id'
                ]
            )->where('main_table.store_id = ?', $currentScopeId);
            $derivedTable->joinInner(
                ['entities' => $entityIdsTable->getName()],
                'main_table.product_id  = entities.entity_id',
                []
            );

            if (!empty($currentCategory)) {
                $derivedTable->join(
                    ['category' => $this->resource->getTableName('catalog_category_entity')],
                    'main_table.category_id = category.entity_id',
                    []
                )->where('`category`.`path` LIKE ?', $currentCategory->getPath() . '%')
                    ->where('`category`.`level` > ?', $currentCategory->getLevel());
            }
            $select = $this->resource->getConnection()->select();
            $select->from(['main_table' => $derivedTable]);
            return $select;
        }
        return $proceed($bucket, $dimensions, $entityIdsTable);
    }
}
