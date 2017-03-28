<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\TestFramework\Helper\Bootstrap;

class OrderSenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testSendNewOrderEmail()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        $this->assertEmpty($order->getEmailSent());

        $orderSender = Bootstrap::getObjectManager()
            ->create(\Magento\Sales\Model\Order\Email\Sender\OrderSender::class);
        $result = $orderSender->send($order);

        $this->assertTrue($result);

        $this->assertNotEmpty($order->getEmailSent());
    }
}
