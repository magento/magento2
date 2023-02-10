<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model;

use Magento\Framework\DB\Select;
use Magento\ProductAlert\Model\ResourceModel\Price as PriceResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use PHPUnit\Framework\TestCase;

/**
 * Test Product Alert observer
 *
 * @magentoDbIsolation disabled
 */
class ObserverTest extends TestCase
{
    /**
     * @var Observer
     */
    private $observer;

    /**
     * @var PriceResource
     */
    private $priceResource;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->observer =  $objectManager->get(Observer::class);
        $this->priceResource = $objectManager->create(PriceResource::class);
    }

    /**
     * Test process() method
     *
     * @magentoConfigFixture current_store catalog/productalert/allow_price 1
     * @magentoDataFixture Magento/ProductAlert/_files/product_alert.php
     */
    public function testProcess()
    {
        $this->observer->process();
        $this->assertProcessAlertByConsumer();
    }

    /**
     * Waiting for execute consumer
     *
     * @return void
     * @throws PreconditionFailedException
     */
    private function assertProcessAlertByConsumer(): void
    {
        /** @var PublisherConsumerController $publisherConsumerController */
        $publisherConsumerController = Bootstrap::getObjectManager()->create(
            PublisherConsumerController::class,
            [
                'consumers' => ['product_alert'],
                'logFilePath' => TESTS_TEMP_DIR . "/MessageQueueTestLog.txt",
                'maxMessages' => 1,
                'appInitParams' => Bootstrap::getInstance()->getAppInitParams()
            ]
        );
        try {
            $publisherConsumerController->startConsumers();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail(
                $e->getMessage()
            );
        }

        sleep(15); // timeout to processing Magento queue

        $publisherConsumerController->waitForAsynchronousResult(
            function () {
                return $this->loadLastPriceAlertStatus();
            },
            []
        );
    }

    /**
     * Load last created price alert
     *
     * @return bool
     */
    private function loadLastPriceAlertStatus(): bool
    {
        $select = $this->priceResource->getConnection()->select();
        $select->from($this->priceResource->getMainTable(), ['status'])
            ->order($this->priceResource->getIdFieldName() . ' ' . Select::SQL_DESC)
            ->limit(1);

        return (bool)$this->priceResource->getConnection()->fetchOne($select);
    }
}
