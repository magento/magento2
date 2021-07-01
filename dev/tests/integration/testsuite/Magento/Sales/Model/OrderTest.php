<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test Magento\Sales\Model\Order
 */
class OrderTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDbIsolation enabled
     */
    public function testAddStatusHistory()
    {
        $statusHistoryFactory = $this->objectManager->create(HistoryFactory::class);
        /** @var $order Order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $statusHistoryData = [
            'comment'              => "Valid status specified",
            'created_at'           => date("Y-m-d H:i:s"),
            'is_customer_notified' => 0,
            'is_visible_on_front'  => 1,
            'status'               => 'fraud'
        ];
        /** @var OrderStatusHistoryInterface $statusHistory */
        $statusHistory = $statusHistoryFactory->create();
        $statusHistory->setData($statusHistoryData);

        $order->addStatusHistory($statusHistory);
        $this->assertEquals($statusHistory->getStatus(), $order->getStatus());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDbIsolation enabled
     */
    public function testAddStatusHistoryInvalidStatus()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Order status is not valid');

        $statusHistoryFactory = $this->objectManager->create(HistoryFactory::class);

        /** @var $order Order */
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');

        $statusHistoryData = [
            'comment'              => "Invalid status specified",
            'created_at'           => date("Y-m-d H:i:s"),
            'is_customer_notified' => 0,
            'is_visible_on_front'  => 1,
            'status'               => 'invalid_status'
        ];

        /** @var OrderStatusHistoryInterface $statusHistory */
        $statusHistory = $statusHistoryFactory->create();
        $statusHistory->setData($statusHistoryData);

        $order->addStatusHistory($statusHistory);

        $this->assertEquals('processing', $order->getStatus());
    }
}
