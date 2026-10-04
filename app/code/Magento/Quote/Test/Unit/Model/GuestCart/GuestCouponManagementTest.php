<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\GuestCart\GuestCouponManagement;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestCouponManagementTest extends TestCase
{
    use MockCreationTrait;
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
        $this->couponManagementMock = $this->createMock(CouponManagementInterface::class);

        $this->couponCode = ' test_coupon_code';
        $this->maskedCartId = 'f216207248d65c789b17be8545e0aa73';
        $this->cartId = 123;

        // Create QuoteIdMask mock
        $this->quoteIdMaskMock = $this->createPartialMockWithReflection(QuoteIdMask::class, ["load", "getQuoteId"]);
        $this->quoteIdMaskMock->method("load")->willReturnSelf();
        $this->quoteIdMaskMock->method("getQuoteId")->willReturn($this->cartId);
        
        // Create QuoteIdMaskFactory mock
        $this->quoteIdMaskFactoryMock = $this->createMock(QuoteIdMaskFactory::class);
        $this->quoteIdMaskFactoryMock->method("create")->willReturn($this->quoteIdMaskMock);

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
