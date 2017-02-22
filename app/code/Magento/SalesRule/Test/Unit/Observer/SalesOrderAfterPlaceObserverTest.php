<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Observer;

class SalesOrderAfterPlaceObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Observer\SalesOrderAfterPlaceObserver|\PHPUnit_Framework_MockObject_MockObject
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

    /**
     * @var
     */
    protected $ruleCustomerFactory;


    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\Usage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponUsage;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            'Magento\SalesRule\Observer\SalesOrderAfterPlaceObserver',
            [
                'ruleFactory' => $this->ruleFactory,
                'ruleCustomerFactory' => $this->ruleCustomerFactory,
                'coupon' => $this->couponMock,
                'couponUsage' => $this->couponUsage,
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
        $this->ruleCustomerFactory = $this->getMock(
            'Magento\SalesRule\Model\Rule\CustomerFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->couponUsage = $this->getMock('Magento\SalesRule\Model\ResourceModel\Coupon\Usage', [], [], '', false);
    }

    /**
     * @param \\PHPUnit_Framework_MockObject_MockObject $observer
     * @return \PHPUnit_Framework_MockObject_MockObject $order
     */
    protected function initOrderFromEvent($observer)
    {
        $event = $this->getMock('Magento\Framework\Event', ['getOrder'], [], '', false);
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['getAppliedRuleIds', 'getCustomerId', 'getDiscountAmount', 'getCouponCode', '__wakeup'],
            [],
            '',
            false
        );

        $observer->expects($this->any())
            ->method('getEvent')
            ->will($this->returnValue($event));
        $event->expects($this->any())
            ->method('getOrder')
            ->will($this->returnValue($order));

        return $order;
    }

    public function testSalesOrderAfterPlaceWithoutOrder()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $this->initOrderFromEvent($observer);

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function testSalesOrderAfterPlaceWithoutRuleId()
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $order = $this->initOrderFromEvent($observer);
        $discountAmount = 10;
        $order->expects($this->once())
            ->method('getDiscountAmount')
            ->will($this->returnValue($discountAmount));

        $this->ruleFactory->expects($this->never())
            ->method('create');
        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    /**
     * @param int|bool $ruleCustomerId
     * @dataProvider salesOrderAfterPlaceDataProvider
     */
    public function testSalesOrderAfterPlace($ruleCustomerId)
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $rule = $this->getMock('Magento\SalesRule\Model\Rule', [], [], '', false);
        $ruleCustomer = $this->getMock(
            'Magento\SalesRule\Model\Rule\Customer',
            [
                'setCustomerId',
                'loadByCustomerRule',
                'getId',
                'setTimesUsed',
                'setRuleId',
                'save',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $order = $this->initOrderFromEvent($observer);
        $ruleId = 1;
        $couponId = 1;
        $customerId = 1;
        $discountAmount = 10;

        $order->expects($this->once())
            ->method('getAppliedRuleIds')
            ->will($this->returnValue($ruleId));
        $order->expects($this->once())
            ->method('getDiscountAmount')
            ->will($this->returnValue($discountAmount));
        $order->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));
        $this->ruleFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($rule));
        $rule->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($ruleId));
        $this->ruleCustomerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($ruleCustomer));
        $ruleCustomer->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($ruleCustomerId));
        $ruleCustomer->expects($this->any())
            ->method('setCustomerId')
            ->will($this->returnSelf());
        $ruleCustomer->expects($this->any())
            ->method('setRuleId')
            ->will($this->returnSelf());
        $this->couponMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($couponId));

        $this->couponUsage->expects($this->once())
            ->method('updateCustomerCouponTimesUsed')
            ->with($customerId, $couponId);

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function salesOrderAfterPlaceDataProvider()
    {
        return [
            'With customer rule id' => [1],
            'Without customer rule id' => [null]
        ];
    }
}
