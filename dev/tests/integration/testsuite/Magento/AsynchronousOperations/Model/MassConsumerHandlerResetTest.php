<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\MessageQueue\Envelope;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * The async.operations.all consumer must not serve products cached by its handlers for an earlier message.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassConsumerHandlerResetTest extends TestCase
{
    #[DataFixture(ProductFixture::class, ['name' => 'Original name'], 'product')]
    public function testHandlerProductRepositoryCacheIsClearedAfterMessage(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $consumer = $objectManager->get(ConsumerFactory::class)->get('async.operations.all');
        $this->assertInstanceOf(MassConsumer::class, $consumer);
        $handler = $this->findProductRepositoryHandler($consumer);
        $this->assertSame('Original name', $handler->get($product->getSku())->getName());

        $storedProduct = $objectManager->create(Product::class)->setStoreId(0)->load($product->getId());
        $storedProduct->setName('Updated name');
        $objectManager->get(ProductResource::class)->saveAttribute($storedProduct, 'name');
        $this->processMessage($consumer);

        $this->assertSame('Updated name', $handler->get($product->getSku())->getName());
    }

    private function findProductRepositoryHandler(MassConsumer $consumer): ProductRepositoryInterface
    {
        $configuration = (new \ReflectionProperty($consumer, 'configuration'))->getValue($consumer);
        $this->assertInstanceOf(ConsumerConfigurationInterface::class, $configuration);
        foreach ($configuration->getTopicNames() as $topicName) {
            foreach ($configuration->getHandlers($topicName) as $handler) {
                if (is_array($handler) && $handler[0] instanceof ProductRepositoryInterface) {
                    return $handler[0];
                }
            }
        }
        $this->fail('async.operations.all has no ProductRepositoryInterface handler.');
    }

    private function processMessage(MassConsumer $consumer): void
    {
        $queue = $this->createStub(QueueInterface::class);
        $callback = (new \ReflectionMethod($consumer, 'getTransactionCallback'))->invoke($consumer, $queue);
        $callback(new Envelope('{}', ['topic_name' => 'async.operations.all']));
    }
}
