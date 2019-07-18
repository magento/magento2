<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the GraphQL endpoint's StoreConfigs query for Catalog Configs
 */
class StoreConfigTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Store/_files/store.php
     * @magentoConfigFixture default_store catalog/seo/product_url_suffix test_product_suffix
     * @magentoConfigFixture default_store catalog/seo/category_url_suffix test_category_suffix
     * @magentoConfigFixture default_store catalog/seo/title_separator ___
     * @magentoConfigFixture default_store catalog/frontend/list_mode 2
     * @magentoConfigFixture default_store catalog/frontend/grid_per_page_values 16
     * @magentoConfigFixture default_store catalog/frontend/list_per_page_values 8
     * @magentoConfigFixture default_store catalog/frontend/grid_per_page 16
     * @magentoConfigFixture default_store catalog/frontend/list_per_page 8
     * @magentoConfigFixture default_store catalog/frontend/default_sort_by asc
     */
    public function testGetStoreConfig()
    {
        $query
            = <<<QUERY
{
  storeConfig{
    product_url_suffix,
    category_url_suffix,
    title_separator,
    list_mode,
    grid_per_page_values,
    list_per_page_values,
    grid_per_page,
    list_per_page,
    catalog_default_sort_by
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('storeConfig', $response);

        $this->assertEquals('test_product_suffix', $response['storeConfig']['product_url_suffix']);
        $this->assertEquals('test_category_suffix', $response['storeConfig']['category_url_suffix']);
        $this->assertEquals('___', $response['storeConfig']['title_separator']);
        $this->assertEquals('2', $response['storeConfig']['list_mode']);
        $this->assertEquals('16', $response['storeConfig']['grid_per_page_values']);
        $this->assertEquals(16, $response['storeConfig']['grid_per_page']);
        $this->assertEquals('8', $response['storeConfig']['list_per_page_values']);
        $this->assertEquals(8, $response['storeConfig']['list_per_page']);
        $this->assertEquals('asc', $response['storeConfig']['catalog_default_sort_by']);
    }
}
