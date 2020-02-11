<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch6\CatalogSearch\Model\Indexer\fulltext\Action;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProviderTest as CatalogSearchDataProviderTest;
use Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * Search products by attribute value using mysql search engine.
 */
class DataProviderTest extends CatalogSearchDataProviderTest
{
    /**
     * Search product by custom attribute value.
     *
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @magentoDbIsolation disabled
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @return void
     */
    public function testSearchProductByAttribute(): void
    {
        $version = Bootstrap::getObjectManager()->get(ElasticsearchVersionChecker::class)->execute();
        $searchEngine = 'elasticsearch' . $version;
        $currentEngine = Bootstrap::getObjectManager()->get(EngineResolverInterface::class)->getCurrentSearchEngine();
        $this->assertEquals($searchEngine, $currentEngine);
        parent::testSearchProductByAttribute();
    }
}
