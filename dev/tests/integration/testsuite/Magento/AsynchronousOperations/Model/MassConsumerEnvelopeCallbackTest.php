<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\SaveMultipleOperationsInterface;
use Magento\AsynchronousOperations\Model\ConfigInterface as AsyncConfig;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\Lock\ReaderInterface;
use Magento\Framework\MessageQueue\LockInterfaceFactory;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Monolog\Test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for MassConsumerEnvelopeCallback
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassConsumerEnvelopeCallbackTest extends TestCase
{
    /**
     * @var MassConsumerEnvelopeCallback
     */
    private $model;

    /**
     * @var QueueInterface|MockObject
     */
    private $queue;

    /**
     * @var ConsumerConfigurationInterface|MockObject
     */
    private $configuration;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var OperationRepositoryInterface
     */
    private $operationRepository;

    /**
     * @var BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var SaveMultipleOperationsInterface
     */
    private $saveMultipleOperations;

    /**
     * @var CollectionFactory
     */
    private $operationCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->queue = $this->createMock(QueueInterface::class);
        $this->configuration = $this->createMock(ConsumerConfigurationInterface::class);
        $this->messageEncoder = $objectManager->get(MessageEncoder::class);
        $this->operationRepository = $objectManager->get(OperationRepositoryInterface::class);
        $operationProcessor = $this->createMock(OperationProcessor::class);
        $this->operationCollectionFactory = $objectManager->get(CollectionFactory::class);
        $this->bulkManagement = $objectManager->get(BulkManagementInterface::class);
        $this->saveMultipleOperations = $objectManager->get(SaveMultipleOperationsInterface::class);
        $operationProcessorFactory = $this->createMock(OperationProcessorFactory::class);
        $operationProcessorFactory->method('create')
            ->willReturn($operationProcessor);
        $this->model = Bootstrap::getObjectManager()->create(
            MassConsumerEnvelopeCallback::class,
            [
                'queue' => $this->queue,
                'configuration' => $this->configuration,
                'operationProcessorFactory' => $operationProcessorFactory
            ]
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testMessageLock(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $product = $objectManager->create(Product::class);
        $product->setSku('simple');
        $product->setName('random name update');
        $message = [
            'product' => $product
        ];
        $topicName = 'async.magento.catalog.api.productrepositoryinterface.save.post';
        $buuid = uniqid('bulk-');
        $messageProps = [
            'message_id' => uniqid('msg-'),
            'topic_name' => $topicName
        ];
        $consumerName = 'async.operations.all';
        $this->bulkManagement->scheduleBulk($buuid, [], 'test bulk');
        $operation = $this->operationRepository->create($topicName, $message, $buuid, 0);
        $this->saveMultipleOperations->execute([$operation]);

        $envelope = $this->createMock(EnvelopeInterface::class);
        $envelope->method('getBody')
            ->willReturn($this->messageEncoder->encode(AsyncConfig::SYSTEM_TOPIC_NAME, $operation));
        $envelope->method('getProperties')
            ->willReturn($messageProps);
        $this->configuration
            ->method('getConsumerName')
            ->willReturn($consumerName);
        $this->configuration
            ->method('getTopicNames')
            ->willReturn([$topicName]);
        $this->model->execute($envelope);

        /** @var Collection $collection */
        $collection = $this->operationCollectionFactory->create();
        $collection->addFieldToFilter(OperationInterface::BULK_ID, ['eq' => $buuid]);
        $this->assertEquals(1, $collection->count());
        $operation = $collection->getFirstItem();
        $this->assertNotEmpty($operation->getData('started_at'));

        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $code = md5($consumerName . '-' . $messageProps['message_id']);
        $lockFactory = $objectManager->get(LockInterfaceFactory::class);
        $lockReader = $objectManager->get(ReaderInterface::class);
        $lock = $lockFactory->create();
        $lockReader->read($lock, $code);
        $this->assertNotEmpty($lock->getId());
    }
}
