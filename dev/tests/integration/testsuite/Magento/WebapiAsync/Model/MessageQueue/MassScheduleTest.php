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
namespace Magento\WebapiAsync\Model\MessageQueue;

use Magento\Framework\Exception\BulkException;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;

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

    protected function setUp()
    {

        $this->objectManager = Bootstrap::getObjectManager();
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
            $this->fail(
                $e->getMessage()
            );
        }

        parent::setUp();

    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @dataProvider productDataProvider
     */
    public function testScheduleMass($products) {
        try {
            $this->skus = [];
            foreach ($products as $data) {
                $this->skus[] = $data['product']->getSku();
            }
            $result = $this->massSchedule->publishMass('async.V1.products.POST', $products);

            //assert bulk accepted with no errors
            $this->assertFalse($result->getIsErrors());

            //assert number of products sent to queue
            $this->assertEquals(count($result->getRequestItems()), count($this->skus));
        } catch (BulkException $bulkException) {
            $this->fail('Bulk was not accepted in full');
        }

        //assert all products are created
        try {
            $this->publisherConsumerController->waitForAsynchronousResult(
                [$this, 'assertProductExists'], [$this->skus, count($this->skus)]
            );
        } catch (PreconditionFailedException $e) {
            $this->fail("Not all products were created");
        }

        foreach ($this->skus as $sku) {
            $this->productRepository->deleteById($sku);
        }
    }

    public function tearDown()
    {
//        foreach ($this->skus as $sku) {
//            $this->productRepository->deleteById($sku);
//        }

        $size = $this->objectManager->create(Collection::class)
            ->addAttributeToFilter('sku', ['in' => $this->skus])
            ->load()
            ->getSize();

        $this->publisherConsumerController->stopConsumers();

        parent::tearDown();
    }

    public function assertProductExists($productsSkus, $count)
    {
        $collection = $this->objectManager->create(Collection::class)
            ->addAttributeToFilter('sku', ['in' => $productsSkus])
            ->load();
        var_dump($collection->getData());
        $size = $collection->getSize();
        var_dump($size, $count);
        return $size == $count;
    }

    public function testScheduleMassMultipleEntities()
    {

    }

    public function testScheduleMassOneEntityFailure()
    {
//        var_dump($bulkException->getData()['request_items']);
//        foreach($bulkException->getData() as $item) {
//            var_dump($item);
//        }
    }

    public function testScheduleMassMultipleEntitiesFailure()
    {

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
                    ['product' => $this->getProduct()],
                    ['product' => $this->getProduct()
                        ->setName('Simple Product 2')
                        ->setSku('unique-simple-product2')
                        ->setMetaTitle('meta title 2')
                    ]
                ]
            ],
        ];
    }
}
