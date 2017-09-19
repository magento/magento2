<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OneTouchOrdering\Model\CustomerBrainTreeManager;
use Magento\OneTouchOrdering\Model\CustomerData;
use Magento\OneTouchOrdering\Model\PrepareQuote;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;

class PrepareQuotePaymentTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerData;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteFactory;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $store;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerBrainTreeManager;
    /**
     * @var PrepareQuote
     */
    private $prepareQuote;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->customerData = $this->createMock(CustomerData::class);
        $this->quoteFactory = $this->createMock(QuoteFactory::class);
        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['getBillingAddress', 'getShippingAddress', 'setInventoryProcessed', 'getPayment', 'collectTotals']
            )->getMock();

        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->customerBrainTreeManager = $this->createMock(CustomerBrainTreeManager::class);

        $this->prepareQuote = $objectManager->getObject(
            PrepareQuote::class,
            [
                'customerData' => $this->customerData,
                'quoteFactory' => $this->quoteFactory,
                'storeManager' => $this->storeManager,
                'customerBrainTreeManager' => $this->customerBrainTreeManager
            ]
        );
    }

    public function testPreparePaymentNoCcAvailable()
    {
        $this->customerBrainTreeManager
            ->expects($this->once())
            ->method('getCustomerBrainTreeCard')
            ->willReturn(false);
        $this->expectException(LocalizedException::class);
        $this->prepareQuote->preparePayment($this->quote);
    }

    public function testPreparePayment()
    {
        $customerId = 32;
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
        $this->customerData->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $cc = $this->createMock(\Magento\Vault\Api\Data\PaymentTokenInterface::class);
        $this->customerBrainTreeManager->expects($this->once())
            ->method('getCustomerBrainTreeCard')
            ->with($customerId)
            ->willReturn($cc);
        $cc->expects($this->once())->method('getPublicHash')->willReturn($publicHash);
        $this->quote->expects($this->once())->method('getPayment')->willReturn($payment);
        $this->customerBrainTreeManager->expects($this->once())
            ->method('getNonce')
            ->with($publicHash, $customerId)
            ->willReturn($nonce);
        $this->quote->expects($this->once())->method('collectTotals');
        $this->prepareQuote->preparePayment($this->quote);
        
        $this->assertArraySubset($paymentAdditionalInformation, $payment->getAdditionalInformation());
    }
}
