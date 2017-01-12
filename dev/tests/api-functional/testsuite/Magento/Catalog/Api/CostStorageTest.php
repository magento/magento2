<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;

/**
 * CostStorage test.
 */
class CostStorageTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogCostStorageV1';
    const SERVICE_VERSION = 'V1';
    const SIMPLE_PRODUCT_SKU = 'simple';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Test get method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGet()
    {
        $cost = 3057;
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $productRepository->save($productRepository->get(self::SIMPLE_PRODUCT_SKU)->setData('cost', $cost));
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/cost-information',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Delete',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['skus' => [self::SIMPLE_PRODUCT_SKU]]);

        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU);

        $this->assertNotEmpty($response);
        $this->assertEquals($product->getCost(), $cost);
    }

    /**
     * Test get method, called with not existing SKUs.
     */
    public function testGetWithInvalidSku()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/cost-information',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $expected = 'Requested products don\'t exist: %sku';

        try {
            $this->_webApiCall($serviceInfo, ['skus' => ['sku_of_not_exiting_product', 'invalid_sku']]);
            $this->fail("Expected throwing exception");
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expected,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $error = $this->processRestExceptionResult($e);
            $this->assertEquals($expected, $error['message']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    /**
     * Test update method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testUpdate()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/cost',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Update',
            ],
        ];
        $storeId = 0;
        $newCost = 31337;
        $response = $this->_webApiCall(
            $serviceInfo,
            [
                'prices' => [
                    [
                        'cost' => $newCost,
                        'store_id' => $storeId,
                        'sku' => self::SIMPLE_PRODUCT_SKU,
                    ]
                ]
            ]
        );
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU);
        $this->assertEmpty($response);
        $this->assertEquals($product->getCost(), $newCost);
    }

    /**
     * Test update method call without SKU.
     */
    public function testUpdateWithInvalidParameters()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/cost',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Update',
            ],
        ];
        $newCost = -9999;
        $storeId = 9999;
        $response = $this->_webApiCall(
            $serviceInfo,
            [
                'prices' => [
                    [
                        'sku' => 'not_existing_sku',
                        'cost' => $newCost,
                        'store_id' => $storeId
                    ]
                ]
            ]
        );

        $expectedResponse = [
            0 => [
                'message' => 'Invalid attribute %fieldName = %fieldValue.',
                'parameters' => [
                    'SKU',
                    'not_existing_sku',
                ]
            ],
            1 => [
                'message' => 'Invalid attribute %fieldName = %fieldValue.',
                'parameters' => [
                    'Cost',
                    '-9999',
                ]
            ],
            2 => [
                'message' => 'Requested store is not found.',
                'parameters' => []
            ]
        ];

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * Test delete method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testDelete()
    {
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $productRepository->save($productRepository->get(self::SIMPLE_PRODUCT_SKU)->setData('cost', 777));
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/cost-delete',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Delete',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['skus' => [self::SIMPLE_PRODUCT_SKU]]);
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU);
        $this->assertTrue($response);
        $this->assertNull($product->getCost());
    }

    /**
     * Test delete method, called with not existing SKUs.
     */
    public function testDeleteWithInvalidSku()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/cost-delete',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Delete',
            ],
        ];
        $expectedResponseMessage = 'Requested product doesn\'t exist: %sku';

        try {
            $this->_webApiCall($serviceInfo, ['skus' => ['sku_of_not_exiting_product']]);
            $this->fail("Expected throwing exception");
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expectedResponseMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $error = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedResponseMessage, $error['message']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }
}
