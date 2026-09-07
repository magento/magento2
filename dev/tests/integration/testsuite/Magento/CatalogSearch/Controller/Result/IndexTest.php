<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Controller\Result;

use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test cases for catalog quick search using search engine.
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class IndexTest extends AbstractController
{
    /**
     * Quick search test by difference product attributes.
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/CatalogSearch/_files/product_for_search.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     *
     * @param string $searchString
     * @return void
     */
    #[DataProvider('searchStringDataProvider')]
    public function testExecute(string $searchString): void
    {
        $this->getRequest()->setParam('q', $searchString);
        $this->dispatch('catalogsearch/result');
        $responseBody = $this->getResponse()->getBody();
        $this->assertStringContainsString('Simple product name', $responseBody);
    }

    /**
     * Data provider with strings for quick search.
     *
     * @return array
     */
    public static function searchStringDataProvider(): array
    {
        return [
            'search_product_by_name' => ['Simple product name'],
            'search_product_by_sku' => ['simple_for_search'],
            'search_product_by_description' => ['Product description'],
            'search_product_by_short_description' => ['Product short description'],
            'search_product_by_custom_attribute' => ['Option 1'],
        ];
    }
}
