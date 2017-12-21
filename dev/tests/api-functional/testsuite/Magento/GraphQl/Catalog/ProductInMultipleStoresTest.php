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
            price
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
        $storeId = $store->getId();
        $storeCodeFromFixture = 'fixture_second_store';
        /** @var \Magento\Catalog\Model\Product $product */
        $product = ObjectManager::getInstance()->get(\Magento\Catalog\Model\Product::class);
        $product->load($prductSku);

        $productNameInFixtureStore = 'Product\'s Name in Fixture Store';
        $product->setName($productNameInFixtureStore)->setStoreId($storeId)->save();
        $productPriceInFixtureStore = 10.75;
        $product->setPrice($productPriceInFixtureStore)->setStoreId($storeId);

        $headerMap = ['Store' => $storeCodeFromFixture];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals($productNameInFixtureStore, $response['products']['items'][0]['name']);
        $this->assertEquals($productPriceInFixtureStore, $response['products']['items'][0]['price']);

        $this->assertBaseFields($product, $response['products']['items'][0]);
    }


    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertBaseFields($product, $actualResponse)
    {
        /**
         * ['product_object_field_name', 'expected_value']
         */
        $assertionMap = [
            ['response_field' => 'attribute_set_id', 'expected_value' => $product->getAttributeSetId()],
            ['response_field' => 'created_at', 'expected_value' => $product->getCreatedAt()],
            ['response_field' => 'id', 'expected_value' => $product->getId()],
            ['response_field' => 'name', 'expected_value' => $product->getName()],
            ['response_field' => 'price', 'expected_value' => $product->getPrice()],
            ['response_field' => 'sku', 'expected_value' => $product->getSku()],
            ['response_field' => 'status', 'expected_value' => $product->getStatus()],
            ['response_field' => 'type_id', 'expected_value' => $product->getTypeId()],
            ['response_field' => 'updated_at', 'expected_value' => $product->getUpdatedAt()],
            ['response_field' => 'visibility', 'expected_value' => $product->getVisibility()],
            ['response_field' => 'weight', 'expected_value' => $product->getWeight()],
        ];

        $this->assertResponseFields($actualResponse, $assertionMap);
    }

    /**
     * @param array $actualResponse
     * @param array $assertionMap ['response_field_name' => 'response_field_value', ...]
     *                         OR [['response_field' => $field, 'expected_value' => $value], ...]
     */
    private function assertResponseFields($actualResponse, $assertionMap)
    {
        foreach ($assertionMap as $key => $assertionData) {
            $expectedValue = isset($assertionData['expected_value'])
                ? $assertionData['expected_value']
                : $assertionData;
            $responseField = isset($assertionData['response_field']) ? $assertionData['response_field'] : $key;
            $this->assertNotNull(
                $expectedValue,
                "Value of '{$responseField}' field must not be NULL"
            );
            $this->assertEquals(
                $expectedValue,
                $actualResponse[$responseField],
                "Value of '{$responseField}' field in response does not match expected value: "
                . var_export($expectedValue, true)
            );
        }
    }
}
