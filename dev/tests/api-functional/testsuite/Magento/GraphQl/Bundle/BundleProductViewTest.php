<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Bundle;

use Magento\Bundle\Model\Product\OptionList;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Query\EnumLookup;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class BundleProductViewTest extends GraphQlAbstract
{
    const KEY_PRICE_TYPE_FIXED = 'FIXED';
    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product.php
     */
    public function testAllFielsBundleProducts()
    {
        $productSku = 'bundle-product';
        $query
            = <<<QUERY
{
   products(filter: {sku: {eq: "{$productSku}"}})
   {
       items{
           id
           attribute_set_id    
           created_at
           name
           sku
           type_id
           updated_at
           ... on PhysicalProductInterface {
             weight
           }
           category_ids                
           ... on BundleProduct {
           dynamic_sku
            dynamic_price
            dynamic_weight
            price_view
            ship_bundle_items
             bundle_product_options {
               option_id
               title
               required
               type
               position
               sku
               values {
                 id
                 product_id
                 qty
                 position
                 is_default
                 price
                 price_type
                 can_change_quantity
               }
             }
             bundle_product_links {
               id
               name
               sku
             }
           }
       }
   }
   
}
QUERY;

        $response = $this->graphQlQuery($query);

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $bundleProduct = $productRepository->get($productSku, false, null, true);
        $this->assertBundleBaseFields($bundleProduct, $response['products']['items'][0]);

        $this->assertBundleProductOptions($bundleProduct, $response['products']['items'][0]);
        $this->assertNotEmpty(
            $response['products']['items'][0]['bundle_product_links'],
            "Precondition failed: 'bundle_product_links' must not be empty"
        );
    }

    private function assertBundleBaseFields($product, $actualResponse)
    {
        $assertionMap = [
            ['response_field' => 'sku', 'expected_value' => $product->getSku()],
            ['response_field' => 'type_id', 'expected_value' => $product->getTypeId()],
            ['response_field' => 'id', 'expected_value' => $product->getId()],
            ['response_field' => 'dynamic_price', 'expected_value' => !(bool)$product->getPriceType()],
            ['response_field' => 'dynamic_weight', 'expected_value' => !(bool)$product->getWeightType()],
            ['response_field' => 'dynamic_sku', 'expected_value' => !(bool)$product->getSkuType()]
        ];

        $this->assertResponseFields($actualResponse, $assertionMap);
    }

    /**
     * @param ProductInterface $product
     * @param  array $actualResponse
     */
    private function assertBundleProductOptions($product, $actualResponse)
    {
        $this->assertNotEmpty(
            $actualResponse['bundle_product_options'],
            "Precondition failed: 'bundle_product_options' must not be empty"
        );
        /** @var OptionList $optionList */
        $optionList = ObjectManager::getInstance()->get(\Magento\Bundle\Model\Product\OptionList::class);
        $options = $optionList->getItems($product);
        $option = $options[0];
        $bundleProductLinks = $option->getProductLinks();
        $bundleProductLink = $bundleProductLinks[0];
        $this->assertEquals(1, count($options));
        $this->assertResponseFields(
            $actualResponse['bundle_product_options'][0],
            [
                'option_id' => $option->getOptionId(),
                'title' => $option->getTitle(),
                'required' =>(bool)$option->getRequired(),
                'type' => $option->getType(),
                'position' => $option->getPosition(),
                'sku' => $option->getSku()
            ]
        );
        $this->assertResponseFields(
            $actualResponse['bundle_product_options'][0]['values'][0],
            [
                'id' => $bundleProductLink->getId(),
                'product_id' => $bundleProductLink->getEntityId(),
                'qty' => (int)$bundleProductLink->getQty(),
                'position' => $bundleProductLink->getPosition(),
                'is_default' => (bool)$bundleProductLink->getIsDefault(),
                'price' => (int)$bundleProductLink->getPrice(),
                 'price_type' => self::KEY_PRICE_TYPE_FIXED,
                'can_change_quantity' => $bundleProductLink->getCanChangeQuantity()
            ]
        );
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
