<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Observer;

class AddSalesRuleNameToOrderObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Observer\AddSalesRuleNameToOrderObserver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\SalesRule\Model\Coupon|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponMock;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactory;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            'Magento\SalesRule\Observer\AddSalesRuleNameToOrderObserver',
            [
                'ruleFactory' => $this->ruleFactory,
                'coupon' => $this->couponMock,
            ]
        );
    }

    protected function initMocks()
    {
        $this->couponMock = $this->getMock(
            '\Magento\SalesRule\Model\Coupon',
            [
                '__wakeup',
                'save',
                'load',
                'getId',
                'setTimesUsed',
                'getTimesUsed',
                'getRuleId',
                'loadByCode',
                'updateCustomerCouponTimesUsed'
            ],
            [],
            '',
            false
        );
        $this->ruleFactory = $this->getMock('Magento\SalesRule\Model\RuleFactory', ['create'], [], '', false);
    }

    public function testAddSalesRuleNameToOrderWithoutCouponCode()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getOrder'], [], '', false);
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['setCouponRuleName', 'getCouponCode', '__wakeup'],
            [],
            '',
            false
        );

        $observer->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $this->couponMock->expects($this->never())
            ->method('loadByCode');

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function testAddSalesRuleNameToOrderWithoutRule()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getOrder'], [], '', false);
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['setCouponRuleName', 'getCouponCode', '__wakeup'],
            [],
            '',
            false
        );
        $couponCode = 'coupon code';

        $observer->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $order->expects($this->once())
            ->method('getCouponCode')
            ->will($this->returnValue($couponCode));
        $this->ruleFactory->expects($this->never())
            ->method('create');

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function testAddSalesRuleNameToOrder()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', ['getOrder'], [], '', false);
        $rule = $this->getMock('Magento\SalesRule\Model\Rule', ['load', 'getName', '__wakeup'], [], '', false);
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['setCouponRuleName', 'getCouponCode', '__wakeup'],
            [],
            '',
            false
        );
        $couponCode = 'coupon code';
        $ruleId = 1;

        $observer->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        $order->expects($this->once())
            ->method('getCouponCode')
            ->will($this->returnValue($couponCode));
        $this->couponMock->expects($this->once())
            ->method('getRuleId')
            ->will($this->returnValue($ruleId));
        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($rule));
        $rule->expects($this->once())
            ->method('load')
            ->with($ruleId)
            ->will($this->returnSelf());
        $order->expects($this->once())
            ->method('setCouponRuleName');

        $this->assertEquals($this->model, $this->model->execute($observer));
    }
}
