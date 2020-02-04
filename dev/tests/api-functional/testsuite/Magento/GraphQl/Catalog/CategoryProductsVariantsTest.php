<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test of getting child products info of configurable product on category request
 */
class CategoryProductsVariantsTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetSimpleProductsFromCategory()
    {
        $query
            = <<<QUERY
{
  category(id: 2) {
    id
    name
    products {
      items {
        sku
        ... on ConfigurableProduct {
          variants {
            product {
              sku
            }
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
        $product = $productRepository->get('simple_10', false, null, true);

        $this->assertArrayHasKey('variants', $response['category']['products']['items'][0]);
        $this->assertCount(2, $response['category']['products']['items'][0]['variants']);
        $this->assertSimpleProductFields($product, $response['category']['products']['items'][0]['variants'][0]);
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertSimpleProductFields($product, $actualResponse)
    {
        $assertionMap = [
            [
                'response_field' => 'product', 'expected_value' => [
                    "sku" => $product->getSku()
                ]
            ],
        ];

        $this->assertResponseFields($actualResponse, $assertionMap);
    }
}
