<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Api\Exception\CodeRequestLimitException;
use Magento\SalesRule\Model\Spi\CodeLimitManagerInterface;
use Magento\SalesRule\Observer\CouponCodeValidation;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class CouponCodeValidationTest
 */
class CouponCodeValidationTest extends TestCase
{
    /**
     * @var CouponCodeValidation
     */
    private $couponCodeValidation;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|CodeLimitManagerInterface
     */
    private $codeLimitManagerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|CartRepositoryInterface
     */
    private $cartRepositoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|SearchCriteriaBuilder
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Observer
     */
    private $observerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * Set Up
     */
    protected function setUp()
    {
        $this->codeLimitManagerMock = $this->createMock(CodeLimitManagerInterface::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->cartRepositoryMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->setMethods(['getItems'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->quoteMock = $this->createPartialMock(
            Quote::class,
            ['getCouponCode', 'setCouponCode', 'getId']
        );

        $this->couponCodeValidation = new CouponCodeValidation(
            $this->codeLimitManagerMock,
            $this->cartRepositoryMock,
            $this->searchCriteriaBuilderMock
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
     *
     * @expectedException \Magento\SalesRule\Api\Exception\CodeRequestLimitException
     * @expectedExceptionMessage Too many coupon code requests, please try again later.
     */
    public function testReachingLimitForCouponCode()
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
