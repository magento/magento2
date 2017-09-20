<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\OneTouchOrdering\Model\CustomerData;
use Magento\OneTouchOrdering\Model\PrepareQuote;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class PrepareQuoteTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CustomerData
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
        $this->store = $this->createMock(Store::class);

        $this->prepareQuote = $objectManager->getObject(
            PrepareQuote::class,
            [
                'quoteFactory' => $this->quoteFactory,
                'storeManager' => $this->storeManager
            ]
        );
    }

    public function testPrepare()
    {
        $customerDataModel = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customerAddressDataModel = $this->createMock(
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $this->customerData
            ->expects($this->once())
            ->method('getCustomerDataModel')
            ->willReturn($customerDataModel);
        $this->customerData
            ->expects($this->once())
            ->method('getDefaultBillingAddressDataModel')
            ->willReturn($customerAddressDataModel);
        $this->customerData
            ->expects($this->once())
            ->method('getDefaultShippingAddressDataModel')
            ->willReturn($customerAddressDataModel);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($this->store);
        $this->quoteFactory->expects($this->once())->method('create')->willReturn($this->quote);

        $quoteAddressMock = $this->createMock(QuoteAddress::class);
        $this->quote->expects($this->once())->method('getBillingAddress')->willReturn($quoteAddressMock);
        $this->quote->expects($this->once())->method('getShippingAddress')->willReturn($quoteAddressMock);
        $quoteAddressMock
            ->expects($this->exactly(2))
            ->method('importCustomerAddressData')
            ->with($customerAddressDataModel);
        $this->quote->expects($this->once())->method('setInventoryProcessed')->with(false);
        $result = $this->prepareQuote->prepare($this->customerData);
        $this->assertSame($this->quote, $result);
    }
}
