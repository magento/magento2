<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Webapi\Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Check async request for multistore product creation service, scheduling bulk
 * to rabbitmq running consumers and check async.operation.add consumer check
 * if product was created by async requests
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AsyncScheduleMultiStoreTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const REST_RESOURCE_PATH = '/V1/products';
    const ASYNC_RESOURCE_PATH = '/async/V1/products';
    const ASYNC_CONSUMER_NAME = 'async.operations.all';

    const STORE_CODE_FROM_FIXTURE = 'fixturestore';
    const STORE_NAME_FROM_FIXTURE = 'Fixture Store';

    const STORE_CODE_ALL = 'all';
    const STORE_CODE_DEFAULT = 'default';

    private $stores = [
        self::STORE_CODE_DEFAULT,
        self::STORE_CODE_ALL,
        self::STORE_CODE_FROM_FIXTURE,
    ];

    const KEY_TIER_PRICES = 'tier_prices';
    const KEY_SPECIAL_PRICE = 'special_price';
    const KEY_CATEGORY_LINKS = 'category_links';

    const BULK_UUID_KEY = 'bulk_uuid';

    protected $consumers = [
        self::ASYNC_CONSUMER_NAME,
    ];

    /**
     * @var string[]
     */
    private $skus = [];

    /**
     * @var PublisherConsumerController
     */
    private $publisherConsumerController;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Registry
     */
    private $registry;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->logFilePath = TESTS_TEMP_DIR . "/MessageQueueTestLog.txt";
        $this->registry = $this->objectManager->get(Registry::class);

        $params = array_merge_recursive(
            Bootstrap::getInstance()->getAppInitParams(),
            ['MAGE_DIRS' => ['cache' => ['path' => TESTS_TEMP_DIR . '/cache']]]
        );

        /** @var PublisherConsumerController publisherConsumerController */
        $this->publisherConsumerController = $this->objectManager->create(
            PublisherConsumerController::class,
            [
                'consumers' => $this->consumers,
                'logFilePath' => $this->logFilePath,
                'appInitParams' => $params,
            ]
        );
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);

        try {
            $this->publisherConsumerController->initialize();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail(
                $e->getMessage()
            );
        }

        parent::setUp();
    }

    /**
     * @dataProvider storeProvider
     * @magentoApiDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testAsyncScheduleBulkMultistore($storeCode)
    {
        $product = $this->getProductData();
        $this->_markTestAsRestOnly();

        /** @var Store $store */
        $store = $this->objectManager->create(Store::class);
        $store->load(self::STORE_CODE_FROM_FIXTURE);
        $this->assertEquals(
            self::STORE_NAME_FROM_FIXTURE,
            $store->getName(),
            'Precondition failed: fixture store was not created.'
        );

        try {
            /** @var Product $productModel */
            $productModel = $this->objectManager->create(
                Product::class,
                ['data' => $product['product']]
            );
            $this->productRepository->save($productModel);
        } catch (\Exception $e) {
            $this->fail("Precondition failed: product was not created.");
        }

        $this->asyncScheduleAndTest($product, $storeCode);
        $this->clearProducts();
    }

    private function asyncScheduleAndTest($product, $storeCode = null)
    {
        $sku = $product['product'][Product::SKU];
        $productName = $product['product'][Product::NAME];
        $newProductName = $product['product'][Product::NAME] . $storeCode;

        $this->skus[] = $sku;

        $product['product'][Product::NAME] = $newProductName;
        $product['product'][Product::TYPE_ID] = 'virtual';

        $response = $this->updateProductAsync($product, $sku, $storeCode);

        $this->assertArrayHasKey(self::BULK_UUID_KEY, $response);
        $this->assertNotNull($response[self::BULK_UUID_KEY]);

        $this->assertCount(1, $response['request_items']);
        $this->assertEquals('accepted', $response['request_items'][0]['status']);
        $this->assertFalse($response['errors']);

        //assert one products is created
        try {
            $this->publisherConsumerController->waitForAsynchronousResult(
                [$this, 'assertProductCreation'],
                [$product]
            );
        } catch (PreconditionFailedException $e) {
            $this->fail("Not all products were created");
        }

        $requestData = ['id' => $sku, 'sku' => $sku];

        foreach ($this->stores as $checkingStore) {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::REST_RESOURCE_PATH . '/' . $sku,
                    'httpMethod' => Request::HTTP_METHOD_GET
                ]
            ];
            $storeResponse = $this->_webApiCall($serviceInfo, $requestData, null, $checkingStore);
            if ($checkingStore == $storeCode || $storeCode == self::STORE_CODE_ALL) {
                $this->assertEquals(
                    $newProductName,
                    $storeResponse[Product::NAME],
                    sprintf(
                        'Product name in %s store is invalid after updating in store %s.',
                        $checkingStore,
                        $storeCode
                    )
                );
            } else {
                $this->assertEquals(
                    $productName,
                    $storeResponse[Product::NAME],
                    sprintf(
                        'Product name in %s store is invalid after updating in store %s.',
                        $checkingStore,
                        $storeCode
                    )
                );
            }
        }
    }

    public function tearDown()
    {
        $this->clearProducts();
        $this->publisherConsumerController->stopConsumers();
        parent::tearDown();
    }

    private function clearProducts()
    {
        $size = $this->objectManager->create(Collection::class)
            ->addAttributeToFilter('sku', ['in' => $this->skus])
            ->load()
            ->getSize();

        if ($size == 0) {
            return;
        }

        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        try {
            foreach ($this->skus as $sku) {
                $this->productRepository->deleteById($sku);
            }
        // phpcs:ignore Magento2.Exceptions.ThrowCatch
        } catch (\Exception $e) {
            throw $e;
            //nothing to delete
        }
        $this->registry->unregister('isSecureArea');

        $size = $this->objectManager->create(Collection::class)
            ->addAttributeToFilter('sku', ['in' => $this->skus])
            ->load()
            ->getSize();

        if ($size > 0) {
            //phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new Exception(new Phrase("Collection size after clearing the products: %size", ['size' => $size]));
        }
        $this->skus = [];
    }

    /**
     * @return array
     */
    public function getProductData()
    {
        $productBuilder = function ($data) {
            return array_replace_recursive(
                $this->getSimpleProductData(),
                $data
            );
        };

        return [
            'product' =>
                $productBuilder(
                    [
                        ProductInterface::TYPE_ID => 'simple',
                        ProductInterface::SKU => 'multistore-sku-test-1',
                        ProductInterface::NAME => 'Test Name ',
                    ]
                ),
        ];
    }

    public function storeProvider()
    {
        $dataSets = [];
        foreach ($this->stores as $store) {
            $dataSets[$store] = [$store];
        }
        return $dataSets;
    }

    /**
     * Get Simple Product Data
     *
     * @param array $productData
     * @return array
     */
    private function getSimpleProductData($productData = [])
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
        ];
    }

    /**
     * @param $requestData
     * @param string|null $storeCode
     * @return mixed
     */
    private function updateProductAsync($requestData, $sku, $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::ASYNC_RESOURCE_PATH . '/' . $sku,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
        ];

        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
    }

    public function assertProductCreation($product)
    {
        $sku = $product['product'][Product::SKU];
        $collection = $this->objectManager->create(Collection::class)
            ->addAttributeToFilter(Product::SKU, ['eq' => $sku])
            ->addAttributeToFilter(Product::TYPE_ID, ['eq' => 'virtual'])
            ->load();
        $size = $collection->getSize();

        return $size > 0;
    }

    /**
     * Remove test store
     * //phpcs:disable
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        //phpcs:enable
        /** @var Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(Registry::class);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var Store $store*/
        $store = Bootstrap::getObjectManager()->create(Store::class);
        $store->load('fixturestore');
        if ($store->getId()) {
            $store->delete();
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
