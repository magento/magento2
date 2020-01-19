<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\Search\Document as SearchDocument;
use Magento\Framework\Search\Request\Builder as SearchRequestBuilder;
use Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Search\Model\AdapterFactory as AdapterFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Search products by attribute value using mysql search engine.
 */
class DataProviderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SearchRequestConfig
     */
    private $searchRequestConfig;

    /**
     * @var SearchRequestBuilder
     */
    private $requestBuilder;

    /**
     * @var AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->searchRequestConfig = $this->objectManager->create(SearchRequestConfig::class);
        $this->requestBuilder = $this->objectManager->create(
            SearchRequestBuilder::class,
            ['config' => $this->searchRequestConfig]
        );
        $this->adapterFactory = $this->objectManager->get(AdapterFactory::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        parent::setUp();
    }

    /**
     * Search product by custom attribute value.
     *
     * @magentoConfigFixture default/catalog/search/engine mysql
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testSearchProductByAttribute(): void
    {
        $this->requestBuilder->bind('search_term', 'Option 1');
        $this->requestBuilder->setRequestName('quick_search_container');
        $queryRequest = $this->requestBuilder->create();
        $adapter = $this->adapterFactory->create();
        $queryResponse = $adapter->query($queryRequest);
        $actualIds = [];
        /** @var SearchDocument $document */
        foreach ($queryResponse as $document) {
            $actualIds[] = $document->getId();
        }
        $product = $this->productRepository->get('simple_for_search');
        $this->assertContains($product->getId(), $actualIds, 'Product not found by searchable attribute.');
    }
}
