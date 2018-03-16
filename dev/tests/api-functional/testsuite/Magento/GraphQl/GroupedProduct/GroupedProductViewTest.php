<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\GroupedProduct;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GroupedProductViewTest extends GraphQlAbstract
{

    /**
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped.php
     */
    public function testAllFieldsGroupedProduct()
    {
        $productSku = 'grouped-product';
        $query
            = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {    
      id
      attribute_set_id
      created_at
      name
      sku
      type_id     
      ... on GroupedProduct {
        items{
          qty
          position
          product{
            sku
            name
            type_id
            url_key            
          }
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        $groupedProduct = $productRepository->get($productSku, false, null, true);
      // $groupedProductLinks = $groupedProduct->getProductLinks();
        $this->assertGroupedProductItems($groupedProduct, $response['products']['items'][0]);
    }

    private function assertGroupedProductItems($product, $actualResponse)
    {
        $this->assertNotEmpty(
            $actualResponse['items'],
            "Precondition failed: 'bundle product items' must not be empty"
        );
        $this->assertEquals(2, count($actualResponse['items']));
        $groupedProductLinks = $product->getProductLinks();
        foreach ($actualResponse['items'] as $itemIndex => $bundleItems) {
            $this->assertNotEmpty($bundleItems);
            $associatedProductSku = $groupedProductLinks[$itemIndex]->getLinkedProductSku();
          //  $products = ObjectManager::getInstance()->get(\Magento\Catalog\Model\Product::class);
          //  $associatedProduct = $products->getIdBySku($associatedProductSku);
            $productsRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
            /** @var \Magento\Catalog\Model\Product $associatedProduct */
            $associatedProduct = $productsRepository->get($associatedProductSku);

            $this->assertEquals(
                $groupedProductLinks[$itemIndex]->getExtensionAttributes()->getQty(),
                $actualResponse['items'][$itemIndex]['qty']
            );
            $this->assertEquals(
                $groupedProductLinks[$itemIndex]->getPosition(),
                $actualResponse['items'][$itemIndex]['position']
            );
            $this->assertResponseFields(
                $actualResponse['items'][$itemIndex]['product'],
                [
                  'sku' => $associatedProductSku,
                  'type_id' => $groupedProductLinks[$itemIndex]->getLinkedProductType(),
                  'url_key'=> $associatedProduct->getUrlKey(),
                  'name' => $associatedProduct->getName()

                ]
            );
        }
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
