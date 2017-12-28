<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Store\Model\Store;
use \Magento\Store\Model\StoreManagerInterface;

class ProductInMultipleStoresTest extends GraphQlAbstract
{

    /**
     *
     * @magentoApiDataFixture Magento/Store/_files/second_store.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProductFromSpecificStore()
    {
        $prductSku = 'simple';

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$prductSku}"}})
    {
        items {
            attribute_set_id
            created_at
            id
            name
            price {
                minimalPrice {
                    amount {
                        value
                        currency
                    }
                }
            }
            sku
            status
            type_id
            updated_at
            visibility
            weight
        }
    }
}
QUERY;

        /** @var \Magento\Store\Model\Store $store */
        $store =  ObjectManager::getInstance()->get(\Magento\Store\Model\Store::class);
        $storeCodeFromFixture = 'fixture_second_store';
        $storeId = $store->load($storeCodeFromFixture)->getStoreId();

        /** @var \Magento\Catalog\Model\Product $product */
        $product = ObjectManager::getInstance()->get(\Magento\Catalog\Model\Product::class);
        $product->load($product->getIdBySku($prductSku));

        $productNameInFixtureStore = 'Product\'s Name in Fixture Store';
        $product->setName($productNameInFixtureStore)->setStoreId($storeId)->save();

        $headerMap = ['Store' => $storeCodeFromFixture];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals($productNameInFixtureStore, $response['products']['items'][0]['name'],'Product name in fixture store is invalid.');
        $nameInDefaultStore = 'Simple Product';
        $headerMapDefault = ['Store' => 'default'];
        $response = $this->graphQlQuery($query, [], '', $headerMapDefault);
        $this->assertEquals(
            $nameInDefaultStore,
            $response['products']['items'][0]['name'],
            'Product name in default store is invalid.'
        );
    }
}
