<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\CatalogSearch\Controller\Advanced;

use Magento\CatalogSearch\Controller\Advanced\ResultTest as CatalogSearchResultTest;
use Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * Test cases for catalog advanced search using Elasticsearch as search engine.
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class ResultTest extends CatalogSearchResultTest
{
    /**
     * Elasticsearch7 engine configuration is also compatible with OpenSearch 1
     */
    private const ENGINE_SUPPORTED_VERSIONS = [
        7 => 'elasticsearch7',
        1 => 'elasticsearch7',
    ];

    /**
     * @var string
     */
    private $searchEngine;

    /**
     * Advanced search test by difference product attributes.
     *
     * @magentoAppArea frontend
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
        $searchEngine = $this->getInstalledSearchEngine();
        $currentEngine = $this->_objectManager->get(EngineResolverInterface::class)->getCurrentSearchEngine();
        $this->assertEquals($searchEngine, $currentEngine);
        parent::testExecute($searchParams);
    }

    /**
     * Returns installed on server search service.
     *
     * @return string
     */
    private function getInstalledSearchEngine(): string
    {
        if (!$this->searchEngine) {
            // phpstan:ignore "Class Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker not found."
            $version = $this->_objectManager->get(ElasticsearchVersionChecker::class)->getVersion();
            $this->searchEngine = self::ENGINE_SUPPORTED_VERSIONS[$version] ?? 'elasticsearch' . $version;
        }

        return $this->searchEngine;
    }
}
