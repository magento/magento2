<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Framework\App\State;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class ShipmentTest
 * @package Magento\Sales\Model\Order
 */
class StatusTest extends \PHPUnit\Framework\TestCase
{
    public static function theCorrectLabelIsUsedDependingOnTheAreaProvider()
    {
        return [
            'backend label' => [
                'adminhtml',
                'Example',
            ],
            'store view label' => [
                'frontend',
                'Store view example',
            ],
        ];
    }

    /**
     * In the backend the regular label must be showed.
     *
     * @param $area
     * @param $result
     *
     * @magentoDataFixture Magento/Sales/_files/order_status.php
     * @dataProvider theCorrectLabelIsUsedDependingOnTheAreaProvider
     */
    public function testTheCorrectLabelIsUsedDependingOnTheArea($area, $result)
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setAreaCode($area);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        $this->assertEquals($result, $order->getStatusLabel());
    }

    /**
     * Tests that specified order status frontend label for store should be displayed correctly
     *
     * @magentoDataFixture Magento/Sales/_files/order_status_with_different_labels.php
     */
    public function testTheCorrectLabelIsUsedDependingOnTheStore()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(State::class)->setAreaCode('frontend');
        $orderFactory = $objectManager->get(OrderInterfaceFactory::class);
        $order = $orderFactory->create()->loadByIncrementId('100000001');
        $this->assertEquals('Custom status label', $order->getFrontendStatusLabel());
        $order->setStoreId(1);
        $order->save();
        $this->assertEquals(1, $order->getStoreId());
        $this->assertEquals('First store label', $order->getFrontendStatusLabel());
    }
}
