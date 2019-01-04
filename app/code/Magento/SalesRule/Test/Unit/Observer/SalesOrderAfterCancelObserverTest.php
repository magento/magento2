<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponRepository;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Customer;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\SalesRule\Model\RuleRepository;
use Magento\SalesRule\Observer\SalesOrderAfterCancelObserver;

/**
 * Unit tests for \Magento\SalesRule\Observer\SalesOrderAfterCancelObserver class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SalesOrderAfterCancelObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SalesOrderAfterCancelObserver
     */
    private $model;

    /**
     * @var Coupon|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coupon;

    /**
     * @var CouponRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $couponRepository;

    /**
     * @var RuleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ruleRepository;

    /**
     * @var Usage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $couponUsage;

    /**
     * @var CustomerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerFactory;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnection;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->initMocks();

        $this->model = $helper->getObject(
            SalesOrderAfterCancelObserver::class,
            [
                'coupon' => $this->coupon,
                'couponRepository' => $this->couponRepository,
                'ruleRepository' => $this->ruleRepository,
                'couponUsage' => $this->couponUsage,
                'customerFactory' => $this->customerFactory,
                'resourceConnection' => $this->resourceConnection,
            ]
        );
    }

    public function testSalesOrderAfterCancelWithoutOrder(): void
    {
        $observer = $this->createMock(Observer::class);
        $this->initOrderFromEvent($observer);

        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    public function testSalesOrderAfterCancelWithoutCustomerId(): void
    {
        $observer = $this->createMock(Observer::class);
        $order = $this->initOrderFromEvent($observer);

        $customerId = null;
        $order->expects($this->once())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));
        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    /**
     * @param string|null $emptyCouponCode
     *
     * @dataProvider salesOrderAfterCancelWithoutCouponCodeDataProvider
     */
    public function testSalesOrderAfterCancelWithoutCouponCode($emptyCouponCode): void
    {
        $observer = $this->createMock(Observer::class);
        $order = $this->initOrderFromEvent($observer);

        $order->expects($this->any())
            ->method('getCouponCode')
            ->will($this->returnValue($emptyCouponCode));
        $this->assertEquals($this->model, $this->model->execute($observer));
    }

    /**
     * @return array
     */
    public function salesOrderAfterCancelWithoutCouponCodeDataProvider(): array
    {
        return [
            'Empty coupon code' => [''],
            'Without coupon code' => [null]
        ];
    }

    /**
     * @param string $ruleToDate
     *
     * @dataProvider salesOrderAfterCancelDataProvider
     */
    public function testSalesOrderAfterCancel(string $ruleToDate): void
    {
        $observer = $this->createMock(Observer::class);
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
                'decrementCustomerCouponTimesUsed',

            ]
        );
        $connectionMock = $this->createPartialMock(
            Mysql::class,
            [
                '__wakeup',
                'commit',
                'rollBack',
                'beginTransaction',
            ]
        );
        $rule = $this->createPartialMock(
            Rule::class,
            [
                '__wakeup',
                'getRuleId',
                'getToDate',
            ]
        );
        $ruleCustomer = $this->createPartialMock(
            Customer::class,
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
            ->will($this->returnValue($connectionMock));
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

    public function salesOrderAfterCancelDataProvider(): array
    {
        return [
            'Rule to_date has ended' => ['2000-01-01'],
            'Rule to_date is actual' => ['2100-01-01'],
        ];
    }

    /**
     * Build necessary mocks for test.
     */
    private function initMocks(): void
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
                'decrementCustomerCouponTimesUsed'
            ]
        );
        $this->couponRepository = $this->createMock(CouponRepository::class);
        $this->ruleRepository = $this->createMock(RuleRepository::class);
        $this->couponUsage = $this->createMock(Usage::class);
        $this->customerFactory = $this->createPartialMock(
            CustomerFactory::class,
            ['create']
        );
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $observer
     *
     * @return \PHPUnit_Framework_MockObject_MockObject $order
     */
    private function initOrderFromEvent($observer): \PHPUnit_Framework_MockObject_MockObject
    {
        $event = $this->createPartialMock(Event::class, ['getOrder']);
        $order = $this->createPartialMock(
            Order::class,
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
}
