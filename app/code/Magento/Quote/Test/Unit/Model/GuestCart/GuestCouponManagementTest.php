<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\GuestCart\GuestCouponManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestCouponManagementTest extends TestCase
{
    /**
     * @var GuestCouponManagement
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var MockObject
     */
    protected $quoteIdMaskMock;

    /**
     * @var MockObject
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

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->couponManagementMock = $this->getMockForAbstractClass(CouponManagementInterface::class);

        $this->couponCode = ' test_coupon_code';
        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        $guestCartTestHelper = new GuestCartTestHelper($this);
        list($this->quoteIdMaskFactoryMock, $this->quoteIdMaskMock) = $guestCartTestHelper->mockQuoteIdMask(
            $this->maskedCartId,
            $this->cartId
        );

        $this->model = $objectManager->getObject(
            GuestCouponManagement::class,
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
        $this->couponManagementMock->expects($this->once())
            ->method('set')
            ->with($this->cartId, trim($this->couponCode))
            ->willReturn(true);
        $this->assertTrue($this->model->set($this->maskedCartId, $this->couponCode));
    }

    public function testRemove()
    {
        $this->couponManagementMock->expects($this->once())->method('remove')->willReturn(true);
        $this->assertTrue($this->model->remove($this->maskedCartId));
    }
}
