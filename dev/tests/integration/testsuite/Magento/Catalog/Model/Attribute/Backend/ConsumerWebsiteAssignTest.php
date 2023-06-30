<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\AsynchronousOperations\Api\Data\OperationInterface as OperationDataInterface;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\ObjectManagerInterface;
use Magento\MysqlMq\Model\Driver\Queue;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MysqlMq\DeleteTopicRelatedMessages;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Mysql website assigning consumer
 *
 * @see \Magento\Catalog\Model\Attribute\Backend\ConsumerWebsiteAssign
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea adminhtml
 */
class ConsumerWebsiteAssignTest extends TestCase
{
    private const TOPIC_NAME = 'product_action_attribute.website.update';

    /** @var DeleteTopicRelatedMessages */
    private static $deleteTopicRelatedMessages;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ConsumerWebsiteAssign */
    private $consumer;

    /** @var Queue */
    private $queue;

    /** @var MessageEncoder */
    private $messageEncoder;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var WebsiteRepositoryInterface */
    private $websiteRepository;

    /** @var CollectionFactory */
    private $operationCollectionFactory;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $objectManager = Bootstrap::getObjectManager();
        self::$deleteTopicRelatedMessages = $objectManager->get(DeleteTopicRelatedMessages::class);
        self::$deleteTopicRelatedMessages->execute(self::TOPIC_NAME);
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->consumer = $this->objectManager->get(ConsumerWebsiteAssign::class);
        $this->queue = $this->objectManager->create(
            Queue::class,
            ['queueName' => 'product_action_attribute.website.update']
        );
        $this->messageEncoder = $this->objectManager->get(MessageEncoder::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->websiteRepository = $this->objectManager->get(WebsiteRepositoryInterface::class);
        $this->operationCollectionFactory = $this->objectManager->get(CollectionFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(Action::class);
        self::$deleteTopicRelatedMessages->execute(self::TOPIC_NAME);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/update_product_website_quene_data.php
     *
     * @return void
     */
    public function testAddWebsite(): void
    {
        $this->processMessages();
        $this->assertProductWebsites('simple2', ['base', 'test']);
        $this->assertOperation(OperationInterface::STATUS_TYPE_COMPLETE);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/detach_product_website_quene_data.php
     *
     * @return void
     */
    public function testRemoveWebsite(): void
    {
        $this->processMessages();
        $this->assertProductWebsites('unique-simple-azaza', ['base']);
        $this->assertOperation(OperationInterface::STATUS_TYPE_COMPLETE);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/update_product_website_quene_data.php
     *
     * @return void
     */
    public function testAddWebsiteToDeletedProduct(): void
    {
        $expectedMessage = __('Something went wrong while adding products to websites.');
        $this->productRepository->deleteById('simple2');
        $this->processMessages();
        $this->assertOperation(OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED, (string)$expectedMessage);
    }

    /**
     * @dataProvider errorProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/update_product_website_quene_data.php
     *
     * @param \Throwable $exception
     * @param int $code
     * @return void
     */
    public function testWithException(\Throwable $exception, int $code): void
    {
        $this->prepareMock($exception);
        $this->processMessages();
        $this->assertOperation($code, $exception->getMessage());
    }

    /**
     * @return array
     */
    public function errorProvider(): array
    {
        return [
            'with_dead_lock_exception' => [
                'exception' => new DeadlockException('Test lock'),
                'code' => OperationDataInterface::STATUS_TYPE_RETRIABLY_FAILED,
            ],
            'with_db_exception' => [
                'exception' => new \Zend_Db_Adapter_Exception(
                    (string)__(
                        'Sorry, something went wrong during product attributes update. Please see log for details.'
                    )
                ),
                'code' => OperationDataInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
            ],
            'with_no_such_entity_exception' => [
                'exception' => new NoSuchEntityException(),
                'code' => OperationDataInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
            ],
            'with_general_exception' => [
                'exception' => new \Exception(
                    (string)__(
                        'Sorry, something went wrong during product attributes update. Please see log for details.'
                    )
                ),
                'code' => OperationDataInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
            ],
        ];
    }

    /**
     * Assert product website ids
     *
     * @param string $sku
     * @param array $expectedWebsites
     * @return void
     */
    private function assertProductWebsites(string $sku, array $expectedWebsites): void
    {
        $product = $this->productRepository->get($sku, false, null, true);
        $websitesIds = $product->getWebsiteIds();
        $this->assertCount(count($expectedWebsites), $websitesIds);

        foreach ($expectedWebsites as $expectedWebsite) {
            $expectedWebsiteId = $this->websiteRepository->get($expectedWebsite)->getId();
            $this->assertContains($expectedWebsiteId, $websitesIds);
        }
    }

    /**
     * Process current consumer topic messages
     *
     * @return void
     */
    private function processMessages(): void
    {
        $envelope = $this->queue->dequeue();
        $decodedMessage = $this->messageEncoder->decode(self::TOPIC_NAME, $envelope->getBody());
        $this->consumer->process($decodedMessage);
    }

    /**
     * Get last current topic related operation
     *
     * @return OperationDataInterface
     */
    private function getLastTopicOperation(): OperationDataInterface
    {
        $collection = $this->operationCollectionFactory->create();
        $collection->addFieldToFilter('topic_name', self::TOPIC_NAME);
        $collection->setPageSize(1)->setCurPage($collection->getLastPageNumber());

        return $collection->getLastItem();
    }

    /**
     * Assert performed operation
     *
     * @param int $status
     * @param string|null $resultMessage
     * @return void
     */
    private function assertOperation(int $status, ?string $resultMessage = null): void
    {
        $operation = $this->getLastTopicOperation();
        $this->assertNotNull($operation->getData('id'));
        $this->assertEquals($status, $operation->getStatus());
        $this->assertEquals($resultMessage, $operation->getResultMessage());
    }

    /**
     * Create mock with provided exception
     *
     * @param \Throwable $exception
     * @return void
     */
    private function prepareMock(\Throwable $exception): void
    {
        $object = $this->createPartialMock(Action::class, ['updateWebsites']);
        $object->method('updateWebsites')->willThrowException($exception);
        $this->objectManager->addSharedInstance($object, Action::class);
        $this->consumer = $this->objectManager->create(ConsumerWebsiteAssign::class);
    }
}
