<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Api\Exception\CodeRequestLimitException;
use Magento\SalesRule\Model\Spi\CodeLimitManagerInterface;
use Magento\SalesRule\Observer\CouponCodeValidation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CouponCodeValidationTest extends TestCase
{
    /**
     * @var CouponCodeValidation
     */
    private $couponCodeValidation;

    /**
     * @var MockObject&CodeLimitManagerInterface
     */
    private $codeLimitManagerMock;

    /**
     * @var MockObject&CartRepositoryInterface
     */
    private $cartRepositoryMock;

    /**
     * @var MockObject&SearchCriteriaBuilder
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var MockObject&SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderMockFactory;

    /**
     * @var MockObject&Observer
     */
    private $observerMock;

    /**
     * @var MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var MockObject
     */
    private $quoteMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->codeLimitManagerMock = $this->getMockForAbstractClass(CodeLimitManagerInterface::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->cartRepositoryMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->addMethods(['getItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->onlyMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMockFactory = $this->getMockBuilder(SearchCriteriaBuilderFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMockFactory->expects($this->any())->method('create')
            ->willReturn($this->searchCriteriaBuilderMock);
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCouponCode', 'setCouponCode'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->couponCodeValidation = new CouponCodeValidation(
            $this->codeLimitManagerMock,
            $this->cartRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->searchCriteriaBuilderMockFactory
        );
    }

    /**
     * Testing the coupon code that haven't reached the request limit
     */
    public function testCouponCodeNotReachedTheLimit()
    {
        $couponCode = 'AB123';
        $this->observerMock->expects($this->once())->method('getData')->with('quote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getCouponCode')->willReturn($couponCode);
        $this->searchCriteriaBuilderMock->expects($this->once())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(123);
        $this->cartRepositoryMock->expects($this->any())->method('getList')->willReturnSelf();
        $this->cartRepositoryMock->expects($this->any())->method('getItems')->willReturn([]);
        $this->codeLimitManagerMock->expects($this->once())->method('checkRequest')->with($couponCode);
        $this->quoteMock->expects($this->never())->method('setCouponCode')->with('');

        $this->couponCodeValidation->execute($this->observerMock);
    }

    /**
     * Testing with the changed coupon code
     */
    public function testCouponCodeNotReachedTheLimitWithNewCouponCode()
    {
        $couponCode = 'AB123';
        $newCouponCode = 'AB234';

        $this->observerMock->expects($this->once())->method('getData')->with('quote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getCouponCode')->willReturn($couponCode);
        $this->searchCriteriaBuilderMock->expects($this->once())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(123);
        $this->cartRepositoryMock->expects($this->any())->method('getList')->willReturnSelf();
        $this->cartRepositoryMock->expects($this->any())->method('getItems')
            ->willReturn([new DataObject(['coupon_code' => $newCouponCode])]);
        $this->codeLimitManagerMock->expects($this->once())->method('checkRequest')->with($couponCode);
        $this->quoteMock->expects($this->never())->method('setCouponCode')->with('');

        $this->couponCodeValidation->execute($this->observerMock);
    }

    /**
     * Testing the coupon code that reached the request limit
     */
    public function testReachingLimitForCouponCode()
    {
        $this->expectException('Magento\SalesRule\Api\Exception\CodeRequestLimitException');
        $this->expectExceptionMessage('Too many coupon code requests, please try again later.');
        $couponCode = 'AB123';
        $this->observerMock->expects($this->once())->method('getData')->with('quote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getCouponCode')->willReturn($couponCode);
        $this->searchCriteriaBuilderMock->expects($this->once())->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->quoteMock->expects($this->once())->method('getId')->willReturn(123);
        $this->cartRepositoryMock->expects($this->any())->method('getList')->willReturnSelf();
        $this->cartRepositoryMock->expects($this->any())->method('getItems')->willReturn([]);
        $this->codeLimitManagerMock->expects($this->once())->method('checkRequest')->with($couponCode)
            ->willThrowException(
                new CodeRequestLimitException(__('Too many coupon code requests, please try again later.'))
            );
        $this->quoteMock->expects($this->once())->method('setCouponCode')->with('');

        $this->couponCodeValidation->execute($this->observerMock);
    }

    /**
     * Testing the quote that doesn't have a coupon code set
     */
    public function testQuoteWithNoCouponCode()
    {
        $couponCode = null;
        $this->observerMock->expects($this->once())->method('getData')->with('quote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getCouponCode')->willReturn($couponCode);
        $this->quoteMock->expects($this->never())->method('getId')->willReturn(123);
        $this->quoteMock->expects($this->never())->method('setCouponCode')->with('');

        $this->couponCodeValidation->execute($this->observerMock);
    }
}
