<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Bundle;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CompareArraysRecursively;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Bundle product with multiple options test.
 */
class BundleProductMultipleOptionsTest extends GraphQlAbstract
{
    /**
     * @var CompareArraysRecursively
     */
    private $compareArraysRecursively;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->compareArraysRecursively = $objectManager->create(CompareArraysRecursively::class);
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/product_with_multiple_options.php
     * @param array $bundleProductDataProvider
     *
     * @dataProvider getBundleProductDataProvider
     * @throws \Exception
     */
    public function testBundleProductWithMultipleOptions(array $bundleProductDataProvider): void
    {
        $productSku = 'bundle-product';
        $query
            = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}}) {
        items {
        sku
        type_id
        id
        name
        ... on BundleProduct {
            dynamic_sku
            dynamic_price
            dynamic_weight
            price_view
            ship_bundle_items
            items {
                option_id
                title
                required
                type
                position
                sku
                options {
                    id
                    quantity
                    position
                    is_default
                    price
                    price_type
                    can_change_quantity
                    label
                    product {
                        id
                        name
                        sku
                        type_id
                    }
                }
            }
        }
    }
}
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertBundleProduct($response, $bundleProductDataProvider);
    }

    /**
     * Assert bundle product response.
     *
     * @param array $response
     * @param array $bundleProductDataProvider
     */
    private function assertBundleProduct(array $response, array $bundleProductDataProvider): void
    {
        $this->assertNotEmpty($response['products']['items'], 'Precondition failed: "items" must not be empty');
        $productItems = $response['products']['items'];

        foreach ($bundleProductDataProvider as $key => $data) {
            $diff = $this->compareArraysRecursively->execute($data, $productItems[$key]);
            self::assertEquals([], $diff, "Actual response doesn't equal to expected data");
        }
    }

    /**
     * Bundle product data provider.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getBundleProductDataProvider(): array
    {
        return [
            'products' => [
                'bundleProductDataProvider' => [
                    [
                        'sku' => 'bundle-product',
                        'type_id' => 'bundle',
                        'name' => 'Bundle Product',
                        'dynamic_sku' => true,
                        'dynamic_price' => false,
                        'dynamic_weight' => true,
                        'price_view' => 'AS_LOW_AS',
                        'ship_bundle_items' => 'TOGETHER',
                        'items' => [
                            [
                                'title' => 'Option 1',
                                'required' => true,
                                'type' => 'select',
                                'position' => 1,
                                'sku' => 'bundle-product',
                                'options' => [
                                    [
                                        'quantity' => 1,
                                        'position' => 0,
                                        'is_default' => false,
                                        'price' => 0,
                                        'price_type' => 'FIXED',
                                        'can_change_quantity' => false,
                                        'label' => 'Simple Product1',
                                        'product' => [
                                            'name' => 'Simple Product1',
                                            'sku' => 'simple1',
                                            'type_id' => 'simple',
                                        ],
                                    ],
                                    [
                                        'quantity' => 1,
                                        'position' => 0,
                                        'is_default' => false,
                                        'price' => 0,
                                        'price_type' => 'FIXED',
                                        'can_change_quantity' => false,
                                        'label' => 'Simple Product2',
                                        'product' => [
                                            'name' => 'Simple Product2',
                                            'sku' => 'simple2',
                                            'type_id' => 'simple',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Option 2',
                                'required' => true,
                                'type' => 'radio',
                                'position' => 2,
                                'sku' => 'bundle-product',
                                'options' => [
                                    [
                                        'quantity' => 1,
                                        'position' => 0,
                                        'is_default' => false,
                                        'price' => 0,
                                        'price_type' => 'FIXED',
                                        'can_change_quantity' => false,
                                        'label' => 'Simple Product1',
                                        'product' => [
                                            'name' => 'Simple Product1',
                                            'sku' => 'simple1',
                                            'type_id' => 'simple',
                                        ],
                                    ],
                                    [

                                        'quantity' => 1,
                                        'position' => 0,
                                        'is_default' => false,
                                        'price' => 0,
                                        'price_type' => 'FIXED',
                                        'can_change_quantity' => false,
                                        'label' => 'Simple Product2',
                                        'product' => [
                                            'name' => 'Simple Product2',
                                            'sku' => 'simple2',
                                            'type_id' => 'simple',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Option 3',
                                'required' => true,
                                'type' => 'checkbox',
                                'position' => 3,
                                'sku' => 'bundle-product',
                                'options' => [
                                    [
                                        'quantity' => 1,
                                        'position' => 0,
                                        'is_default' => false,
                                        'price' => 0,
                                        'price_type' => 'FIXED',
                                        'can_change_quantity' => false,
                                        'label' => 'Simple Product1',
                                        'product' => [
                                            'name' => 'Simple Product1',
                                            'sku' => 'simple1',
                                            'type_id' => 'simple',
                                        ],
                                    ],
                                    [
                                        'quantity' => 1,
                                        'position' => 0,
                                        'is_default' => false,
                                        'price' => 0,
                                        'price_type' => 'FIXED',
                                        'can_change_quantity' => false,
                                        'label' => 'Simple Product2',
                                        'product' => [
                                            'name' => 'Simple Product2',
                                            'sku' => 'simple2',
                                            'type_id' => 'simple',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Option 4',
                                'required' => true,
                                'type' => 'multi',
                                'position' => 4,
                                'sku' => 'bundle-product',
                                'options' => [
                                    [
                                        'quantity' => 1,
                                        'position' => 0,
                                        'is_default' => false,
                                        'price' => 0,
                                        'price_type' => 'FIXED',
                                        'can_change_quantity' => false,
                                        'label' => 'Simple Product1',
                                        'product' => [
                                            'name' => 'Simple Product1',
                                            'sku' => 'simple1',
                                            'type_id' => 'simple',
                                        ],
                                    ],
                                    [
                                        'quantity' => 1,
                                        'position' => 0,
                                        'is_default' => false,
                                        'price' => 0,
                                        'price_type' => 'FIXED',
                                        'can_change_quantity' => false,
                                        'label' => 'Simple Product2',
                                        'product' => [
                                            'name' => 'Simple Product2',
                                            'sku' => 'simple2',
                                            'type_id' => 'simple',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'title' => 'Option 5',
                                'required' => false,
                                'type' => 'multi',
                                'position' => 5,
                                'sku' => 'bundle-product',
                                'options' => [
                                    [
                                        'quantity' => 1,
                                        'position' => 0,
                                        'is_default' => false,
                                        'price' => 0,
                                        'price_type' => 'FIXED',
                                        'can_change_quantity' => false,
                                        'label' => 'Simple Product1',
                                        'product' => [
                                            'name' => 'Simple Product1',
                                            'sku' => 'simple1',
                                            'type_id' => 'simple',
                                        ],
                                    ],
                                    [
                                        'quantity' => 1,
                                        'position' => 0,
                                        'is_default' => false,
                                        'price' => 0,
                                        'price_type' => 'FIXED',
                                        'can_change_quantity' => false,
                                        'label' => 'Simple Product2',
                                        'product' => [
                                            'name' => 'Simple Product2',
                                            'sku' => 'simple2',
                                            'type_id' => 'simple',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
