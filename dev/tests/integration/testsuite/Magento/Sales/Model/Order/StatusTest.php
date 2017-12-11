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
    public function theCorrectLabelIsUsedDependingOnTheAreaProvider()
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
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\App\State::class)->setAreaCode($area);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        $this->assertEquals($result, $order->getStatusLabel());
    }
}
