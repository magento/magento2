<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

/**
 * @magentoDbIsolation disabled
 */
class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     */
    public function testSearchProductByAttribute()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $objectManager->create(\Magento\Indexer\Model\Indexer::class);
        $indexer->load('catalogsearch_fulltext');
        $indexer->reindexAll();

        $config = $objectManager->create(\Magento\Framework\Search\Request\Config::class);
        /** @var \Magento\Framework\Search\Request\Builder $requestBuilder */
        $requestBuilder = $objectManager->create(
            \Magento\Framework\Search\Request\Builder::class,
            ['config' => $config]
        );
        $requestBuilder->bind('search_term', 'VALUE1');
        $requestBuilder->setRequestName('quick_search_container');
        $queryRequest = $requestBuilder->create();
        /** @var \Magento\Framework\Search\Adapter\Mysql\Adapter $adapter */
        $adapter = $objectManager->create(\Magento\Framework\Search\Adapter\Mysql\Adapter::class);
        $queryResponse = $adapter->query($queryRequest);
        $actualIds = [];
        foreach ($queryResponse as $document) {
            /** @var \Magento\Framework\Api\Search\Document $document */
            $actualIds[] = $document->getId();
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class)->get('simple');
        $this->assertContains($product->getId(), $actualIds, 'Product not found by searchable attribute.');
    }
}
