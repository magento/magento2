<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository as ProductRepository;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Api\Search\Document as SearchDocument;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Search\AdapterInterface as AdapterInterface;
use Magento\Framework\Search\Request\Builder as SearchRequestBuilder;
use Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Search\Model\AdapterFactory as AdapterFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        /*
         * Due to insufficient search engine isolation for Elasticsearch, this class must explicitly perform
         * a fulltext reindex prior to running its tests.
         *
         * This should be removed upon completing MC-19455.
         */
        $indexRegistry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);
        $fulltextIndexer = $indexRegistry->get(Fulltext::INDEXER_ID);
        $fulltextIndexer->reindexAll();
    }

    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDbIsolation disabled
     */
    public function testSearchProductByAttribute()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();

        /** @var SearchRequestConfig $config */
        $config = $objectManager->create(SearchRequestConfig::class);

        /** @var SearchRequestBuilder $requestBuilder */
        $requestBuilder = $objectManager->create(
            SearchRequestBuilder::class,
            ['config' => $config]
        );

        $requestBuilder->bind('search_term', 'VALUE1');
        $requestBuilder->setRequestName('quick_search_container');
        $queryRequest = $requestBuilder->create();

        /** @var AdapterInterface $adapter */
        $adapterFactory = $objectManager->create(AdapterFactory::class);
        $adapter = $adapterFactory->create();
        $queryResponse = $adapter->query($queryRequest);
        $actualIds = [];

        foreach ($queryResponse as $document) {
            /** @var SearchDocument $document */
            $actualIds[] = $document->getId();
        }

        /** @var Product $product */
        $product = $objectManager->create(ProductRepository::class)->get('simple');
        $this->assertContains($product->getId(), $actualIds, 'Product not found by searchable attribute.');
    }
}
