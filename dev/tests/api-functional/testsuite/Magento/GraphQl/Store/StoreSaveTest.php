<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Store;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Store save tests
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation disabled
 */
class StoreSaveTest extends GraphQlAbstract
{
    /**
     * Test a product from newly created store
     *
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/Catalog/_files/category_product.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProductVisibleInNewStore()
    {
        $newStoreCode = 'fixture_second_store';
        $this->assertCategory($newStoreCode);
        $this->assertProduct($newStoreCode);
    }

    /**
     * Test product in store.
     *
     * @param string $storeCodeFromFixture
     * @throws \Exception
     */
    private function assertProduct(string $storeCodeFromFixture)
    {
        $productSku = 'simple333';
        $productNameInFixtureStore = 'Simple Product Three';

        $productsQuery = <<<QUERY
{
  products(filter: { sku: { eq: "%s" } }, sort: { name: ASC }) {
    items {
      id
      sku
      name
    }
  }
}
QUERY;
        $headerMap = ['Store' => $storeCodeFromFixture];
        $response = $this->graphQlQuery(
            sprintf($productsQuery, $productSku),
            [],
            '',
            $headerMap
        );
        $this->assertCount(
            1,
            $response['products']['items'],
            sprintf('Product with sku "%s" not found in store "%s"', $productSku, $storeCodeFromFixture)
        );
        $this->assertEquals(
            $productNameInFixtureStore,
            $response['products']['items'][0]['name'],
            'Product name in fixture store is invalid.'
        );
    }

    /**
     * Test category in store.
     *
     * @param string $storeCodeFromFixture
     * @throws \Exception
     */
    private function assertCategory(string $storeCodeFromFixture)
    {
        $categoryName = 'Category 1';
        $categoryQuery = <<<QUERY
{
    categoryList(filters: {name: {match: "%s"}}){
        id
        name
        url_key
        url_path
        children_count
        path
        position
    }
}
QUERY;
        $headerMap = ['Store' => $storeCodeFromFixture];
        $response = $this->graphQlQuery(
            sprintf($categoryQuery, $categoryName),
            [],
            '',
            $headerMap
        );
        $this->assertCount(
            1,
            $response['categoryList'],
            sprintf('Category with name "%s" not found in store "%s"', $categoryName, $storeCodeFromFixture)
        );
        $this->assertEquals(
            $categoryName,
            $response['categoryList'][0]['name'],
            'Category name in fixture store is invalid.'
        );
    }
}
