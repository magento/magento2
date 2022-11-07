<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * SpecialPriceStorage API operations test
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SpecialPriceStorageTest extends WebapiAbstract
{
    private const SERVICE_NAME = 'catalogSpecialPriceStorageV1';
    private const SERVICE_VERSION = 'V1';
    private const SIMPLE_PRODUCT_SKU = 'simple';
    private const VIRTUAL_PRODUCT_SKU = 'virtual-product';
    private const PRODUCT_SKU_TWO_WEBSITES = 'simple-on-two-websites';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @ingeritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productResource = $this->objectManager->get(ProductResource::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
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
     * Delete special price for specified store when price scope is global
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoConfigFixture default_store catalog/price/scope 0
     * @return void
     */
    public function testDeleteWhenPriceIsGlobal(): void
    {
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
                    [
                        'price' => 777,
                        'store_id' => 1,
                        'sku' => self::SIMPLE_PRODUCT_SKU,
                        'price_from' => '2037-01-19 03:14:07',
                        'price_to' => '2038-01-19 03:14:07'
                    ]
                ]
            ]
        );

        $errorMessage = current(array_column($response, 'message'));
        $this->assertStringContainsString(
            'Could not change non global Price when price scope is global.',
            $errorMessage
        );
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

    /**
     * Test delete method for specific store.
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_two_websites.php
     * @magentoConfigFixture default_store catalog/price/scope 1
     * @return void
     */
    public function testDeleteDataForSpecificStore(): void
    {
        $secondStoreViewId = $this->storeManager->getStore('fixture_second_store')
            ->getId();

        $data = [
            'price' => 777,
            'store_id' => $secondStoreViewId,
            'sku' => self::PRODUCT_SKU_TWO_WEBSITES,
            'price_from' => '1970-01-01 00:00:01',
            'price_to' => false
        ];

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get($data['sku'], true, 1, true);
        $product->setData('special_price', $data['price']);
        $product->setData('special_from_date', $data['price_from']);

        $this->productResource->saveAttribute($product, 'special_price');
        $this->productResource->saveAttribute($product, 'special_from_date');
        $this->productResource->saveAttribute($product, 'special_to_date');

        $product->setData('store_id', $secondStoreViewId);
        $this->productResource->saveAttribute($product, 'special_price');
        $this->productResource->saveAttribute($product, 'special_from_date');
        $this->productResource->saveAttribute($product, 'special_to_date');

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

        $this->_webApiCall($serviceInfo, ['prices' => [$data]]);

        $product = $productRepository->get($data['sku'], false, 1, true);
        $this->assertNotNull($product->getSpecialPrice());

        $product = $productRepository->get($data['sku'], false, $secondStoreViewId, true);
        $this->assertNull($product->getSpecialPrice());
    }
}
