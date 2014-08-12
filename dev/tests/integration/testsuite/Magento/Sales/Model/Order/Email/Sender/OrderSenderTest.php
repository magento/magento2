<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            ->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        $payment = $order->getPayment();
        $paymentInfoBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Payment\Helper\Data'
        )->getInfoBlock(
                $payment
            );
        $paymentInfoBlock->setArea('invalid-area');
        $payment->setBlockMock($paymentInfoBlock);

        $this->assertEmpty($order->getEmailSent());

        $orderSender = Bootstrap::getObjectManager()
            ->create('Magento\Sales\Model\Order\Email\Sender\OrderSender');
        $result = $orderSender->send($order);

        $this->assertTrue($result);

        $this->assertNotEmpty($order->getEmailSent());
        $this->assertEquals('frontend', $paymentInfoBlock->getArea());
    }
}
