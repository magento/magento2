<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\TestCase\WebapiAbstract;

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

    private static $stores = [
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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $logFilePath = TESTS_TEMP_DIR . "/MessageQueueTestLog.txt";
        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);

        $this->publisherConsumerController = $this->objectManager->create(
            PublisherConsumerController::class,
            [
                'consumers' => $this->consumers,
                'logFilePath' => $logFilePath,
                'appInitParams' => Bootstrap::getInstance()->getAppInitParams(),
            ]
        );
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);

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
     * @param string|null $storeCode
     * @return void
     */
    public function testAsyncScheduleBulkMultistore(?string $storeCode): void
    {
        $product = $this->getProductData();
        $this->_markTestAsRestOnly();

        /** @var Store $store */
        $store = $this->objectManager->get(Store::class);
        $store->load(self::STORE_CODE_FROM_FIXTURE);
        $this->assertEquals(
            self::STORE_NAME_FROM_FIXTURE,
            $store->getName(),
            'Precondition failed: fixture store was not created.'
        );

        try {
            /** @var ProductInterface $productModel */
            $productModel = $this->objectManager->create(
                ProductInterface::class,
                ['data' => $product['product']]
            );
            $this->productRepository->save($productModel);
        } catch (\Exception $e) {
            $this->fail("Precondition failed: product was not created.");
        }

        $this->asyncScheduleAndTest($product, $storeCode);
        $this->clearProducts();
    }

    /**
     * @param array $product
     * @param string|null $storeCode
     * @return void
     */
    private function asyncScheduleAndTest(array $product, $storeCode = null): void
    {
        $sku = $product['product'][ProductInterface::SKU];
        $productName = $product['product'][ProductInterface::NAME];
        $newProductName = $product['product'][ProductInterface::NAME] . $storeCode;

        $this->skus[] = $sku;

        $product['product'][ProductInterface::NAME] = $newProductName;
        $product['product'][ProductInterface::TYPE_ID] = 'virtual';

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

        foreach (self::$stores as $checkingStore) {
            $serviceInfo = [
                'rest' => [
                    'resourcePath' => self::REST_RESOURCE_PATH . '/' . $sku,
                    'httpMethod' => Request::HTTP_METHOD_GET,
                ]
            ];
            $storeResponse = $this->_webApiCall($serviceInfo, $requestData, null, $checkingStore);
            if ($checkingStore == $storeCode || $storeCode == self::STORE_CODE_ALL) {
                $this->assertEquals(
                    $newProductName,
                    $storeResponse[ProductInterface::NAME],
                    sprintf(
                        'Product name in %s store is invalid after updating in store %s.',
                        $checkingStore,
                        $storeCode
                    )
                );
            } else {
                $this->assertEquals(
                    $productName,
                    $storeResponse[ProductInterface::NAME],
                    sprintf(
                        'Product name in %s store is invalid after updating in store %s.',
                        $checkingStore,
                        $storeCode
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->clearProducts();
        $this->publisherConsumerController->stopConsumers();
        parent::tearDown();
    }

    /**
     * @return void
     */
    private function clearProducts(): void
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
    public function getProductData(): array
    {
        $productBuilder = function ($data) {
            return array_replace_recursive(
                $this->getSimpleProductData(),
                $data
            );
        };

        return [
            'product' => $productBuilder(
                [
                    ProductInterface::TYPE_ID => 'simple',
                    ProductInterface::SKU => 'multistore-sku-test-1',
                    ProductInterface::NAME => 'Test Name ',
                ]
            ),
        ];
    }

    /**
     * @return array
     */
    public static function storeProvider(): array
    {
        $dataSets = [];
        foreach (self::$stores as $store) {
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
    private function getSimpleProductData($productData = []): array
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
            ProductInterface::ATTRIBUTE_SET_ID => 4,
        ];
    }

    /**
     * @param array $requestData
     * @param string $sku
     * @param string|null $storeCode
     * @return mixed
     */
    private function updateProductAsync(array $requestData, string $sku, $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::ASYNC_RESOURCE_PATH . '/' . $sku,
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
        ];

        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
    }

    /**
     * @param array $product
     * @return bool
     */
    public function assertProductCreation(array $product): bool
    {
        $sku = $product['product'][ProductInterface::SKU];
        $collection = $this->objectManager->create(Collection::class)
            ->addAttributeToFilter(ProductInterface::SKU, ['eq' => $sku])
            ->addAttributeToFilter(ProductInterface::TYPE_ID, ['eq' => 'virtual'])
            ->load();
        $size = $collection->getSize();

        return $size > 0;
    }
}
