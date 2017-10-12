<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

/**
 * Class ShipmentTest
 * @package Magento\Sales\Model\Order
 */
class StatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * In the backend the regular label must be showed.
     *
     * @magentoDataFixture Magento/Sales/_files/order_status.php
     */
    public function testTheLabelIsUsedInTheBackend()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('adminhtml');

        /** @var \Magento\Sales\Model\Order $order */
        $order = $objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        $this->assertEquals('Example', $order->getStatusLabel());
    }

    /**
     * In the frontend the store view specific label must be showed.
     *
     * @magentoDataFixture Magento/Sales/_files/order_status.php
     */
    public function testTheStoreViewLabelIsUsedInTheFrontend()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');

        /** @var \Magento\Sales\Model\Order $order */
        $order = $objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        $this->assertEquals('Store view example', $order->getStatusLabel());
    }
}
