<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * SpecialPriceStorage API operations test
 */
class SpecialPriceStorageTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogSpecialPriceStorageV1';
    const SERVICE_VERSION = 'V1';
    const SIMPLE_PRODUCT_SKU = 'simple';
    const VIRTUAL_PRODUCT_SKU = 'virtual-product';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test get method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGet()
    {
        $specialPrice = 3057;
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU, true);
        $product->setData('special_price', $specialPrice);
        $productRepository->save($product);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/special-price-information',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['skus' => [self::SIMPLE_PRODUCT_SKU]]);
        /** @var ProductInterface $product */
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU);
        $this->assertNotEmpty($response);
        $this->assertEquals($product->getSpecialPrice(), $response[0]['price']);
    }

    /**
     * Test get method when special price is 0.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetZeroValue()
    {
        $specialPrice = 0;
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get(self::SIMPLE_PRODUCT_SKU, true);
        $product->setData('special_price', $specialPrice);
        $productRepository->save($product);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/special-price-information',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];
        $response = $this->_webApiCall($serviceInfo, ['skus' => [self::SIMPLE_PRODUCT_SKU]]);
        $this->assertNotEmpty($response);
        $this->assertEquals($specialPrice, $response[0]['price']);
    }

    /**
     * Test update method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @dataProvider updateData
     * @param array $data
     */
    public function testUpdate(array $data)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/special-price',
                'httpMethod' => Request::HTTP_METHOD_POST
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Update',
            ],
        ];
        $response = $this->_webApiCall(
            $serviceInfo,
            [
                'prices' => [
                    $data
                ]
            ]
        );
        $this->assertEmpty($response);
    }

    /**
     * Test delete method.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider deleteData
     * @param array $data
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testDelete(array $data)
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get($data['sku'], true);
        $product->setData('special_price', $data['price']);
        $product->setData('special_from_date', $data['price_from']);
        if ($data['price_to']) {
            $product->setData('special_to_date', $data['price_to']);
        }
        $productRepository->save($product);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/products/special-price-delete',
                'httpMethod' => Request::HTTP_METHOD_POST
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
                        $data
                ]
            ]
        );
        $product = $productRepository->get($data['sku'], false, null, true);
        $this->assertEmpty($response);
        $this->assertNull($product->getSpecialPrice());
    }

    /**
     * Data provider for testUpdate
     *
     * @return array
     */
    public function updateData(): array
    {
        $fromDate = '2037-01-19 03:14:07';
        $toDate = '2038-01-19 03:14:07';

        return [
            [
                // data set with 'price_to' specified
                [
                    'price' => 31337,
                    'store_id' => 0,
                    'sku' => self::VIRTUAL_PRODUCT_SKU,
                    'price_from' => $fromDate,
                    'price_to' => $toDate
                ]
            ],
            [
                // data set without 'price_to' specified
                [
                    'price' => 31337,
                    'store_id' => 0,
                    'sku' => self::VIRTUAL_PRODUCT_SKU,
                    'price_from' => $fromDate,
                    'price_to' => false
                ]
            ],
        ];
    }

    /**
     * Data provider for testDelete
     *
     * @return array
     */
    public function deleteData(): array
    {
        $fromDate = '1970-01-01 00:00:01';
        $toDate = '2038-01-19 03:14:07';

        return [
            [
                // data set with 'price_to' specified
                [
                    'price' => 3057,
                    'store_id' => 0,
                    'sku' => self::SIMPLE_PRODUCT_SKU,
                    'price_from' => $fromDate,
                    'price_to' => $toDate
                ]
            ],
            [
                // data set without 'price_to' specified
                [
                    'price' => 3057,
                    'store_id' => 0,
                    'sku' => self::SIMPLE_PRODUCT_SKU,
                    'price_from' => $fromDate,
                    'price_to' => false
                ]
            ],
        ];
    }
}
