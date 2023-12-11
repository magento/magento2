<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\CouponManagement;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CouponManagementTest extends TestCase
{
    /**
     * @var CouponManagement
     */
    protected $couponManagement;

    /**
     * @var MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var MockObject
     */
    protected $quoteMock;

    /**
     * @var MockObject
     */
    protected $storeMock;

    /**
     * @var MockObject
     */
    protected $quoteAddressMock;

    protected function setUp(): void
    {
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->storeMock = $this->createMock(Store::class);
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['setCouponCode', 'getCouponCode'])
            ->onlyMethods(['getItemsCount', 'collectTotals', 'save', 'getShippingAddress', 'getStoreId', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteAddressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['setCollectShippingRates'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->couponManagement = new CouponManagement(
            $this->quoteRepositoryMock
        );
    }

    public function testGetCoupon()
    {
        $cartId = 11;
        $couponCode = 'test_coupon_code';

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCouponCode'])
            ->onlyMethods(['__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())->method('getCouponCode')->willReturn($couponCode);

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($quoteMock);

        $this->assertEquals($couponCode, $this->couponManagement->get($cartId));
    }

    public function testSetWhenCartDoesNotContainsProducts()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('The "33" Cart doesn\'t contain products.');
        $cartId = 33;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(0);

        $this->couponManagement->set($cartId, 'coupon_code');
    }

    public function testSetWhenCouldNotApplyCoupon()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $this->expectExceptionMessage('The coupon code couldn\'t be applied. Verify the coupon code and try again.');
        $cartId = 33;
        $couponCode = '153a-ABC';

        $this->storeMock->expects($this->any())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('getStoreId')->willReturn($this->returnValue(1));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(12);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())->method('setCollectShippingRates')->with(true);
        $this->quoteMock->expects($this->once())->method('setCouponCode')->with($couponCode);
        $exceptionMessage = "The coupon code couldn't be applied. Verify the coupon code and try again.";
        $exception = new CouldNotDeleteException(__($exceptionMessage));
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);

        $this->couponManagement->set($cartId, $couponCode);
    }

    public function testSetWhenCouponCodeIsInvalid()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('The coupon code isn\'t valid. Verify the code and try again.');
        $cartId = 33;
        $couponCode = '153a-ABC';

        $this->storeMock->expects($this->any())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('getStoreId')->willReturn($this->returnValue(1));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(12);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())->method('setCollectShippingRates')->with(true);
        $this->quoteMock->expects($this->once())->method('setCouponCode')->with($couponCode);
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getCouponCode')->willReturn('invalidCoupon');

        $this->couponManagement->set($cartId, $couponCode);
    }

    public function testSet()
    {
        $cartId = 33;
        $couponCode = '153a-ABC';

        $this->storeMock->expects($this->any())->method('getId')->willReturn(1);
        $this->quoteMock->expects($this->once())->method('getStoreId')->willReturn($this->returnValue(1));

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(12);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())->method('setCollectShippingRates')->with(true);
        $this->quoteMock->expects($this->once())->method('setCouponCode')->with($couponCode);
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getCouponCode')->willReturn($couponCode);

        $this->assertTrue($this->couponManagement->set($cartId, $couponCode));
    }

    public function testDeleteWhenCartDoesNotContainsProducts()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
        $this->expectExceptionMessage('The "65" Cart doesn\'t contain products.');
        $cartId = 65;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(0);
        $this->quoteMock->expects($this->never())->method('getShippingAddress');

        $this->couponManagement->remove($cartId);
    }

    public function testDeleteWhenCouldNotDeleteCoupon()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $this->expectExceptionMessage('The coupon code couldn\'t be deleted. Verify the coupon code and try again.');
        $cartId = 65;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(12);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())->method('setCollectShippingRates')->with(true);
        $this->quoteMock->expects($this->once())->method('setCouponCode')->with('');
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $exceptionMessage = "The coupon code couldn't be deleted. Verify the coupon code and try again.";
        $exception = new CouldNotSaveException(__($exceptionMessage));
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->quoteRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->quoteMock)
            ->willThrowException($exception);

        $this->couponManagement->remove($cartId);
    }

    public function testDeleteWhenCouponIsNotEmpty()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotDeleteException');
        $this->expectExceptionMessage('The coupon code couldn\'t be deleted. Verify the coupon code and try again.');
        $cartId = 65;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(12);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())->method('setCollectShippingRates')->with(true);
        $this->quoteMock->expects($this->once())->method('setCouponCode')->with('');
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getCouponCode')->willReturn('123_ABC');

        $this->couponManagement->remove($cartId);
    }

    public function testDelete()
    {
        $cartId = 65;

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')->with($cartId)->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getItemsCount')->willReturn(12);
        $this->quoteMock->expects($this->once())
            ->method('getShippingAddress')->willReturn($this->quoteAddressMock);
        $this->quoteAddressMock->expects($this->once())->method('setCollectShippingRates')->with(true);
        $this->quoteMock->expects($this->once())->method('setCouponCode')->with('');
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('collectTotals')->willReturn($this->quoteMock);
        $this->quoteRepositoryMock->expects($this->once())->method('save')->with($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getCouponCode')->willReturn('');

        $this->assertTrue($this->couponManagement->remove($cartId));
    }
}
