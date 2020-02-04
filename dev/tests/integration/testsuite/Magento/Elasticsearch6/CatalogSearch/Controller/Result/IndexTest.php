<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch6\CatalogSearch\Controller\Result;

use Magento\CatalogSearch\Controller\Result\IndexTest as CatalogSearchIndexTest;

/**
 * Test cases for catalog quick search using Elasticsearch 6.0+ search engine.
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
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @dataProvider searchStringDataProvider
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @param string $searchString
     * @return void
     */
    public function testExecute(string $searchString): void
    {
        parent::testExecute($searchString);
    }
}
