<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\CouponManagement;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CouponManagementTest extends TestCase
{
    /**
     * @var CartRepositoryInterface|MockObject
     */
    private CartRepositoryInterface|MockObject $quoteRepository;

    /**
     * @var CouponManagement
     */
    private CouponManagement $couponManagement;

    protected function setUp(): void
    {
        $this->quoteRepository = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->couponManagement = new CouponManagement($this->quoteRepository);

        parent::setUp();
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function testSetCouponSuccess(): void
    {
        $cartId = 1;
        $couponCode = ' code ';

        $shippingAddress = $this->getShippingAddressMock();
        $shippingAddress->expects($this->once())->method('setCollectShippingRates')->with(true);
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCouponCode', 'getCouponCode'])
            ->onlyMethods(['getItemsCount', 'getStoreId', 'getShippingAddress', 'collectTotals'])
            ->getMock();
        $quote->expects($this->once())->method('getItemsCount')->willReturn(2);
        $quote->expects($this->once())->method('getStoreId')->willReturn(1);
        $quote->expects($this->once())->method('getShippingAddress')->willReturn($shippingAddress);
        $quote->expects($this->once())->method('setCouponCode')->with(trim($couponCode));
        $quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quote->expects($this->once())->method('getCouponCode')->willReturn(trim($couponCode));
        $this->quoteRepository->expects($this->once())->method('getActive')->with($cartId)->willReturn($quote);
        $this->quoteRepository->expects($this->once())->method('save');

        $this->assertTrue($this->couponManagement->set($cartId, $couponCode));
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function testSetCouponNoEntityExceptionProducts(): void
    {
        $cartId = 1;
        $couponCode = ' code ';

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('The "' . $cartId . '" Cart doesn\'t contain products.');

        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCouponCode', 'getCouponCode'])
            ->onlyMethods(['getItemsCount', 'getStoreId', 'getShippingAddress', 'collectTotals'])
            ->getMock();
        $quote->expects($this->once())->method('getItemsCount')->willReturn(0);
        $this->quoteRepository->expects($this->once())->method('getActive')->with($cartId)->willReturn($quote);

        $this->couponManagement->set($cartId, $couponCode);
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function testSetCouponNoEntityExceptionStore(): void
    {
        $cartId = 1;
        $couponCode = ' code ';

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Cart isn\'t assigned to correct store');

        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCouponCode', 'getCouponCode'])
            ->onlyMethods(['getItemsCount', 'getStoreId', 'getShippingAddress', 'collectTotals'])
            ->getMock();
        $quote->expects($this->once())->method('getItemsCount')->willReturn(1);
        $quote->expects($this->once())->method('getStoreId')->willReturn(0);
        $this->quoteRepository->expects($this->once())->method('getActive')->with($cartId)->willReturn($quote);

        $this->couponManagement->set($cartId, $couponCode);
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function testSetCouponExceptionSave(): void
    {
        $cartId = 1;
        $couponCode = ' code ';

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage("The coupon code couldn't be applied. Verify the coupon code and try again.");

        $shippingAddress = $this->getShippingAddressMock();
        $shippingAddress->expects($this->once())->method('setCollectShippingRates')->with(true);
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCouponCode', 'getCouponCode'])
            ->onlyMethods(['getItemsCount', 'getStoreId', 'getShippingAddress', 'collectTotals'])
            ->getMock();
        $quote->expects($this->once())->method('getItemsCount')->willReturn(2);
        $quote->expects($this->once())->method('getStoreId')->willReturn(1);
        $quote->expects($this->once())->method('getShippingAddress')->willReturn($shippingAddress);
        $quote->expects($this->once())->method('setCouponCode')->with(trim($couponCode));
        $quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $this->quoteRepository->expects($this->once())->method('getActive')->with($cartId)->willReturn($quote);
        $this->quoteRepository->expects($this->once())->method('save')->willThrowException(new \Exception());

        $this->couponManagement->set($cartId, $couponCode);
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function testSetCouponNoEntityExceptionCoupon(): void
    {
        $cartId = 1;
        $couponCode = ' code ';

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage("The coupon code isn't valid. Verify the code and try again.");

        $shippingAddress = $this->getShippingAddressMock();
        $shippingAddress->expects($this->once())->method('setCollectShippingRates')->with(true);
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCouponCode', 'getCouponCode'])
            ->onlyMethods(['getItemsCount', 'getStoreId', 'getShippingAddress', 'collectTotals'])
            ->getMock();
        $quote->expects($this->once())->method('getItemsCount')->willReturn(2);
        $quote->expects($this->once())->method('getStoreId')->willReturn(1);
        $quote->expects($this->once())->method('getShippingAddress')->willReturn($shippingAddress);
        $quote->expects($this->once())->method('setCouponCode')->with(trim($couponCode));
        $quote->expects($this->once())->method('collectTotals')->willReturnSelf();
        $quote->expects($this->once())->method('getCouponCode')->willReturn(null);
        $this->quoteRepository->expects($this->once())->method('getActive')->with($cartId)->willReturn($quote);
        $this->quoteRepository->expects($this->once())->method('save');

        $this->couponManagement->set($cartId, $couponCode);
    }

    /**
     * @return MockObject
     */
    private function getShippingAddressMock(): MockObject
    {
        return $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->addMethods(['setCollectShippingRates'])
            ->getMock();
    }
}
