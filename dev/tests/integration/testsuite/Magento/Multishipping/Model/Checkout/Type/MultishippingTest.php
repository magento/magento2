<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Model\Checkout\Type;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * @magentoAppArea frontend
 */
class MultishippingTest extends \PHPUnit\Framework\TestCase
{
    const ADDRESS_TYPE_SHIPPING = 'shipping';

    const ADDRESS_TYPE_BILLING = 'billing';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Multishipping
     */
    private $model;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->addressRepository = $this->objectManager->get(AddressRepositoryInterface::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $orderSender = $this->getMockBuilder(OrderSender::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->create(
            Multishipping::class,
            ['orderSender' => $orderSender]
        );
    }

    /**
     * Test case when default billing and shipping addresses are set and they are different.
     *
     * @param string $addressType
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     * @dataProvider getCustomerDefaultAddressDataProvider
     */
    public function testGetCustomerDefaultAddress($addressType)
    {
        /**
         * Preconditions:
         * - second address is default address of {$addressType},
         * - current customer is set to customer session
         */
        $secondFixtureAddressId = 2;
        $secondFixtureAddressStreet = ['Black str, 48'];

        $methodName = 'getCustomerDefault' . ucfirst($addressType) . 'Address';
        $setterMethodName = 'setDefault' . ucfirst($addressType);

        $customer = $this->customerRepository->get('customer@example.com');
        $customer->$setterMethodName($secondFixtureAddressId);
        $this->customerRepository->save($customer);

        /** @var Customer $customerModel */
        $customerModel = $this->objectManager->create(Customer::class);
        $customerModel->updateData($customer);
        /** @var Session $customerSession */
        $customerSession = $this->objectManager->get(Session::class);
        $customerSession->setCustomer($customerModel);

        $addressId = $this->model->$methodName();
        $address = $this->addressRepository->getById($addressId);

        self::assertEquals($secondFixtureAddressId, $address->getId(), "Invalid address loaded.");
        self::assertEquals(
            $secondFixtureAddressStreet,
            $address->getStreet(),
            "Street in default {$addressType} address is invalid."
        );

        /** Ensure that results are cached properly by changing default address and invoking SUT once again */
        $firstFixtureAddressId = 1;
        $customer->$setterMethodName($firstFixtureAddressId);
        $this->customerRepository->save($customer);
        $addressId = $this->model->$methodName();

        $address = $this->addressRepository->getById($addressId);

        self::assertEquals(
            $secondFixtureAddressId,
            $address->getId(),
            "Method results are not cached properly."
        );
    }

    /**
     * Test case when customer has addresses, but default {$addressType} address is not set.
     *
     * @param string $addressType
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoAppIsolation enabled
     * @dataProvider getCustomerDefaultAddressDataProvider
     */
    public function testGetCustomerDefaultAddressDefaultAddressNotSet($addressType)
    {
        /**
         * Preconditions:
         * - customer has addresses, but default address of {$addressType} is not set
         * - current customer is set to customer session
         */
        $firstFixtureAddressId = 1;
        $firstFixtureAddressStreet = ['Green str, 67'];
        $customer = $this->customerRepository->get('customer@example.com');
        $methodName = 'setDefault' . ucfirst($addressType);
        $customer->$methodName(null);
        $this->customerRepository->save($customer);

        /** @var Customer $customerModel */
        $customerModel = $this->objectManager->create(Customer::class);
        $customerModel->updateData($customer);
        /** @var Session $customerSession */
        $customerSession = $this->objectManager->get(Session::class);
        $customerSession->setCustomer($customerModel);

        $methodName = 'getCustomerDefault' . ucfirst($addressType) . 'Address';
        $addressId = $this->model->$methodName();
        $address = $this->addressRepository->getById($addressId);

        self::assertEquals($firstFixtureAddressId, $address->getId(), "Invalid address loaded.");
        self::assertEquals(
            $firstFixtureAddressStreet,
            $address->getStreet(),
            "Street in default {$addressType} address is invalid."
        );
    }

    /**
     * Test case when customer has no addresses.
     *
     * @param string $addressType
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     * @dataProvider getCustomerDefaultAddressDataProvider
     */
    public function testGetCustomerDefaultAddressCustomerWithoutAddresses($addressType)
    {
        /**
         * Preconditions:
         * - customer has no addresses
         * - current customer is set to customer session
         */
        $customer = $this->customerRepository->get('customer@example.com');
        $customer->setDefaultShipping(null)
            ->setDefaultBilling(null);
        $this->customerRepository->save($customer);

        /** @var Customer $customerModel */
        $customerModel = $this->objectManager->create(Customer::class);
        $customerModel->updateData($customer);
        /** @var Session $customerSession */
        $customerSession = $this->objectManager->get(Session::class);
        $customerSession->setCustomer($customerModel);

        $methodName = 'getCustomerDefault' . ucfirst($addressType) . 'Address';
        $address = $this->model->$methodName();

        self::assertNull($address, "When customer has no addresses, null is expected.");
    }

    public function getCustomerDefaultAddressDataProvider()
    {
        return [
            self::ADDRESS_TYPE_SHIPPING => [self::ADDRESS_TYPE_SHIPPING],
            self::ADDRESS_TYPE_BILLING => [self::ADDRESS_TYPE_BILLING]
        ];
    }

    /**
     * Checks a case when multiple orders with different shipping addresses are created successfully.
     *
     * @magentoDataFixture Magento/Multishipping/Fixtures/quote_with_split_items.php
     */
    public function testCreateOrders()
    {
        $quote = $this->getQuote('multishipping_quote_id');
        /** @var CheckoutSession $session */
        $session = $this->objectManager->get(CheckoutSession::class);
        $session->replaceQuote($quote);

        $this->model->createOrders();

        $orderList = $this->getOrderList($quote->getId());
        self::assertEquals(3, sizeof($orderList));

        /**
         * @var Order $firstOrder
         * The order with $10 simple product
         */
        $firstOrder = array_shift($orderList);
        /**
         * @var Order $secondOrder
         * The order with $20 simple product
         */
        $secondOrder = array_shift($orderList);
        /**
         * @var Order $thirdOrder
         * The order with $5 virtual product and billing address as shipping
         */
        $thirdOrder = array_shift($orderList);

        $this->performOrderAddressAssertions(
            $firstOrder->getShippingAddress(),
            [
                'street' => ['Main Division 1'],
                'city' => 'Culver City',
                'region' => 'California',
                'postcode' => 90800
            ]
        );
        $this->performOrderAddressAssertions(
            $secondOrder->getShippingAddress(),
            [
                'street' => ['Second Division 2'],
                'city' => 'Denver',
                'region' => 'Colorado',
                'postcode' => 80203
            ]
        );
        $this->performOrderAddressAssertions(
            $thirdOrder->getBillingAddress(),
            [
                'street' => ['Third Division 1'],
                'city' => 'New York',
                'region' => 'New York',
                'postcode' => 10029
            ]
        );

        $this->performOrderTotalAssertions(
            $firstOrder->getBaseGrandTotal(),
            15.00
        );
        $this->performOrderTotalAssertions(
            $secondOrder->getBaseGrandTotal(),
            25.00
        );
        $this->performOrderTotalAssertions(
            $thirdOrder->getBaseGrandTotal(),
            5.00
        );
    }

    /**
     * Retrieves quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }

    /**
     * Get list of orders by quote id.
     *
     * @param int $quoteId
     * @return array
     */
    private function getOrderList(int $quoteId): array
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('quote_id', $quoteId)
            ->create();

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        return $orderRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Performs assertions for order address.
     *
     * @param OrderAddressInterface $address
     * @param array $expected
     * @return void
     */
    private function performOrderAddressAssertions(OrderAddressInterface $address, array $expected)
    {
        foreach ($expected as $key => $item) {
            $methodName = 'get' . ucfirst($key);
            self::assertEquals($item, $address->$methodName(), 'The "'. $key . '" does not match.');
        }
    }

    /**
     * Perform assertions for order total amount.
     *
     * @param float $total
     * @param float $expected
     */
    private function performOrderTotalAssertions(float $total, float $expected)
    {
        self::assertEquals($expected, $total, 'Order total amount does not match.');
    }
}
