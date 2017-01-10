<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;

/**
 * SpecialPriceStorage test.
 */
class SpecialPriceStorageTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogSpecialPriceStorageV1';
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
        $specialPrice = 3057;
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU, true);
        $product->setData('special_price', $specialPrice);
        $productRepository->save($product);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/special-price-information',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['skus' => [self::SIMPLE_PRODUCT_SKU]]);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU);
        $this->assertNotEmpty($response);
        $this->assertEquals($product->getSpecialPrice(), $response[0]['price']);
    }

    /**
     * Test get method, called with not existing SKUs.
     */
    public function testGetWithInvalidSku()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/special-price-information',
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
            $this->_webApiCall($serviceInfo, ['skus' => ['sku_of_not_exiting_product', 'invalid_sku_1']]);
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
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     */
    public function testUpdate()
    {
        $sku = 'virtual-product';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/special-price',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Update',
            ],
        ];
        $storeId = 0;
        $newPrice = 31337;
        $response = $this->_webApiCall(
            $serviceInfo,
            [
                'prices' => [
                    [
                        'price' => $newPrice,
                        'store_id' => $storeId,
                        'sku' => $sku,
                        'price_from' => '2037-01-19 03:14:07',
                        'price_to' => '2038-01-19 03:14:07',
                    ]
                ]
            ]
        );
        $this->assertEmpty($response);
    }

    /**
     * Test delete method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testDelete()
    {
        $specialPrice = 3057;
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $fromDate = '1970-01-01 00:00:01';
        $toDate = '2038-01-19 03:14:07';
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU, true);
        $product->setData('special_price', $specialPrice)
            ->setData('special_from_date', $fromDate)
            ->setData('special_to_date', $toDate);
        $productRepository->save($product);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/special-price-delete',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Delete',
            ],
        ];
        $response = $this->_webApiCall(
            $serviceInfo,
            [
                'prices' => [
                    [
                        'price' => $specialPrice,
                        'store_id' => 0,
                        'sku' => self::SIMPLE_PRODUCT_SKU,
                        'price_from' => $fromDate,
                        'price_to' => $toDate,
                    ]
                ]
            ]
        );
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU, false, null, true);
        $this->assertEmpty($response);
        $this->assertNull($product->getSpecialPrice());
    }
}
