<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Adapter\Mysql\BaseSelectStrategy;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainerFactory;

class BaseSelectAttributesSearchStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BaseSelectAttributesSearchStrategy
     */
    private $baseSelectAttributesSearchStrategy;

    /**
     * @var SelectContainerFactory
     */
    private $selectContainerFactory;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    protected function setUp()
    {
        $this->baseSelectAttributesSearchStrategy = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(BaseSelectAttributesSearchStrategy::class);

        $this->selectContainerFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(SelectContainerFactory::class);

        $this->resource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(ResourceConnection::class);

        $this->storeManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(StoreManagerInterface::class);

        $this->scopeResolver = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(IndexScopeResolver::class);
    }

    public function testCreateBaseSelectWithoutFullTextSearch()
    {
        $selectContainer = $this->getSelectContainerWithoutFullTextSearch();
        $selectContainer = $this->baseSelectAttributesSearchStrategy->createBaseSelect($selectContainer);
        $select = $selectContainer->getSelect();
        $expectedSelect = $this->getMainSelect();

        $this->assertEquals((string) $expectedSelect, (string) $select);
    }

    public function testCreateBaseSelectWithFullTextSearch()
    {
        $selectContainer = $this->getSelectContainerWithFullTextSearch();
        $selectContainer = $this->baseSelectAttributesSearchStrategy->createBaseSelect($selectContainer);
        $select = $selectContainer->getSelect();
        $expectedSelect = $this->getFulltextSelect();

        $this->assertEquals((string) $expectedSelect, (string) $select);
    }

    private function getMainSelect()
    {
        $select = $this->resource->getConnection()->select();
        $select->distinct()
            ->from(
                ['search_index' => $this->resource->getTableName('catalog_product_index_eav')],
                ['entity_id' => 'entity_id']
            )->where(
                $this->resource->getConnection()->quoteInto(
                    'search_index.store_id = ?',
                    $this->storeManager->getStore()->getId()
                )
            );

        return $select;
    }

    private function getFulltextSelect()
    {
        $select = $this->resource->getConnection()->select();
        $select->distinct()
            ->from(
                ['eav_index' => $this->resource->getTableName('catalog_product_index_eav')],
                ['entity_id' => 'entity_id']
            )->where(
                $this->resource->getConnection()->quoteInto(
                    'eav_index.store_id = ?',
                    $this->storeManager->getStore()->getId()
                )
            )->joinInner(
                ['search_index' => $this->scopeResolver->resolve('', [])],
                'eav_index.entity_id = search_index.entity_id',
                []
            )->joinInner(
                ['cea' => $this->resource->getTableName('catalog_eav_attribute')],
                'search_index.attribute_id = cea.attribute_id',
                []
            );

        return $select;
    }

    private function getSelectContainerWithFullTextSearch()
    {
        return $this->selectContainerFactory->create(
            [
                'nonCustomAttributesFilters' => [],
                'customAttributesFilters' => [],
                'visibilityFilter' => null,
                'isFullTextSearchRequired' => true,
                'isShowOutOfStockEnabled' => false,
                'usedIndex' => '',
                'dimensions' => [],
                'select' => $this->resource->getConnection()->select()
            ]
        );
    }

    private function getSelectContainerWithoutFullTextSearch()
    {
        return $this->selectContainerFactory->create(
            [
                'nonCustomAttributesFilters' => [],
                'customAttributesFilters' => [],
                'visibilityFilter' => null,
                'isFullTextSearchRequired' => false,
                'isShowOutOfStockEnabled' => false,
                'usedIndex' => '',
                'dimensions' => [],
                'select' => $this->resource->getConnection()->select()
            ]
        );
    }
}
