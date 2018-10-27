<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDbIsolation disabled
     */
    public function testSearchProductByAttribute()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

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
