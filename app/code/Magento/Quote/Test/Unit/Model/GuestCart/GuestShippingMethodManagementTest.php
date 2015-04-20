<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model\GuestCart;

class GuestShippingMethodManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Api\GuestShippingMethodManagementInterface
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingMethodManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteIdMaskFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteIdMaskMock;

    /**
     * @var string
     */
    private $maskedCartId;

    /**
     * @var string
     */
    private $cartId;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->shippingMethodManagementMock =
            $this->getMockBuilder('Magento\Quote\Api\ShippingMethodManagementInterface')
            ->getMockForAbstractClass();
        $this->quoteIdMaskFactoryMock = $this->getMockBuilder('Magento\Quote\Model\QuoteIdMaskFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteIdMaskMock = $this->getMockBuilder('Magento\Quote\Model\QuoteIdMask')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = $objectManager->getObject(
            'Magento\Quote\Model\GuestCart\GuestShippingMethodManagement',
            [
                'shippingMethodManagement' => $this->shippingMethodManagementMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
            ]
        );

        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 867;

        $this->quoteIdMaskFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('load')
            ->with($this->maskedCartId, 'masked_id')
            ->willReturn($this->quoteIdMaskMock);
        $this->quoteIdMaskMock->expects($this->once())
            ->method('getId')
            ->willReturn($this->cartId);
    }

    public function testSet()
    {
        $carrierCode = 'carrierCode';
        $methodCode = 'methodCode';

        $retValue = 'retValue';
        $this->shippingMethodManagementMock->expects($this->once())
            ->method('set')
            ->with($this->cartId, $carrierCode, $methodCode)
            ->will($this->returnValue($retValue));

        $this->assertEquals($retValue, $this->model->set($this->maskedCartId, $carrierCode, $methodCode));
    }

    public function testGetList()
    {
        $retValue = 'retValue';
        $this->shippingMethodManagementMock->expects($this->once())
            ->method('getList')
            ->with($this->cartId)
            ->will($this->returnValue($retValue));

        $this->assertEquals($retValue, $this->model->getList($this->maskedCartId));
    }

    public function testGet()
    {
        $retValue = 'retValue';
        $this->shippingMethodManagementMock->expects($this->once())
            ->method('get')
            ->with($this->cartId)
            ->will($this->returnValue($retValue));

        $this->assertEquals($retValue, $this->model->get($this->maskedCartId));
    }
}
