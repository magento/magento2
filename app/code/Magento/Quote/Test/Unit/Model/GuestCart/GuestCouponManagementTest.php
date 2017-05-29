<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\GuestCart;

class GuestCouponManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\GuestCart\GuestCouponManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponManagementMock;

    /**
     * @var string
     */
    protected $maskedCartId;

    /**
     * @var int
     */
    protected $cartId;

    /**
     * @var string
     */
    protected $couponCode;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->couponManagementMock = $this->getMock(
            \Magento\Quote\Api\CouponManagementInterface::class,
            [],
            [],
            '',
            false
        );

        $this->couponCode = 'test_coupon_code';
        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $objectManager->getObject(
            \Magento\Quote\Model\GuestCart\GuestCouponManagement::class,
            [
                'couponManagement' => $this->couponManagementMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock
            ]
        );
    }

    public function testGet()
    {
        $this->couponManagementMock->expects($this->once())->method('get')->willReturn($this->couponCode);
        $this->assertEquals($this->couponCode, $this->model->get($this->maskedCartId));
    }

    public function testSet()
    {
        $this->couponManagementMock->expects($this->once())->method('set')->willReturn(true);
        $this->assertTrue($this->model->set($this->maskedCartId, $this->couponCode));
    }

    public function testRemove()
    {
        $this->couponManagementMock->expects($this->once())->method('remove')->willReturn(true);
        $this->assertTrue($this->model->remove($this->maskedCartId));
    }
}
