<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch6\CatalogSearch\Controller\Advanced;

use Magento\CatalogSearch\Controller\Advanced\ResultTest as CatalogSearchResultTest;

/**
 * Test cases for catalog advanced search using Elasticsearch 6.0+ search engine.
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ResultTest extends CatalogSearchResultTest
{
    /**
     * Advanced search test by difference product attributes.
     *
     * @magentoAppArea frontend
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @dataProvider searchStringDataProvider
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @param array $searchParams
     * @return void
     */
    public function testExecute(array $searchParams): void
    {
        parent::testExecute($searchParams);
    }
}
