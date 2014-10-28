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
