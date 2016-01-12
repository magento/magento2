<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

/**
 * Tests vault payment token model
 *
 * @see \Magento\Vault\Model\PaymentToken
 * @magentoDataFixture Magento/Vault/_files/order_paid_with_payflowpro_vault.php
 */
class PaymentTokenTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateAndLoadPaymentToken()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId('100000001');
        $payment = $order->getPayment();
        $extensionAttributes = $payment->getExtensionAttributes();
        $paymentToken = $extensionAttributes->getVaultPaymentToken();

        $this->assertEquals('asdfg', $paymentToken->getGatewayToken());
    }
}
