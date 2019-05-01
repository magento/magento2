<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

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
    product_use_categories,
    save_rewrites_history,
    title_separator,
    category_canonical_tag,
    product_canonical_tag,
    list_mode,
    grid_per_page_values,
    list_per_page_values,
    grid_per_page,
    list_per_page,
    flat_catalog_category,
    catalog_default_sort_by,
    parse_url_directives,
    remember_pagination
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('storeConfig', $response);

        //TODO: provide assertions after unmarking test as incomplete
    }
}
