<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\SaveMultipleOperationsInterface;
use Magento\AsynchronousOperations\Model\ConfigInterface as AsyncConfig;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\MessageQueue\Envelope;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * The async.operations.all consumer must not keep products cached by its handlers once a message is processed.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassConsumerHandlerResetTest extends TestCase
{
    private const TOPIC = 'async.magento.catalog.api.productrepositoryinterface.save.post';

    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, ['price' => 10], 'product'),
    ]
    public function testHandlerProductRepositoryCacheIsClearedAfterMessage(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $sku = DataFixtureStorageManager::getStorage()->get('product')->getSku();
        $consumer = $objectManager->get(ConsumerFactory::class)->get('async.operations.all');
        $this->assertInstanceOf(MassConsumer::class, $consumer);
        $handler = $this->findProductRepositoryHandler($consumer);

        $bulkUuid = uniqid('bulk-');
        $this->processMessage($consumer, $this->scheduleProductPriceUpdate($bulkUuid, $sku));

        $operation = $objectManager->get(CollectionFactory::class)->create()
            ->addFieldToFilter(OperationInterface::BULK_ID, $bulkUuid)
            ->getFirstItem();
        $this->assertSame(
            OperationInterface::STATUS_TYPE_COMPLETE,
            (int)$operation->getStatus(),
            (string)$operation->getResultMessage()
        );
        $this->assertSame([], (new \ReflectionProperty(ProductRepository::class, 'instances'))->getValue($handler));
    }

    private function findProductRepositoryHandler(MassConsumer $consumer): ProductRepository
    {
        $configuration = (new \ReflectionProperty($consumer, 'configuration'))->getValue($consumer);
        foreach ($configuration->getHandlers(self::TOPIC) as $handler) {
            if (is_array($handler) && $handler[0] instanceof ProductRepository) {
                return $handler[0];
            }
        }
        $this->fail('async.operations.all has no ProductRepository handler for ' . self::TOPIC);
    }

    private function scheduleProductPriceUpdate(string $bulkUuid, string $sku): OperationInterface
    {
        $objectManager = Bootstrap::getObjectManager();
        $product = $objectManager->create(Product::class)->setSku($sku)->setPrice(15);
        $objectManager->get(BulkManagementInterface::class)->scheduleBulk($bulkUuid, [], 'test bulk');
        $operation = $objectManager->get(OperationRepositoryInterface::class)
            ->create(self::TOPIC, ['product' => $product], $bulkUuid, 0);
        $objectManager->get(SaveMultipleOperationsInterface::class)->execute([$operation]);
        return $operation;
    }

    private function processMessage(MassConsumer $consumer, OperationInterface $operation): void
    {
        $body = Bootstrap::getObjectManager()->get(MessageEncoder::class)
            ->encode(AsyncConfig::SYSTEM_TOPIC_NAME, $operation);
        $callback = (new \ReflectionMethod($consumer, 'getTransactionCallback'))
            ->invoke($consumer, $this->createStub(QueueInterface::class));
        $callback(new Envelope($body, ['message_id' => uniqid('msg-'), 'topic_name' => self::TOPIC]));
    }
}
