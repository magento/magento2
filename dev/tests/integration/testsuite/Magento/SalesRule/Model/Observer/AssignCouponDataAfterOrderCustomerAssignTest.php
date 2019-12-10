<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Observer;

use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\GroupManagement;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\OrderService;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssignCouponDataAfterOrderCustomerAssignTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Quote\Api\GuestCartManagementInterface
     */
    private $assignCouponToCustomerObserver;

    /**
     * @var Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Order\OrderCustomerDelegate
     */
    private $delegateCustomerService;

    /**
     * @var Magento\SalesRule\Model\Rule\CustomerFactory
     */
    private $ruleCustomerFactory;

    /**
     * @var Rule
     */
    private $salesRule;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->orderRepository = $this->objectManager->get(\Magento\Sales\Model\OrderRepository::class);
        $this->delegateCustomerService = $this->objectManager->get(Order\OrderCustomerDelegate::class);
        $this->customerRepository = $this->objectManager->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->ruleCustomerFactory =  $this->objectManager->get(\Magento\SalesRule\Model\Rule\CustomerFactory::class);
        $this->assignCouponToCustomerObserver = $this->objectManager->get(
            \Magento\SalesRule\Observer\AssignCouponDataAfterOrderCustomerAssignObserver::class
        );
        $this->orderService = $this->objectManager->get(OrderService::class);

        $this->salesRule = $this->prepareSalesRule();
        $this->coupon = $this->attachSalesruleCoupon($this->salesRule);
        $this->order  = $this->makeOrderWithCouponAsGuest($this->coupon);
        $this->delegateOrderToBeAssigned($this->order);
        $this->customer = $this->registerNewCustomer();
        $this->order->setCustomerId($this->customer->getId());
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->salesRule = null;
        $this->customer = null;
        $this->coupon = null;
        $this->order = null;
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCouponDataHasBeenAssignedTest()
    {
        $ruleCustomer = $this->getSalesruleCustomerUsage($this->customer, $this->salesRule);

        // Assert, that rule customer model has been created for specific customer
        $this->assertEquals(
            $ruleCustomer->getCustomerId(),
            $this->customer->getId()
        );

        // Assert, that customer has increased coupon usage of specific rule
        $this->assertEquals(
            1,
            $ruleCustomer->getTimesUsed()
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderCancelingDecreasesCouponUsages()
    {
        $this->processOrder($this->order);

        // Should not throw exception as bux is fixed now
        $this->orderService->cancel($this->order->getId());
        $ruleCustomer = $this->getSalesruleCustomerUsage($this->customer, $this->salesRule);

        // Assert, that rule customer model has been created for specific customer
        $this->assertEquals(
            $ruleCustomer->getCustomerId(),
            $this->customer->getId()
        );

        // Assert, that customer has increased coupon usage of specific rule
        $this->assertEquals(
            0,
            $ruleCustomer->getTimesUsed()
        );
    }

    /**
     * @param Order $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    private function processOrder(Order $order)
    {
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus(Order::STATE_PROCESSING);
        return $this->orderRepository->save($order);
    }

    /**
     * @param Customer $customer
     * @param Rule $rule
     * @return Rule\Customer
     */
    private function getSalesruleCustomerUsage(Customer $customer, Rule $rule) : \Magento\SalesRule\Model\Rule\Customer
    {
        $ruleCustomer = $this->ruleCustomerFactory->create();
        return $ruleCustomer->loadByCustomerRule($customer->getId(), $rule->getRuleId());
    }

    /**
     * @return Rule
     */
    private function prepareSalesRule() : Rule
    {
        /** @var Rule $salesRule */
        $salesRule = $this->objectManager->create(Rule::class);
        $salesRule->setData(
            [
                'name' => '15$ fixed discount on whole cart',
                'is_active' => 1,
                'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
                'coupon_type' => Rule::COUPON_TYPE_SPECIFIC,
                'conditions' => [
                    [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'attribute' => 'base_subtotal',
                        'operator' => '>',
                        'value' => 45,
                    ],
                ],
                'simple_action' => Rule::CART_FIXED_ACTION,
                'discount_amount' => 15,
                'discount_step' => 0,
                'stop_rules_processing' => 1,
                'website_ids' => [
                    $this->objectManager->get(StoreManagerInterface::class)->getWebsite()->getId(),
                ],
            ]
        );
        Bootstrap::getObjectManager()->get(
            \Magento\SalesRule\Model\ResourceModel\Rule::class
        )->save($salesRule);

        return $salesRule;
    }

    /**
     * @param Rule $salesRule
     * @return Coupon
     */
    private function attachSalesruleCoupon(Rule $salesRule) : Coupon
    {
        $coupon = $this->objectManager->create(Coupon::class);
        $coupon->setRuleId($salesRule->getId())
            ->setCode('CART_FIXED_DISCOUNT_15')
            ->setType(0);

        Bootstrap::getObjectManager()->get(CouponRepositoryInterface::class)->save($coupon);

        return $coupon;
    }

    /**
     * @param Coupon $coupon
     * @return Order
     */
    private function makeOrderWithCouponAsGuest(Coupon $coupon) : Order
    {
        $order = Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001')
            ->setCustomerIsGuest(true)
            ->setCouponCode($coupon->getCode())
            ->setCreatedAt('2014-10-25 10:10:10')
            ->setAppliedRuleIds($coupon->getRuleId())
            ->save();

        return $order;
    }

    /**
     * @param Order $order
     */
    private function delegateOrderToBeAssigned(Order $order)
    {
        $this->delegateCustomerService->delegateNew($order->getId());
    }

    /**
     * @return Customer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    private function registerNewCustomer() : Customer
    {
        $customer = Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\Data\CustomerInterface::class
        );

        /** @var Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer->setWebsiteId(1)
            ->setEmail('customer@example.com')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setPrefix('Mr.')
            ->setFirstname('John')
            ->setMiddlename('A')
            ->setLastname('Smith')
            ->setSuffix('Esq.')
            ->setDefaultBilling(1)
            ->setDefaultShipping(1)
            ->setTaxvat('12')
            ->setGender(0);

        $customer = $this->customerRepository->save($customer, 'password');

        return $customer;
    }
}
