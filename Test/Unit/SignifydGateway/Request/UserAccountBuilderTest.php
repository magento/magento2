<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Unit\SignifydGateway\Request;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use Magento\Signifyd\Model\SignifydGateway\Request\CustomerOrders;
use Magento\Signifyd\Model\SignifydGateway\Request\UserAccountBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for Signifyd account builder
 */
class UserAccountBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    private static $customerId = 1;

    /**
     * Order amount in EUR
     * @var int
     */
    private static $eurAmount = 100;

    /**
     * Order amount in UAH
     * @var int
     */
    private static $uahAmount = 270;

    /**
     * Order amount in USD
     * @var int
     */
    private static $usdAmount = 50;

    /**
     * @var UserAccountBuilder
     */
    private $builder;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CustomerOrders|MockObject
     */
    private $customerOrdersService;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var Currency|MockObject
     */
    private $eurCurrency;

    /**
     * @var Currency|MockObject
     */
    private $uahCurrency;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->customerOrdersService = $this->getMockBuilder(CustomerOrders::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->setMethods(['getById'])
            ->getMockForAbstractClass();

        $dateTimeFactory = new DateTimeFactory();

        $this->builder = $this->objectManager->getObject(UserAccountBuilder::class, [
            'customerRepository' => $this->customerRepository,
            'dateTimeFactory' => $dateTimeFactory,
            'customerOrders' => $this->customerOrdersService
        ]);

        $this->initCurrencies();

        $this->objectManager->setBackwardCompatibleProperty(
            $this->builder,
            'currencies',
            ['EUR' => $this->eurCurrency, 'UAH' => $this->uahCurrency]
        );
    }

    /**
     * @covers \Magento\Signifyd\Model\SignifydGateway\Request\UserAccountBuilder::build
     */
    public function testBuild()
    {
        $order = $this->getOrder();

        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->setMethods(['getEmail', 'getCreatedAt', 'getUpdatedAt'])
            ->getMockForAbstractClass();
        $customer->expects(static::exactly(2))
            ->method('getEmail')
            ->willReturn('jonh.doe@testmage.com');
        $customer->expects(static::once())
            ->method('getCreatedAt')
            ->willReturn('2016-10-12 12:23:12');
        $customer->expects(static::once())
            ->method('getUpdatedAt')
            ->willReturn('2016-12-14 18:19:00');

        $this->customerRepository->expects(static::once())
            ->method('getById')
            ->with(self::$customerId)
            ->willReturn($customer);

        $orders = $this->getOrders();
        $this->customerOrdersService->expects(static::once())
            ->method('get')
            ->with(self::$customerId)
            ->willReturn($orders);

        $this->eurCurrency->expects(static::once())
            ->method('convert')
            ->with(self::$eurAmount, 'USD')
            ->willReturn(109);

        $this->uahCurrency->expects(static::once())
            ->method('convert')
            ->with(self::$uahAmount, 'USD')
            ->willReturn(10.35);

        $actual = $this->builder->build($order);

        static::assertEquals(3, $actual['userAccount']['aggregateOrderCount']);
        static::assertEquals(169.35, $actual['userAccount']['aggregateOrderDollars']);
    }

    /**
     * Creates mocks for currencies
     * @return void
     */
    private function initCurrencies()
    {
        $this->eurCurrency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->setMethods(['convert'])
            ->getMock();

        $this->uahCurrency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->setMethods(['convert'])
            ->getMock();
    }

    /**
     * Creates order mock
     * @return Order|MockObject
     */
    private function getOrder()
    {
        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBillingAddress', 'getCustomerId'])
            ->getMock();

        $order->expects(static::once())
            ->method('getCustomerId')
            ->willReturn(self::$customerId);

        $billingAddress = $this->getMockBuilder(OrderAddressInterface::class)
            ->setMethods(['getTelephone'])
            ->getMockForAbstractClass();
        $billingAddress->expects(static::once())
            ->method('getTelephone')
            ->willReturn('444-444-44');

        $order->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);

        return $order;
    }

    /**
     * Get list of mocked orders with different currencies
     * @return array
     */
    private function getOrders()
    {
        $eurOrder = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseGrandTotal', 'getBaseCurrencyCode'])
            ->getMock();

        $eurOrder->expects(static::once())
            ->method('getBaseGrandTotal')
            ->willReturn(self::$eurAmount);
        $eurOrder->expects(static::once())
            ->method('getBaseCurrencyCode')
            ->willReturn('EUR');

        $uahOrder = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseGrandTotal', 'getBaseCurrencyCode'])
            ->getMock();

        $uahOrder->expects(static::once())
            ->method('getBaseGrandTotal')
            ->willReturn(self::$uahAmount);
        $uahOrder->expects(static::once())
            ->method('getBaseCurrencyCode')
            ->willReturn('UAH');

        $usdOrder = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseGrandTotal', 'getBaseCurrencyCode'])
            ->getMock();

        $usdOrder->expects(static::once())
            ->method('getBaseGrandTotal')
            ->willReturn(self::$usdAmount);
        $usdOrder->expects(static::once())
            ->method('getBaseCurrencyCode')
            ->willReturn('USD');

        return [$eurOrder, $uahOrder, $usdOrder];
    }
}
