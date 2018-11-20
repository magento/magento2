<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\ObjectManager;
use Magento\Catalog\Api\ProductRepositoryInterface;

class VariablesSupportQueryTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Tests that Introspection is disabled when not in developer mode
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_all_fields.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryObjectVariablesSupport()
    {
        $productSku = 'simple';

        $query
            = <<<'QUERY'
query GetProductsQuery($page: Int, $filterInput: ProductFilterInput){
  products(
    pageSize: 10
    currentPage: $page
    filter: $filterInput
    sort: {}
  ) {
    items {
    	name
    }
  }
}
QUERY;
        $variables = [
            'page' => 1,
            'filterInput' => [
                'sku' => [
                    'like' => '%simple%'
                ]
            ]
        ];

        $response = $this->graphQlQuery($query, $variables);
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($productSku, false, null, true);

        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertEquals(1, count($response['products']['items']));
        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertFields($product, $response['products']['items'][0]);
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertFields($product, $actualResponse)
    {
        $assertionMap = [
            ['response_field' => 'name', 'expected_value' => $product->getName()],
        ];

        $this->assertResponseFields($actualResponse, $assertionMap);
    }
}
