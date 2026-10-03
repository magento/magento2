<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\MessageQueue\Consumer;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\Envelope;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * A long-running consumer must not serve products cached while handling an earlier message.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConsumerProductRepositoryResetTest extends TestCase
{
    private const TOPIC = 'test.consumer.product.reset';

    #[DataFixture(ProductFixture::class, ['name' => 'Original name'], 'product')]
    public function testProductRepositoryCacheIsClearedBetweenMessages(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $sku = DataFixtureStorageManager::getStorage()->get('product')->getSku();
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $loadedNames = [];
        $handler = function () use ($objectManager, $productRepository, $sku, &$loadedNames): void {
            $product = $productRepository->get($sku);
            $loadedNames[] = $product->getName();
            if (count($loadedNames) === 1) {
                $this->renameProductBypassingRepository($objectManager, (int)$product->getId());
            }
        };

        $this->createConsumer($handler)->process();

        $this->assertSame(['Original name', 'Updated name'], $loadedNames);
    }

    private function renameProductBypassingRepository(ObjectManagerInterface $objectManager, int $productId): void
    {
        $product = $objectManager->create(Product::class)->setStoreId(0)->load($productId);
        $product->setName('Updated name');
        $objectManager->get(ProductResource::class)->saveAttribute($product, 'name');
    }

    private function createConsumer(\Closure $handler): Consumer
    {
        $envelope = new Envelope('{}', ['topic_name' => self::TOPIC]);
        $queue = $this->createMock(QueueInterface::class);
        $queue->method('subscribe')->willReturnCallback(
            static function (\Closure $callback) use ($envelope): void {
                $callback($envelope);
                $callback($envelope);
            }
        );
        $configuration = $this->createMock(ConsumerConfigurationInterface::class);
        $configuration->method('getQueue')->willReturn($queue);
        $configuration->method('getConsumerName')->willReturn('test.consumer');
        $configuration->method('getTopicNames')->willReturn([self::TOPIC]);
        $configuration->method('getHandlers')->willReturn([$handler]);
        $configuration->method('getMessageSchemaType')->willReturn(CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS);
        $communicationConfig = $this->createMock(CommunicationConfig::class);
        $communicationConfig->method('getTopic')
            ->willReturn([CommunicationConfig::TOPIC_IS_SYNCHRONOUS => false]);
        $messageEncoder = $this->createMock(MessageEncoder::class);
        $messageEncoder->method('decode')->willReturn('payload');
        $messageController = $this->createMock(MessageController::class);
        $messageController->method('lock')->willReturn($this->createMock(LockInterface::class));

        return Bootstrap::getObjectManager()->create(
            Consumer::class,
            [
                'configuration' => $configuration,
                'communicationConfig' => $communicationConfig,
                'messageEncoder' => $messageEncoder,
                'messageController' => $messageController,
            ]
        );
    }
}
