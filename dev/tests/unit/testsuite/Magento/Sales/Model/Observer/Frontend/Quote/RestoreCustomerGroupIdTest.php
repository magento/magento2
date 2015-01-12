<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer\Frontend\Quote;

/**
 * Tests Magento\Sales\Model\Observer\Frontend\Quote\RestoreCustomerGroupIdTest
 */
class RestoreCustomerGroupIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressHelperMock;

    /**
     * @var RestoreCustomerGroupId
     */
    protected $quote;

    protected function setUp()
    {
        $this->customerAddressHelperMock = $this->getMockBuilder('Magento\Customer\Helper\Address')
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = new RestoreCustomerGroupId(
            $this->customerAddressHelperMock
        );
    }

    /**
     * @param string|null $configAddressType
     * @dataProvider restoreCustomerGroupIdDataProvider
     */
    public function testExecute($configAddressType)
    {
        $quoteAddress = $this->getMock(
            'Magento\Sales\Model\Quote\Address',
            [
                'getQuote', 'setCustomerGroupId', 'getPrevQuoteCustomerGroupId',
                'unsPrevQuoteCustomerGroupId', 'hasPrevQuoteCustomerGroupId'
            ],
            [],
            '',
            false
        );
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getQuoteAddress'], [], '', false);
        $observer->expects($this->once())
            ->method('getQuoteAddress')
            ->will($this->returnValue($quoteAddress));

        $this->customerAddressHelperMock->expects($this->once())
            ->method('getTaxCalculationAddressType')
            ->will($this->returnValue($configAddressType));

        $quoteAddress->expects($this->once())->method('hasPrevQuoteCustomerGroupId');
        $id = $quoteAddress->expects($this->any())->method('getPrevQuoteCustomerGroupId');
        $quoteAddress->expects($this->any())->method('setCustomerGroupId')->with($id);
        $quoteAddress->expects($this->any())->method('getQuote');
        $quoteAddress->expects($this->any())->method('unsPrevQuoteCustomerGroupId');

        $this->assertNull($this->quote->execute($observer));
    }

    public function restoreCustomerGroupIdDataProvider()
    {
        return [
            [\Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING],
            [null],
            [\Magento\Customer\Model\Address\AbstractAddress::TYPE_BILLING],
        ];
    }
}
