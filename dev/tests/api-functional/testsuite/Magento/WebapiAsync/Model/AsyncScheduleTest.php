<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NotFoundException;
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

/**
 * Check async request for product creation service, scheduling bulk to rabbitmq
 * running consumers and check async.operation.add consumer
 * check if product was created by async requests
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AsyncScheduleTest extends WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const REST_RESOURCE_PATH = '/V1/products';
    const ASYNC_RESOURCE_PATH = '/async/V1/products';
    const ASYNC_CONSUMER_NAME = 'async.operations.all';

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
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Registry
     */
    private $registry;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $logFilePath = TESTS_TEMP_DIR . "/MessageQueueTestLog.txt";
        $this->registry = $this->objectManager->get(Registry::class);

        $params = array_merge_recursive(
            \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams(),
            ['MAGE_DIRS' => ['cache' => ['path' => TESTS_TEMP_DIR . '/cache']]]
        );

        /** @var PublisherConsumerController publisherConsumerController */
        $this->publisherConsumerController = $this->objectManager->create(PublisherConsumerController::class, [
            'consumers'     => $this->consumers,
            'logFilePath'   => $logFilePath,
            'appInitParams' => $params,
        ]);
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
     * @dataProvider productCreationProvider
     */
    public function testAsyncScheduleBulk($product)
    {
        $this->_markTestAsRestOnly();
        $this->skus[] = $product['product'][ProductInterface::SKU];
        $this->clearProducts();

        $response = $this->saveProductAsync($product);
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
    }

    protected function tearDown(): void
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
            throw new Exception(new Phrase("Collection size after clearing the products: %size", ['size' => $size]));
        }
    }

    /**
     * @param string $sku
     * @param string|null $storeCode
     * @dataProvider productGetDataProvider
     */
    public function testGETRequestToAsync($sku, $storeCode = null)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Specified request cannot be processed.');

        $this->_markTestAsRestOnly();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::ASYNC_RESOURCE_PATH . '/' . $sku,
                'httpMethod'   => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        $response = null;
        try {
            $response = $this->_webApiCall($serviceInfo, [ProductInterface::SKU => $sku], null, $storeCode);
        } catch (NotFoundException $e) {
            $this->assertEquals(400, $e->getCode());
        }
        $this->assertNull($response);
    }

    /**
     * @return array
     */
    public static function productCreationProvider()
    {
        $productBuilder = function ($data) {
            return array_replace_recursive(
                self::getSimpleProductData(),
                $data
            );
        };

        return [
            [
                [
                    'product' =>
                        $productBuilder([
                            ProductInterface::TYPE_ID => 'simple',
                            ProductInterface::SKU     => 'psku-test-1',
                        ]),
                ],
            ],
            [
                [
                    'product' => $productBuilder([
                        ProductInterface::TYPE_ID => 'virtual',
                        ProductInterface::SKU     => 'psku-test-2',
                    ]),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function productGetDataProvider()
    {
        return [
            ['psku-test-1', null],
        ];
    }

    /**
     * Get Simple Product Data
     *
     * @param array $productData
     * @return array
     */
    private static function getSimpleProductData($productData = [])
    {
        return [
            ProductInterface::SKU              => isset($productData[ProductInterface::SKU])
                ? $productData[ProductInterface::SKU] : uniqid('sku-', true),
            ProductInterface::NAME             => isset($productData[ProductInterface::NAME])
                ? $productData[ProductInterface::NAME] : uniqid('sku-', true),
            ProductInterface::VISIBILITY       => 4,
            ProductInterface::TYPE_ID          => 'simple',
            ProductInterface::PRICE            => 3.62,
            ProductInterface::STATUS           => 1,
            ProductInterface::ATTRIBUTE_SET_ID => 4,
            'custom_attributes'                => [
                ['attribute_code' => 'cost', 'value' => ''],
                ['attribute_code' => 'description', 'value' => 'Description'],
            ],
        ];
    }

    /**
     * @param $requestData
     * @param string|null $storeCode
     * @return mixed
     */
    private function saveProductAsync($requestData, $storeCode = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::ASYNC_RESOURCE_PATH,
                'httpMethod'   => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
        ];

        return $this->_webApiCall($serviceInfo, $requestData, null, $storeCode);
    }

    public function assertProductCreation()
    {
        $collection = $this->objectManager->create(Collection::class)
            ->addAttributeToFilter('sku', ['in' => $this->skus])
            ->load();
        $size = $collection->getSize();

        return $size == count($this->skus);
    }
}
