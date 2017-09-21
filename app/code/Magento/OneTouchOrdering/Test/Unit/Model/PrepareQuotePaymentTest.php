<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OneTouchOrdering\Model\CustomerCreditCardManager;
use Magento\OneTouchOrdering\Model\PrepareQuote;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\QuoteFactory;
use PHPUnit\Framework\TestCase;
use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;

class PrepareQuotePaymentTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteFactory;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Quote
     */
    private $quote;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerCreditCardManager;
    /**
     * @var PrepareQuote
     */
    private $prepareQuote;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->quoteFactory = $this->createMock(QuoteFactory::class);
        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['getBillingAddress', 'getShippingAddress', 'setInventoryProcessed', 'getPayment', 'collectTotals']
            )->getMock();

        $this->customerCreditCardManager = $this->createMock(CustomerCreditCardManager::class);

        $this->prepareQuote = $objectManager->getObject(
            PrepareQuote::class,
            [
                'quoteFactory' => $this->quoteFactory,
                'customerCreditCardManager' => $this->customerCreditCardManager
            ]
        );
    }

    public function testPreparePayment()
    {
        $customerId = 32;
        $ccId = 2;
        $publicHash = '123456789';
        $nonce = '987654321';

        $paymentAdditionalInformation = [
            'customer_id' => $customerId,
            'public_hash' => $publicHash,
            'payment_method_nonce' => $nonce,
            'is_active_payment_token_enabler' => true
        ];

        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['importData'])
            ->getMock();
        $payment->expects($this->once())
            ->method('importData')
            ->with(['method' => BrainTreeConfigProvider::CC_VAULT_CODE])
            ->willReturnSelf();
        $cc = $this->createMock(\Magento\Vault\Api\Data\PaymentTokenInterface::class);
        $this->customerCreditCardManager->expects($this->once())
            ->method('getCustomerCreditCard')
            ->with($customerId)
            ->willReturn($cc);
        $cc->expects($this->once())->method('getPublicHash')->willReturn($publicHash);
        $this->quote->expects($this->once())->method('getPayment')->willReturn($payment);
        $this->customerCreditCardManager->expects($this->once())
            ->method('getNonce')
            ->with($publicHash, $customerId)
            ->willReturn($nonce);
        $this->quote->expects($this->once())->method('collectTotals');
        $this->prepareQuote->preparePayment($this->quote, $customerId, $ccId);
        
        $this->assertArraySubset($paymentAdditionalInformation, $payment->getAdditionalInformation());
    }
}
