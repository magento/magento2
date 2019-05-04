<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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

        $this->assertGroupedProductItems($groupedProduct, $response['products']['items'][0]);
    }

    private function assertGroupedProductItems($product, $actualResponse)
    {
        $this->assertNotEmpty(
            $actualResponse['items'],
            "Precondition failed: 'grouped product items' must not be empty"
        );
        $this->assertEquals(2, count($actualResponse['items']));
        $groupedProductLinks = $product->getProductLinks();
        foreach ($actualResponse['items'] as $itemIndex => $bundleItems) {
            $this->assertNotEmpty($bundleItems);
            $associatedProductSku = $groupedProductLinks[$itemIndex]->getLinkedProductSku();

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
}
