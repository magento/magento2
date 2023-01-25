<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Observer\AddSalesRuleNameToOrderObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddSalesRuleNameToOrderObserverTest extends TestCase
{
    /**
     * @var AddSalesRuleNameToOrderObserver|MockObject
     */
    protected $model;

    /**
     * @var Coupon|MockObject
     */
    protected $couponMock;

    /**
     * @var RuleFactory|MockObject
     */
    protected $ruleFactory;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            AddSalesRuleNameToOrderObserver::class,
            [
                'ruleFactory' => $this->ruleFactory,
                'coupon' => $this->couponMock,
            ]
        );
    }

    protected function initMocks()
    {
        $this->couponMock = $this->getMockBuilder(Coupon::class)
            ->addMethods(['updateCustomerCouponTimesUsed'])
            ->onlyMethods(['save', 'load', 'getId', 'setTimesUsed', 'getTimesUsed', 'getRuleId', 'loadByCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->ruleFactory = $this->createPartialMock(RuleFactory::class, ['create']);
    }

    public function testAddSalesRuleNameToOrderWithoutCouponCode()
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $order = $this->getMockBuilder(Order::class)
            ->addMethods(['setCouponRuleName'])
            ->onlyMethods(['getCouponCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->any())
            ->method('getOrder')
            ->willReturn($order);

        $this->couponMock->expects($this->never())
            ->method('loadByCode');

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function testAddSalesRuleNameToOrderWithoutRule()
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $order = $this->getMockBuilder(Order::class)
            ->addMethods(['setCouponRuleName'])
            ->onlyMethods(['getCouponCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $couponCode = 'coupon code';

        $observer->expects($this->any())
            ->method('getOrder')
            ->willReturn($order);

        $order->expects($this->once())
            ->method('getCouponCode')
            ->willReturn($couponCode);
        $this->ruleFactory->expects($this->never())
            ->method('create');

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function testAddSalesRuleNameToOrder()
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->addMethods(['getOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $rule = $this->getMockBuilder(Rule::class)
            ->addMethods(['getName'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $order = $this->getMockBuilder(Order::class)
            ->addMethods(['setCouponRuleName'])
            ->onlyMethods(['getCouponCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $couponCode = 'coupon code';
        $ruleId = 1;

        $observer->expects($this->any())
            ->method('getOrder')
            ->willReturn($order);

        $order->expects($this->once())
            ->method('getCouponCode')
            ->willReturn($couponCode);
        $this->couponMock->expects($this->once())
            ->method('getRuleId')
            ->willReturn($ruleId);
        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->willReturn($rule);
        $rule->expects($this->once())
            ->method('load')
            ->with($ruleId)->willReturnSelf();
        $order->expects($this->once())
            ->method('setCouponRuleName');

        $this->assertEquals($this->model, $this->model->execute($observer));
    }
}
