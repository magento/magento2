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
 * Tests Magento\Sales\Model\Observer\Frontend\Quote\AddVatRequestParamsOrderComment
 */
class AddVatRequestParamsOrderCommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Helper\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerAddressHelperMock;

    /**
     * @var AddVatRequestParamsOrderComment
     */
    protected $observer;

    protected function setUp()
    {
        $this->customerAddressHelperMock = $this->getMockBuilder('Magento\Customer\Helper\Address')
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new AddVatRequestParamsOrderComment(
            $this->customerAddressHelperMock
        );
    }

    /**
     * @param string $configAddressType
     * @param string|int $vatRequestId
     * @param string|int $vatRequestDate
     * @param string $orderHistoryComment
     * @dataProvider addVatRequestParamsOrderCommentDataProvider
     */
    public function testAddVatRequestParamsOrderComment(
        $configAddressType,
        $vatRequestId,
        $vatRequestDate,
        $orderHistoryComment
    ) {
        $this->customerAddressHelperMock->expects($this->once())
            ->method('getTaxCalculationAddressType')
            ->will($this->returnValue($configAddressType));

        $orderAddressMock = $this->getMock(
            'Magento\Sales\Model\Order\Address',
            ['getVatRequestId', 'getVatRequestDate', '__wakeup'],
            [],
            '',
            false
        );
        $orderAddressMock->expects($this->any())
            ->method('getVatRequestId')
            ->will($this->returnValue($vatRequestId));
        $orderAddressMock->expects($this->any())
            ->method('getVatRequestDate')
            ->will($this->returnValue($vatRequestDate));

        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->setMethods(['getShippingAddress', '__wakeup', 'addStatusHistoryComment', 'getBillingAddress'])
            ->getMock();
        $orderMock->expects($this->any())
            ->method('getShippingAddress')
            ->will($this->returnValue($orderAddressMock));
        if (is_null($orderHistoryComment)) {
            $orderMock->expects($this->never())
                ->method('addStatusHistoryComment');
        } else {
            $orderMock->expects($this->once())
                ->method('addStatusHistoryComment')
                ->with($orderHistoryComment, false);
        }
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getOrder'], [], '', false);
        $observer->expects($this->once())
            ->method('getOrder')
            ->will($this->returnValue($orderMock));

        $this->assertNull($this->observer->execute($observer));
    }

    public function addVatRequestParamsOrderCommentDataProvider()
    {
        return [
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING,
                'vatRequestId',
                'vatRequestDate',
                'VAT Request Identifier: vatRequestId<br />VAT Request Date: vatRequestDate',
            ],
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING,
                1,
                'vatRequestDate',
                null,
            ],
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_SHIPPING,
                'vatRequestId',
                1,
                null,
            ],
            [
                null,
                'vatRequestId',
                'vatRequestDate',
                null,
            ],
            [
                \Magento\Customer\Model\Address\AbstractAddress::TYPE_BILLING,
                'vatRequestId',
                'vatRequestDate',
                null,
            ],
        ];
    }
}
