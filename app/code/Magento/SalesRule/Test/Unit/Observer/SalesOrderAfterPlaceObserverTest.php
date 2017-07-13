<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Observer;

class SalesOrderAfterPlaceObserverTest extends \PHPUnit\Framework\TestCase
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
            \Magento\SalesRule\Observer\SalesOrderAfterPlaceObserver::class,
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
        $this->couponMock = $this->createPartialMock(\Magento\SalesRule\Model\Coupon::class, [
                '__wakeup',
                'save',
                'load',
                'getId',
                'setTimesUsed',
                'getTimesUsed',
                'getRuleId',
                'loadByCode',
                'updateCustomerCouponTimesUsed'
            ]);
        $this->ruleFactory = $this->createPartialMock(\Magento\SalesRule\Model\RuleFactory::class, ['create']);
        $this->ruleCustomerFactory = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule\CustomerFactory::class,
            ['create']
        );
        $this->couponUsage = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Coupon\Usage::class);
    }

    /**
     * @param \\PHPUnit_Framework_MockObject_MockObject $observer
     * @return \PHPUnit_Framework_MockObject_MockObject $order
     */
    protected function initOrderFromEvent($observer)
    {
        $event = $this->createPartialMock(\Magento\Framework\Event::class, ['getOrder']);
        $order = $this->createPartialMock(
            \Magento\Sales\Model\Order::class,
            ['getAppliedRuleIds', 'getCustomerId', 'getDiscountAmount', 'getCouponCode', '__wakeup']
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
        $observer = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->initOrderFromEvent($observer);

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function testSalesOrderAfterPlaceWithoutRuleId()
    {
        $observer = $this->createMock(\Magento\Framework\Event\Observer::class);
        $order = $this->initOrderFromEvent($observer);
        $ruleIds = null;
        $order->expects($this->once())
            ->method('getAppliedRuleIds')
            ->will($this->returnValue($ruleIds));

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
        $observer = $this->createMock(\Magento\Framework\Event\Observer::class);
        $rule = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $ruleCustomer = $this->createPartialMock(\Magento\SalesRule\Model\Rule\Customer::class, [
                'setCustomerId',
                'loadByCustomerRule',
                'getId',
                'setTimesUsed',
                'setRuleId',
                'save',
                '__wakeup'
            ]);
        $order = $this->initOrderFromEvent($observer);
        $ruleId = 1;
        $couponId = 1;
        $customerId = 1;

        $order->expects($this->exactly(2))
            ->method('getAppliedRuleIds')
            ->will($this->returnValue($ruleId));
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
