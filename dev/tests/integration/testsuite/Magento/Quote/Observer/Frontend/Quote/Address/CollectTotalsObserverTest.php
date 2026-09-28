<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Observer\Frontend\Quote\Address;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Group;
use Magento\Framework\Event\Observer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Shipping;
use Magento\Quote\Model\ShippingAssignment;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectTotalsObserverTest extends TestCase
{
    private const STUB_CUSTOMER_EMAIL = 'customer@example.com';

    /**
     * @var CollectTotalsObserver
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(CollectTotalsObserver::class);
    }

    /**
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store customer/create_account/default_group 1
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     *
     * @covers \Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver::execute
     */
    public function testChangeQuoteCustomerGroupIdForCustomerWithDisabledAutomaticGroupChange(): void
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = Bootstrap::getObjectManager();

        /** @var $customer Customer */
        $customer = $objectManager->create(Customer::class);
        $customer->load(1);
        $customer->setDisableAutoGroupChange(1);
        $customer->setGroupId(2);
        $customer->save();

        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
        $customerData = $customerRepository->getById($customer->getId());

        /** @var $quote Quote */
        $quote = $objectManager->create(Quote::class);
        $quote->load('test01', 'reserved_order_id');
        $quote->setCustomer($customerData);

        $quoteAddress = $quote->getBillingAddress();
        $shippingAssignment = $this->objectManager->create(ShippingAssignment::class);
        $shipping = $this->objectManager->create(Shipping::class);
        $shipping->setAddress($quoteAddress);
        $shippingAssignment->setShipping($shipping);
        /** @var  Total $total */
        $total = $this->objectManager->create(Total::class);

        $eventObserver = $objectManager->create(
            Observer::class,
            ['data' => [
                'quote' => $quote,
                'shipping_assignment' => $shippingAssignment,
                'total' => $total
            ]
            ]
        );
        $this->model->execute($eventObserver);

        $this->assertEquals(2, $quote->getCustomer()->getGroupId());
    }

    /**
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store customer/create_account/default_group 1
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     *
     * @covers \Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver::execute
     */
    public function testChangeQuoteCustomerGroupIdForCustomerWithEnabledAutomaticGroupChange(): void
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = Bootstrap::getObjectManager();

        /** @var $customer Customer */
        $customer = $objectManager->create(Customer::class);
        $customer->load(1);
        $customer->setDisableAutoGroupChange(0);
        $customer->setGroupId(2);
        $customer->save();

        /** @var CustomerRegistry $customerRegistry */
        $customerRegistry = $objectManager->get(CustomerRegistry::class);
        $customerRegistry->remove($customer->getId());

        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
        $customerData = $customerRepository->getById($customer->getId());

        /** @var $quote Quote */
        $quote = $objectManager->create(Quote::class);
        $quote->load('test01', 'reserved_order_id');
        $quote->setCustomer($customerData);

        $quoteAddress = $quote->getBillingAddress();

        $shippingAssignment = $this->objectManager->create(ShippingAssignment::class);
        $shipping = $this->objectManager->create(Shipping::class);
        $shipping->setAddress($quoteAddress);
        $shippingAssignment->setShipping($shipping);
        /** @var  Total $total */
        $total = $this->objectManager->create(Total::class);

        $eventObserver = $objectManager->create(
            Observer::class,
            ['data' => ['quote' => $quote, 'shipping_assignment' => $shippingAssignment, 'total' => $total]]
        );
        $this->model->execute($eventObserver);

        $this->assertEquals(2, $quote->getCustomer()->getGroupId());
    }

    /**
     * Dispatch event with guest quote and check that email will not be override to null when auto group assign enabled
     *
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     *
     * @return void
     */
    public function testQuoteCustomerEmailNotChanged(): void
    {
        // prepare quote for guest
        $quote = $this->objectManager->create(Quote::class);
        $quote->setCustomerId(null)
            ->setCustomerEmail(self::STUB_CUSTOMER_EMAIL)
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Group::NOT_LOGGED_IN_ID);

        $quoteAddress = $quote->getBillingAddress();

        $shippingAssignment = $this->objectManager->create(ShippingAssignment::class);
        $shipping = $this->objectManager->create(Shipping::class);
        $shipping->setAddress($quoteAddress);
        $shippingAssignment->setShipping($shipping);
        /** @var  Total $total */
        $total = $this->objectManager->create(Total::class);

        $eventObserver = $this->objectManager->create(
            Observer::class,
            ['data' => ['quote' => $quote, 'shipping_assignment' => $shippingAssignment, 'total' => $total]]
        );
        $this->model->execute($eventObserver);

        $this->assertEquals(self::STUB_CUSTOMER_EMAIL, $quote->getCustomerEmail());
    }
}
