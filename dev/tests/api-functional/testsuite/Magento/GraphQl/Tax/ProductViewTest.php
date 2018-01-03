<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Tax;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Tax\Model\Config;

/**
 * @magentoAppIsolation enabled
 */
class ProductViewTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);

        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = $this->objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);

        //default state tax calculation AL
        $config->saveConfig(
            Config::CONFIG_XML_PATH_DEFAULT_REGION,
            1,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            1
        );

        $config->saveConfig(
            Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
            3,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            1
        );

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = $this->objectManager->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->reinit();
    }

    public function tearDown()
    {
        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = $this->objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);

        //default state tax calculation AL
        $config->saveConfig(
            Config::CONFIG_XML_PATH_DEFAULT_REGION,
            null,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            1
        );

        $config->saveConfig(
            Config::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE,
            1,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            1
        );

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = $this->objectManager->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->reinit();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_primary_addresses.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_all_fields.php
     * @magentoApiDataFixture Magento/Tax/_files/tax_rule_region_1_al.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryAllFieldsSimpleProduct()
    {
        $productSku = 'simple';

        $product = $this->productRepository->get($productSku, null, null, true);
        // set product to taxable goods
        $product->setData('tax_class_id', 2)->save();

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
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
                adjustments {
                  amount {
                    value
                    currency
                  }
                  code
                  description
                }
              }
              maximalPrice {
                amount {
                  value
                  currency
                }
                adjustments {
                  amount {
                    value
                    currency
                  }
                  code
                  description
                }
              }
              regularPrice {
                amount {
                  value
                  currency
                }
                adjustments {
                  amount {
                    value
                    currency
                  }
                  code
                  description
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

        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $customerToken = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $response = $this->graphQlQuery($query, [], '', $headerMap);

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($productSku, false, null, true);
        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertEquals(1, count($response['products']['items']));
        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertBaseFields($product, $response['products']['items'][0]);
    }

    /**
     * @param ProductInterface $product
     * @param array $actualResponse
     */
    private function assertBaseFields($product, $actualResponse)
    {
        // ['product_object_field_name', 'expected_value']
        $assertionMap = [
            ['response_field' => 'attribute_set_id', 'expected_value' => $product->getAttributeSetId()],
            ['response_field' => 'created_at', 'expected_value' => $product->getCreatedAt()],
            ['response_field' => 'id', 'expected_value' => $product->getId()],
            ['response_field' => 'name', 'expected_value' => $product->getName()],
            ['response_field' => 'price', 'expected_value' =>
                [
                    'minimalPrice' => [
                        'amount' => [
                            'value' => 4.106501,
                            'currency' => 'USD'
                        ],
                        'adjustments' => [
                            0 =>
                                [
                                    'amount' =>
                                        [
                                            'value' => 0.286501,
                                            'currency' => 'USD',
                                        ],
                                        'code' => 'tax',
                                        'description' => 'Included',
                                ],
                        ]
                    ],
                    'regularPrice' => [
                        'amount' => [
                            'value' => 10.750001,
                            'currency' => 'USD'
                        ],
                        'adjustments' => [
                            0 =>
                                [
                                    'amount' =>
                                        [
                                            'value' => 0.750001,
                                            'currency' => 'USD',
                                        ],
                                        'code' => 'tax',
                                        'description' => 'Included',
                                ],
                        ]
                    ],
                    'maximalPrice' => [
                        'amount' => [
                            'value' => 4.106501,
                            'currency' => 'USD'
                        ],
                        'adjustments' => [
                            0 =>
                                [
                                    'amount' =>
                                        [
                                            'value' => 0.286501,
                                            'currency' => 'USD',
                                        ],
                                        'code' => 'tax',
                                        'description' => 'Included',
                                ],
                        ]
                    ],
                ]
            ],
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
