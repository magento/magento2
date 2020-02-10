<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\CatalogSearch\Controller\Result;

use Magento\CatalogSearch\Controller\Result\IndexTest as CatalogSearchIndexTest;
use Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker;

/**
 * Test cases for catalog quick search using Elasticsearch 7.0+ search engine.
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class IndexTest extends CatalogSearchIndexTest
{
    /**
     * Quick search test by difference product attributes.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default/catalog/search/engine elasticsearch7
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDataFixture Magento/Elasticsearch7/_files/full_reindex.php
     * @dataProvider searchStringDataProvider
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @param string $searchString
     * @return void
     */
    public function testExecute(string $searchString): void
    {
        $checker = $this->_objectManager->get(ElasticsearchVersionChecker::class);

        if ($checker->execute() !== 7) {
            $this->markTestSkipped('The installed elasticsearch version isn\'t supported by test');
        }
        parent::testExecute($searchString);
    }
}
