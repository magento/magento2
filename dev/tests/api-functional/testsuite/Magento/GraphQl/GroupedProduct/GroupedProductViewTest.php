<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GroupedProduct;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class to test GraphQl response with grouped products
 */
class GroupedProductViewTest extends GraphQlAbstract
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped.php
     */
    public function testAllFieldsGroupedProduct()
    {
        $productSku = 'grouped-product';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "{$productSku}"}}) {
    items {
      id
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
        product_links{
          linked_product_sku
          position
          link_type
        }
      }
    }
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $groupedProduct = $this->productRepository->get($productSku, false, null, true);

        $this->assertNotEmpty(
            $response['products']['items'][0]['items'],
            "Precondition failed: 'Grouped product items' must not be empty"
        );
        $this->assertGroupedProductItems($groupedProduct, $response['products']['items'][0]['items']);
        $this->assertNotEmpty(
            $response['products']['items'][0]['product_links'],
            "Precondition failed: 'Linked product items' must not be empty"
        );
        $this->assertProductLinks($groupedProduct, $response['products']['items'][0]['product_links']);
    }

    /**
     * @param ProductInterface $product
     * @param array $items
     */
    private function assertGroupedProductItems(ProductInterface $product, array $items): void
    {
        $this->assertCount(2, $items);
        $groupedProductLinks = $product->getProductLinks();
        foreach ($items as $itemIndex => $bundleItem) {
            $this->assertNotEmpty($bundleItem);
            $associatedProductSku = $groupedProductLinks[$itemIndex]->getLinkedProductSku();
            $associatedProduct = $this->productRepository->get($associatedProductSku);

            $this->assertEquals(
                $groupedProductLinks[$itemIndex]->getExtensionAttributes()->getQty(),
                $bundleItem['qty']
            );
            $this->assertEquals(
                $groupedProductLinks[$itemIndex]->getPosition(),
                $bundleItem['position']
            );
            $this->assertResponseFields(
                $bundleItem['product'],
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
     * @param ProductInterface $product
     * @param array $links
     * @return void
     */
    private function assertProductLinks(ProductInterface $product, array $links): void
    {
        $this->assertCount(2, $links);
        $productLinks = $product->getProductLinks();
        foreach ($links as $itemIndex => $linkedItem) {
            $this->assertNotEmpty($linkedItem);
            $this->assertEquals(
                $productLinks[$itemIndex]->getPosition(),
                $linkedItem['position']
            );
            $this->assertEquals(
                $productLinks[$itemIndex]->getLinkedProductSku(),
                $linkedItem['linked_product_sku']
            );
            $this->assertEquals(
                $productLinks[$itemIndex]->getLinkType(),
                $linkedItem['link_type']
            );
        }
    }
}
