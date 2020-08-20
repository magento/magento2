<?php
/**
 * Test services for name collisions.
 *
 * Let we have two service interfaces called Foo\Bar\Service\SomeBazV1Interface and Foo\Bar\Service\Some\BazV1Interface.
 * Given current name generation logic both are going to be translated to BarSomeBazV1. This test checks such things
 * are not going to happen.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\Exception\BulkException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Webapi\Exception;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @magentoDbIsolation disabled
 */
class MassScheduleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string[]
     */
    protected $consumers = ['async.operations.all'];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var MassSchedule
     */
    private $massSchedule;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PublisherConsumerController
     */
    private $publisherConsumerController;

    /**
     * @var array
     */
    private $skus = [];

    /** @var string */
    private $logFilePath;

    /**
     * @var Registry
     */
    private $registry;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->massSchedule = $this->objectManager->create(MassSchedule::class);
        $this->logFilePath = TESTS_TEMP_DIR . "/MessageQueueTestLog.txt";
        $this->collection = $this->objectManager->create(Collection::class);
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        /** @var PublisherConsumerController publisherConsumerController */
        $this->publisherConsumerController = $this->objectManager->create(PublisherConsumerController::class, [
            'consumers' => $this->consumers,
            'logFilePath' => $this->logFilePath,
            'appInitParams' => \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams()
        ]);

        try {
            $this->publisherConsumerController->initialize();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail($e->getMessage());
        }

        parent::setUp();
    }

    /**
     * @dataProvider productDataProvider
     * @param ProductInterface[] $products
     */
    public function testScheduleMass($products)
    {
        try {
            $this->sendBulk($products);
        } catch (BulkException $bulkException) {
            $this->fail('Bulk was not accepted in full');
        }

        //assert all products are created
        try {
            $this->publisherConsumerController->waitForAsynchronousResult(
                [$this, 'assertProductExists'],
                [$this->skus, count($this->skus)]
            );
        } catch (PreconditionFailedException $e) {
            $this->fail("Not all products were created");
        }
    }

    public function sendBulk($products)
    {
        $this->skus = [];
        foreach ($products as $data) {
            if (isset($data['product'])) {
                $this->skus[] = $data['product']->getSku();
            }
        }
        $this->clearProducts();

        $result = $this->massSchedule->publishMass(
            'async.magento.catalog.api.productrepositoryinterface.save.post',
            $products
        );

        //assert bulk accepted with no errors
        $this->assertFalse($result->isErrors());

        //assert number of products sent to queue
        $this->assertCount(count($this->skus), $result->getRequestItems());
    }

    protected function tearDown(): void
    {
        $this->publisherConsumerController->stopConsumers();
        $this->clearProducts();

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

    public function assertProductExists($productsSkus, $count)
    {
        $collection = $this->objectManager->create(Collection::class)
            ->addAttributeToFilter('sku', ['in' => $productsSkus])
            ->load();
        $size = $collection->getSize();
        return $size == $count;
    }

    /**
     * @dataProvider productExceptionDataProvider
     * @param ProductInterface[] $products
     */
    public function testScheduleMassOneEntityFailure($products)
    {
        try {
            $this->sendBulk($products);
        } catch (BulkException $e) {
            $this->assertCount(1, $e->getErrors());

            $errors = $e->getErrors();
            $this->assertInstanceOf(\Magento\Framework\Exception\LocalizedException::class, $errors[0]);

            $this->assertEquals("Error processing 1 element of input data", $errors[0]->getMessage());

            $reasonException = $errors[0]->getPrevious();

            $expectedErrorMessage = "Data item corresponding to \"product\" " .
                "must be specified in the message with topic " .
                "\"async.magento.catalog.api.productrepositoryinterface.save.post\".";
            $this->assertEquals(
                $expectedErrorMessage,
                $reasonException->getMessage()
            );

            /** @var \Magento\WebapiAsync\Model\AsyncResponse $bulkStatus */
            $bulkStatus = $e->getData();
            $this->assertTrue($bulkStatus->isErrors());

            /** @var ItemStatus[] $items */
            $items = $bulkStatus->getRequestItems();
            $this->assertCount(2, $items);

            $this->assertEquals(ItemStatus::STATUS_ACCEPTED, $items[0]->getStatus());
            $this->assertEquals(0, $items[0]->getId());

            $this->assertEquals(ItemStatus::STATUS_REJECTED, $items[1]->getStatus());
            $this->assertEquals(1, $items[1]->getId());
            $this->assertEquals($expectedErrorMessage, $items[1]->getErrorMessage());
        }

        //assert one products is created
        try {
            $this->publisherConsumerController->waitForAsynchronousResult(
                [$this, 'assertProductExists'],
                [$this->skus, count($this->skus)]
            );
        } catch (PreconditionFailedException $e) {
            $this->fail("Not all products were created");
        }
    }

    private function getProduct()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(ProductInterface::class);
        $product
            ->setTypeId('simple')
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Simple Product 1')
            ->setSku('unique-simple-product1')
            ->setPrice(10)
            ->setMetaTitle('meta title')
            ->setMetaKeyword('meta keyword')
            ->setMetaDescription('meta description')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setStockData(['use_config_manage_stock' => 0]);
        return $product;
    }

    public function productDataProvider()
    {
        return [
            'single_product' => [
                [['product' => $this->getProduct()]],
            ],
            'multiple_products' => [
                [
                    ['product' => $this->getProduct()
                        ->setName('Simple Product 3')
                        ->setSku('unique-simple-product3')
                        ->setMetaTitle('meta title 3')
                    ],
                    ['product' => $this->getProduct()
                        ->setName('Simple Product 2')
                        ->setSku('unique-simple-product2')
                        ->setMetaTitle('meta title 2')
                    ]
                ]
            ],
        ];
    }

    public function productExceptionDataProvider()
    {
        return [
            'single_product' => [
                [['product' => $this->getProduct()]],
            ],
            'multiple_products' => [
                [
                    ['product' => $this->getProduct()],
                    ['customer' => $this->getProduct()]
                ]
            ],
        ];
    }
}
