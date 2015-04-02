<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;

class ProductRepositoryInterfaceTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products';

    const KEY_GROUP_PRICES = 'group_prices';
    const KEY_TIER_PRICES = 'tier_prices';

    private $productData = [
        [
            ProductInterface::SKU => 'simple',
            ProductInterface::NAME => 'Simple Related Product',
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 10,
        ],
        [
            ProductInterface::SKU => 'simple_with_cross',
            ProductInterface::NAME => 'Simple Product With Related Product',
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 10
        ],
    ];

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_related.php
     */
    public function testGet()
    {
        $productData = $this->productData[0];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productData[ProductInterface::SKU],
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['sku' => $productData[ProductInterface::SKU]]);
        foreach ([ProductInterface::SKU, ProductInterface::NAME, ProductInterface::PRICE] as $key) {
            $this->assertEquals($productData[$key], $response[$key]);
        }
    }

    protected function getProduct($sku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['sku' => $sku]);
        return $response;
    }

    public function testGetNoSuchEntityException()
    {
        $invalidSku = '(nonExistingSku)';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $invalidSku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $expectedMessage = 'Requested product doesn\'t exist';

        try {
            $this->_webApiCall($serviceInfo, ['sku' => $invalidSku]);
            $this->fail("Expected throwing exception");
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    /**
     * @return array
     */
    public function productCreationProvider()
    {
        $productBuilder = function ($data) {
            return array_replace_recursive(
                $this->getSimpleProductData(),
                $data
            );
        };
        return [
            [$productBuilder([ProductInterface::TYPE_ID => 'simple', ProductInterface::SKU => 'psku-test-1'])],
            [$productBuilder([ProductInterface::TYPE_ID => 'virtual', ProductInterface::SKU => 'psku-test-2'])],
        ];
    }

    /**
     * @dataProvider productCreationProvider
     */
    public function testCreate($product)
    {
        $response = $this->saveProduct($product);
        $this->assertArrayHasKey(ProductInterface::SKU, $response);
        $this->deleteProduct($product[ProductInterface::SKU]);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testUpdate()
    {
        $productData = [
            ProductInterface::NAME => 'Very Simple Product', //new name
            ProductInterface::SKU => 'simple', //sku from fixture
        ];
        $product = $this->getSimpleProductData($productData);
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
            $product[ProductInterface::SKU] = null;
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $productData[ProductInterface::SKU],
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response =  $this->_webApiCall($serviceInfo, $requestData);

        $this->assertArrayHasKey(ProductInterface::SKU, $response);
        $this->assertArrayHasKey(ProductInterface::NAME, $response);
        $this->assertEquals($productData[ProductInterface::NAME], $response[ProductInterface::NAME]);
        $this->assertEquals($productData[ProductInterface::SKU], $response[ProductInterface::SKU]);
    }

    protected function updateProduct($product)
    {
        $sku = $product[ProductInterface::SKU];
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_REST) {
            $product[ProductInterface::SKU] = null;
        }

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        $response =  $this->_webApiCall($serviceInfo, $requestData);
        return $response;
    }


    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testDelete()
    {
        $response = $this->deleteProduct('simple');
        $this->assertTrue($response);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetList()
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'sku',
                                'value' => 'simple',
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
                'page_size' => 2,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertTrue($response['total_count'] > 0);
        $this->assertTrue(count($response['items']) > 0);

        $this->assertNotNull($response['items'][0]['sku']);
        $this->assertEquals('simple', $response['items'][0]['sku']);
    }

    /**
     * Get Simple Product Data
     *
     * @param array $productData
     * @return array
     */
    protected function getSimpleProductData($productData = [])
    {
        return [
            ProductInterface::SKU => isset($productData[ProductInterface::SKU])
                ? $productData[ProductInterface::SKU] : uniqid('sku-', true),
            ProductInterface::NAME => isset($productData[ProductInterface::NAME])
                ? $productData[ProductInterface::NAME] : uniqid('sku-', true),
            ProductInterface::VISIBILITY => 4,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::PRICE => 3.62,
            ProductInterface::STATUS => 1,
            ProductInterface::TYPE_ID => 'simple',
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            'custom_attributes' => [
                ['attribute_code' => 'cost', 'value' => ''],
                ['attribute_code' => 'description', 'value' => 'Description'],
            ]
        ];
    }

    /**
     * @param $product
     * @return mixed
     */
    protected function saveProduct($product)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $requestData = ['product' => $product];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Delete Product
     *
     * @param string $sku
     * @return boolean
     */
    protected function deleteProduct($sku)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $sku,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];

        return (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) ?
            $this->_webApiCall($serviceInfo, ['sku' => $sku]) : $this->_webApiCall($serviceInfo);
    }

    public function testGroupPrices()
    {
        // create a product with group prices
        $custGroup1 = \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
        $custGroup2 = \Magento\Customer\Model\Group::CUST_GROUP_ALL;
        $productData = $this->getSimpleProductData();
        $productData[self::KEY_GROUP_PRICES] = [
            [
                'customer_group_id' => $custGroup1,
                'value' => 3.14
            ],
            [
                'customer_group_id' => $custGroup2,
                'value' => 3.45,
            ]
        ];
        $this->saveProduct($productData);
        $response = $this->getProduct($productData[ProductInterface::SKU]);

        $this->assertArrayHasKey(self::KEY_GROUP_PRICES, $response);
        $groupPrices = $response[self::KEY_GROUP_PRICES];
        $this->assertNotNull($groupPrices, "CREATE: expected to have group prices");
        $this->assertCount(2, $groupPrices, "CREATE: expected to have 2 'group_prices' objects");
        $this->assertEquals(3.14, $groupPrices[0]['value']);
        $this->assertEquals($custGroup1, $groupPrices[0]['customer_group_id']);
        $this->assertEquals(3.45, $groupPrices[1]['value']);
        $this->assertEquals($custGroup2, $groupPrices[1]['customer_group_id']);

        // update the product's group prices: update 1st group price, (delete the 2nd group price), add a new one
        $custGroup3 = 1;
        $groupPrices[0]['value'] = 3.33;
        $groupPrices[1] = [
            'customer_group_id' => $custGroup3,
            'value' => 2.10,
        ];
        $response[self::KEY_GROUP_PRICES] = $groupPrices;
        $response = $this->updateProduct($response);

        $this->assertArrayHasKey(self::KEY_GROUP_PRICES, $response);
        $groupPrices = $response[self::KEY_GROUP_PRICES];
        $this->assertNotNull($groupPrices, "UPDATE 1: expected to have group prices");
        $this->assertCount(2, $groupPrices, "UPDATE 1: expected to have 2 'group_prices' objects");
        $this->assertEquals(3.33, $groupPrices[0]['value']);
        $this->assertEquals($custGroup1, $groupPrices[0]['customer_group_id']);
        $this->assertEquals(2.10, $groupPrices[1]['value']);
        $this->assertEquals($custGroup3, $groupPrices[1]['customer_group_id']);

        // update the product without any mention of group prices; no change expected for group pricing
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        unset($response[self::KEY_GROUP_PRICES]);
        $response = $this->updateProduct($response);

        $this->assertArrayHasKey(self::KEY_GROUP_PRICES, $response);
        $groupPrices = $response[self::KEY_GROUP_PRICES];
        $this->assertNotNull($groupPrices, "UPDATE 2: expected to have group prices");
        $this->assertCount(2, $groupPrices, "UPDATE 2: expected to have 2 'group_prices' objects");
        $this->assertEquals(3.33, $groupPrices[0]['value']);
        $this->assertEquals($custGroup1, $groupPrices[0]['customer_group_id']);
        $this->assertEquals(2.10, $groupPrices[1]['value']);
        $this->assertEquals($custGroup3, $groupPrices[1]['customer_group_id']);

        // update the product with empty group prices; expect to have the existing group prices removed
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $response[self::KEY_GROUP_PRICES] = [];
        $response = $this->updateProduct($response);
        $this->assertArrayHasKey(self::KEY_GROUP_PRICES, $response, "expected to have the 'group_prices' key");
        $this->assertEmpty($response[self::KEY_GROUP_PRICES], "expected to have an empty array of 'group_prices'");

        // delete the product with group prices; expect that all goes well
        $response = $this->deleteProduct($productData[ProductInterface::SKU]);
        $this->assertTrue($response);
    }

    public function testTierPrices()
    {
        // create a product with tier prices
        $custGroup1 = \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
        $custGroup2 = \Magento\Customer\Model\Group::CUST_GROUP_ALL;
        $productData = $this->getSimpleProductData();
        $productData[self::KEY_TIER_PRICES] = [
            [
                'customer_group_id' => $custGroup1,
                'value' => 3.14,
                'qty' => 5,
            ],
            [
                'customer_group_id' => $custGroup2,
                'value' => 3.45,
                'qty' => 10,
            ]
        ];
        $this->saveProduct($productData);
        $response = $this->getProduct($productData[ProductInterface::SKU]);

        $this->assertArrayHasKey(self::KEY_TIER_PRICES, $response);
        $tierPrices = $response[self::KEY_TIER_PRICES];
        $this->assertNotNull($tierPrices, "CREATE: expected to have tier prices");
        $this->assertCount(2, $tierPrices, "CREATE: expected to have 2 'tier_prices' objects");
        $this->assertEquals(3.14, $tierPrices[0]['value']);
        $this->assertEquals(5, $tierPrices[0]['qty']);
        $this->assertEquals($custGroup1, $tierPrices[0]['customer_group_id']);
        $this->assertEquals(3.45, $tierPrices[1]['value']);
        $this->assertEquals(10, $tierPrices[1]['qty']);
        $this->assertEquals($custGroup2, $tierPrices[1]['customer_group_id']);

        // update the product's tier prices: update 1st tier price, (delete the 2nd tier price), add a new one
        $custGroup3 = 1;
        $tierPrices[0]['value'] = 3.33;
        $tierPrices[0]['qty'] = 6;
        $tierPrices[1] = [
            'customer_group_id' => $custGroup3,
            'value' => 2.10,
            'qty' => 12,
        ];
        $response[self::KEY_TIER_PRICES] = $tierPrices;
        $response = $this->updateProduct($response);

        $this->assertArrayHasKey(self::KEY_TIER_PRICES, $response);
        $tierPrices = $response[self::KEY_TIER_PRICES];
        $this->assertNotNull($tierPrices, "UPDATE 1: expected to have tier prices");
        $this->assertCount(2, $tierPrices, "UPDATE 1: expected to have 2 'tier_prices' objects");
        $this->assertEquals(3.33, $tierPrices[0]['value']);
        $this->assertEquals(6, $tierPrices[0]['qty']);
        $this->assertEquals($custGroup1, $tierPrices[0]['customer_group_id']);
        $this->assertEquals(2.10, $tierPrices[1]['value']);
        $this->assertEquals(12, $tierPrices[1]['qty']);
        $this->assertEquals($custGroup3, $tierPrices[1]['customer_group_id']);

        // update the product without any mention of tier prices; no change expected for tier pricing
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        unset($response[self::KEY_TIER_PRICES]);
        $response = $this->updateProduct($response);

        $this->assertArrayHasKey(self::KEY_TIER_PRICES, $response);
        $tierPrices = $response[self::KEY_TIER_PRICES];
        $this->assertNotNull($tierPrices, "UPDATE 2: expected to have tier prices");
        $this->assertCount(2, $tierPrices, "UPDATE 2: expected to have 2 'tier_prices' objects");
        $this->assertEquals(3.33, $tierPrices[0]['value']);
        $this->assertEquals(6, $tierPrices[0]['qty']);
        $this->assertEquals($custGroup1, $tierPrices[0]['customer_group_id']);
        $this->assertEquals(2.10, $tierPrices[1]['value']);
        $this->assertEquals(12, $tierPrices[1]['qty']);
        $this->assertEquals($custGroup3, $tierPrices[1]['customer_group_id']);

        // update the product with empty tier prices; expect to have the existing tier prices removed
        $response = $this->getProduct($productData[ProductInterface::SKU]);
        $response[self::KEY_TIER_PRICES] = [];
        $response = $this->updateProduct($response);
        $this->assertArrayHasKey(self::KEY_TIER_PRICES, $response, "expected to have the 'tier_prices' key");
        $this->assertEmpty($response[self::KEY_TIER_PRICES], "expected to have an empty array of 'tier_prices'");

        // delete the product with tier prices; expect that all goes well
        $response = $this->deleteProduct($productData[ProductInterface::SKU]);
        $this->assertTrue($response);
    }
}
