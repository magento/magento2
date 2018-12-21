<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for \Magento\SalesRule\Observer\SalesOrderAfterCancelObserver class
 */
class SalesOrderAfterCancelObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Observer\SalesOrderAfterCancelObserver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var Coupon
     */
    public $coupon;

    /**
     * @var CouponRepositoryInterface
     */
    protected $couponRepository;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\Usage
     */
    protected $couponUsage;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            \Magento\SalesRule\Observer\SalesOrderAfterCancelObserver::class,
            [
                'coupon'             => $this->coupon,
                'couponRepository'   => $this->couponRepository,
                'ruleRepository'     => $this->ruleRepository,
                'couponUsage'        => $this->couponUsage,
                'customerFactory'    => $this->customerFactory,
                'resourceConnection' => $this->resourceConnection,
            ]
        );
    }

    protected function initMocks()
    {
        $this->coupon = $this->createPartialMock(
            Coupon::class,
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
            ]
        );
        $this->couponRepository = $this->createMock(\Magento\SalesRule\Model\CouponRepository::class);
        $this->ruleRepository = $this->createMock(\Magento\SalesRule\Model\RuleRepository::class);
        $this->couponUsage = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Coupon\Usage::class);
        $this->customerFactory = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule\CustomerFactory::class,
            ['create']
        );
        $this->resourceConnection = $this->createMock(ResourceConnection::class, ['create']);
    }

    /**
     * @param \\PHPUnit_Framework_MockObject_MockObject $observer
     *
     * @return \PHPUnit_Framework_MockObject_MockObject $order
     */
    protected function initOrderFromEvent($observer)
    {
        $event = $this->createPartialMock(\Magento\Framework\Event::class, ['getOrder']);
        $order = $this->createPartialMock(
            \Magento\Sales\Model\Order::class,
            ['getCustomerId', 'getCouponCode', '__wakeup']
        );

        $observer->expects($this->any())
                 ->method('getEvent')
                 ->will($this->returnValue($event));
        $event->expects($this->any())
              ->method('getOrder')
              ->will($this->returnValue($order));

        return $order;
    }

    public function testSalesOrderAfterCancelWithoutOrder()
    {
        $observer = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->initOrderFromEvent($observer);

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function testSalesOrderAfterCancelWithoutCustomerId()
    {
        $observer = $this->createMock(\Magento\Framework\Event\Observer::class);
        $order = $this->initOrderFromEvent($observer);

        $customerId = null;
        $order->expects($this->once())
              ->method('getCustomerId')
              ->will($this->returnValue($customerId));
        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    /**
     * @param $emptyCouponCode
     *
     * @dataProvider salesOrderAfterCancelWithoutCouponCodeDataProvider
     */
    public function testSalesOrderAfterCancelWithoutCouponCode($emptyCouponCode)
    {
        $observer = $this->createMock(\Magento\Framework\Event\Observer::class);
        $order = $this->initOrderFromEvent($observer);

        $order->expects($this->any())
              ->method('getCouponCode')
              ->will($this->returnValue($emptyCouponCode));
        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    /**
     * @return array
     */
    public function salesOrderAfterCancelWithoutCouponCodeDataProvider()
    {
        return [
            'Empty coupon code'   => [''],
            'Without coupon code' => [null]
        ];
    }

    /**
     * @param $ruleToDate
     *
     * @dataProvider salesOrderAfterCancelDataProvider
     */
    public function testSalesOrderAfterCancel($ruleToDate)
    {
        $observer = $this->createMock(\Magento\Framework\Event\Observer::class);
        $order = $this->initOrderFromEvent($observer);
        $couponMock = $this->createPartialMock(
            Coupon::class,
            [
                '__wakeup',
                'getCouponId',
                'getRuleId',
                'getId',
                'setTimesUsed',
                'getTimesUsed',
                'updateCustomerCouponTimesUsed',

            ]
        );
        $connectionMockup = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            [
                '__wakeup',
                'commit',
                'rollBack',
                'beginTransaction',
            ]
        );
        $rule = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule::class,
            [
                '__wakeup',
                'getRuleId',
                'getToDate',
            ]
        );
        $ruleCustomer = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule\Customer::class,
            [
                'loadByCustomerRule',
                'getId',
                'setTimesUsed',
                'save',
                '__wakeup'
            ]
        );

        $customerId = 1;
        $couponCode = 'testCoupon';
        $couponId = 1;
        $ruleId = 1;
        $ruleCustomerId = 1;

        $order->expects($this->exactly(2))
              ->method('getCustomerId')
              ->will($this->returnValue($customerId));

        $order->expects($this->exactly(2))
              ->method('getCouponCode')
              ->will($this->returnValue($couponCode));
        $this->coupon->expects($this->once())
                     ->method('loadByCode')
                     ->will($this->returnValue($couponMock));
        $couponMock->expects($this->any())
                   ->method('getCouponId')
                   ->will($this->returnValue($couponId));
        $this->resourceConnection->expects($this->once())
                                 ->method('getConnection')
                                 ->will($this->returnValue($connectionMockup));
        $couponMock->expects($this->once())
                   ->method('getRuleId')
                   ->will($this->returnValue($ruleId));
        $this->ruleRepository->expects($this->once())
                             ->method('getById')
                             ->will($this->returnValue($rule));
        $rule->expects($this->once())
             ->method('getToDate')
             ->will($this->returnValue($ruleToDate));
        $rule->expects($this->once())
             ->method('getRuleId')
             ->will($this->returnValue($ruleId));
        $couponMock->expects($this->any())
                   ->method('setTimesUsed')
                   ->will($this->returnSelf());

        $this->customerFactory->expects($this->any())
                              ->method('create')
                              ->will($this->returnValue($ruleCustomer));
        $ruleCustomer->expects($this->any())
                     ->method('getId')
                     ->will($this->returnValue($ruleCustomerId));
        $ruleCustomer->expects($this->any())
                     ->method('setTimesUsed')
                     ->will($this->returnSelf());

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function salesOrderAfterCancelDataProvider()
    {
        return [
            'Rule to_date has ended' => ['2000-01-01'],
            'Rule to_date is actual' => ['2100-01-01'],
        ];
    }

}
