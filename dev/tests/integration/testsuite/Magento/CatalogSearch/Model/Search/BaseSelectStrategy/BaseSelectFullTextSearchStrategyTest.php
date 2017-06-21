<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\BaseSelectStrategy;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\FrontendResource;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainerFactory;

class BaseSelectFullTextSearchStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BaseSelectFullTextSearchStrategy
     */
    private $baseSelectFullTextSearchStrategy;

    /**
     * @var SelectContainerFactory
     */
    private $selectContainerFactory;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    protected function setUp()
    {
        $this->baseSelectFullTextSearchStrategy = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(BaseSelectFullTextSearchStrategy::class);

        $this->selectContainerFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(SelectContainerFactory::class);

        $this->resource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(ResourceConnection::class);

        $this->scopeResolver = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(IndexScopeResolver::class);
    }

    public function testCreateBaseSelect()
    {
        $selectContainer = $this->getSelectContainerWithFullTextSearch();
        $selectContainer = $this->baseSelectFullTextSearchStrategy->createBaseSelect($selectContainer);
        $select = $selectContainer->getSelect();
        $expectedSelect = $this->getExpectedSelect();

        $this->assertEquals((string) $expectedSelect, (string) $select);
    }

    private function getExpectedSelect()
    {
        $select = $this->resource->getConnection()->select();
        $select->from(
                ['search_index' => $this->scopeResolver->resolve('', [])],
                ['entity_id' => 'entity_id']
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
}
