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
    protected function setUp()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/167');
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/store.php
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

        //TODO: provide assertions after unmarking test as incomplete
    }
}
