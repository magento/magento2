<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\AdminOrder\Create as OrderCreate;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for reorder controller.
 *
 * @see \Magento\Sales\Controller\Adminhtml\Order\Create\Reorder
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReorderTest extends AbstractBackendController
{
    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var CartInterface */
    private $quote;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var array
     */
    private $customerIds = [];

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->orderFactory = $this->_objectManager->get(OrderInterfaceFactory::class);
        $this->quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $this->orderRepository = $this->_objectManager->get(OrderRepositoryInterface::class);
        $this->customerFactory = $this->_objectManager->get(CustomerInterfaceFactory::class);
        $this->accountManagement = $this->_objectManager->get(AccountManagementInterface::class);
        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
        $this->fixtures = $this->_objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->addressRepository = $this->_objectManager->get(AddressRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->quote instanceof CartInterface) {
            $this->quoteRepository->delete($this->quote);
        }
        foreach ($this->customerIds as $customerId) {
            try {
                $this->customerRepository->deleteById($customerId);
            } catch (NoSuchEntityException $e) {
                //customer already deleted
            }
        }
        parent::tearDown();
    }

    /**
     * Reorder with JS calendar options
     *
     * @magentoConfigFixture current_store catalog/custom_options/use_calendar 1
     * @magentoDataFixture Magento/Sales/_files/order_with_date_time_option_product.php
     *
     * @return void
     */
    public function testReorderAfterJSCalendarEnabled(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->reorder($order, 'customer@example.com');
    }

    /**
     * Test load billing address by reorder for delegating customer
     *
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_address.php
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testLoadBillingAddressAfterReorderWithDelegatingCustomer(): void
    {
        $orderId = $this->getOrderWithDelegatingCustomer()->getId();
        $this->getRequest()->setMethod(Http::METHOD_GET);
        $this->getRequest()->setParam('order_id', $orderId);
        $this->dispatch('backend/sales/order_create/loadBlock/block/billing_address');
        $html = $this->getResponse()->getBody();
        $this->assertEquals(
            0,
            Xpath::getElementsCountForXpath(
                '//*[@id="order-billing_address_save_in_address_book" and contains(@checked, "checked")]',
                $html
            ),
            'Billing address checked "Save in address book"'
        );
    }

    /**
     * Get Order with delegating customer
     *
     * @return OrderInterface
     */
    private function getOrderWithDelegatingCustomer(): OrderInterface
    {
        $orderAutoincrementId = '100000001';
        /** @var Order $orderModel */
        $orderModel = $this->orderFactory->create();
        $orderModel->loadByIncrementId($orderAutoincrementId);
        //Saving new customer with prepared data from order.
        /** @var CustomerInterface $customer */
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId(1)
            ->setEmail('customer_order_delegate@example.com')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setPrefix('Mr.')
            ->setFirstname('John')
            ->setMiddlename('A')
            ->setLastname('Smith')
            ->setSuffix('Esq.')
            ->setTaxvat('12')
            ->setGender(0);
        $createdCustomer = $this->accountManagement->createAccount(
            $customer,
            '12345abcD'
        );
        $this->customerIds[] = $createdCustomer->getId();
        $orderModel->setCustomerId($createdCustomer->getId());

        return $this->orderRepository->save($orderModel);
    }

    /**
     * Dispatch reorder request.
     *
     * @param null|int $orderId
     * @return void
     */
    private function dispatchReorderRequest(?int $orderId = null): void
    {
        $this->getRequest()->setMethod(Request::METHOD_GET);
        $this->getRequest()->setParam('order_id', $orderId);
        $this->dispatch('backend/sales/order_create/reorder');
    }

    /**
     * Gets quote by reserved order id.
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    private function getQuote(string $customerEmail): \Magento\Quote\Api\Data\CartInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('customer_email', $customerEmail)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->_objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }

    /**
     * Verify that the updated customer's addresses have been populated for the quote's billing and shipping addresses
     * during reorder.
     *
     * @return void
     * @throws LocalizedException
     */
    #[
        DbIsolation(false),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, ['addresses' => [['postcode' => '12345'] ]], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddress::class, [
            'cart_id' => '$quote.id$',
            'address' => [
                'customer_id' => '$customer.id$',
                'save_in_address_book' => 1
            ]
        ]),
        DataFixture(SetShippingAddress::class, [
            'cart_id' => '$quote.id$',
            'address' => [
                'customer_id' => '$customer.id$',
                'save_in_address_book' => 1
            ]
        ]),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testReorderBillingAndShippingAddresses(): void
    {
        $billingPostCode = '98765';
        $shippingPostCode = '01234';

        $customer = $this->fixtures->get('customer');
        $order = $this->fixtures->get('order');

        $customerBillingAddressId = $order->getBillingAddress()->getCustomerAddressId();
        $this->updateCustomerAddress((int)$customerBillingAddressId, ['postcode' => $billingPostCode]);

        $customerShippingAddressId = $order->getShippingAddress()->getCustomerAddressId();
        $this->updateCustomerAddress((int)$customerShippingAddressId, ['postcode' => $shippingPostCode]);

        $this->reorder($order, $customer->getEmail());

        $orderBillingAddress = $order->getBillingAddress();
        $orderShippingAddress = $order->getShippingAddress();

        $quoteShippingAddress = $this->quote->getShippingAddress();
        $quoteBillingAddress = $this->quote->getBillingAddress();

        $this->assertEquals($quoteBillingAddress->getStreet(), $orderBillingAddress->getStreet());
        $this->assertEquals($billingPostCode, $quoteBillingAddress->getPostCode());
        $this->assertEquals($quoteBillingAddress->getFirstname(), $orderBillingAddress->getFirstname());
        $this->assertEquals($quoteBillingAddress->getCity(), $orderBillingAddress->getCity());
        $this->assertEquals($quoteBillingAddress->getTelephone(), $orderBillingAddress->getTelephone());
        $this->assertEquals($quoteBillingAddress->getEmail(), $orderBillingAddress->getEmail());

        $this->assertEquals($quoteShippingAddress->getStreet(), $orderShippingAddress->getStreet());
        $this->assertEquals($shippingPostCode, $quoteShippingAddress->getPostCode());
        $this->assertEquals($quoteShippingAddress->getFirstname(), $orderShippingAddress->getFirstname());
        $this->assertEquals($quoteShippingAddress->getCity(), $orderShippingAddress->getCity());
        $this->assertEquals($quoteShippingAddress->getTelephone(), $orderShippingAddress->getTelephone());
        $this->assertEquals($quoteShippingAddress->getEmail(), $orderShippingAddress->getEmail());
    }

    /**
     * An edited reorder shipping address survives the next quote save (#41624)
     *
     * Getting shipping rates posts the edited address with an empty customer_address_id (the "Select from existing
     * customer addresses" dropdown is cleared on edit). A later block reload that posts no address, such as loading
     * the payment methods, must not import the saved address over it.
     *
     * @return void
     * @throws LocalizedException
     */
    #[
        DbIsolation(false),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, ['addresses' => [['postcode' => '12345'] ]], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddress::class, [
            'cart_id' => '$quote.id$',
            'address' => [
                'customer_id' => '$customer.id$',
                'save_in_address_book' => 1
            ]
        ]),
        DataFixture(SetShippingAddress::class, [
            'cart_id' => '$quote.id$',
            'address' => [
                'customer_id' => '$customer.id$',
                'save_in_address_book' => 1
            ]
        ]),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testReorderKeepsEditedShippingAddress(): void
    {
        $customer = $this->fixtures->get('customer');
        $order = $this->fixtures->get('order');
        $this->reorder($order, $customer->getEmail());

        $this->saveOrderCreateData(['shipping_address' => $this->postedShippingAddress('55555', '')], true);
        $this->saveOrderCreateData([], false);

        $shippingAddress = $this->getReorderQuote()->getShippingAddress();
        $this->assertEquals('55555', $shippingAddress->getPostcode());
        $this->assertEmpty($shippingAddress->getCustomerAddressId());
    }

    /**
     * An unchanged saved reorder shipping address still takes the latest address book data (ACP2E-1826)
     *
     * @return void
     * @throws LocalizedException
     */
    #[
        DbIsolation(false),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, ['addresses' => [['postcode' => '12345'] ]], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddress::class, [
            'cart_id' => '$quote.id$',
            'address' => [
                'customer_id' => '$customer.id$',
                'save_in_address_book' => 1
            ]
        ]),
        DataFixture(SetShippingAddress::class, [
            'cart_id' => '$quote.id$',
            'address' => [
                'customer_id' => '$customer.id$',
                'save_in_address_book' => 1
            ]
        ]),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order')
    ]
    public function testReorderSavedShippingAddressFollowsAddressBook(): void
    {
        $customer = $this->fixtures->get('customer');
        $order = $this->fixtures->get('order');
        $this->reorder($order, $customer->getEmail());
        $customerAddressId = (int)$order->getShippingAddress()->getCustomerAddressId();
        $this->updateCustomerAddress($customerAddressId, ['postcode' => '01234']);

        $this->saveOrderCreateData(
            ['shipping_address' => $this->postedShippingAddress('55555', (string)$customerAddressId)],
            true
        );
        $this->saveOrderCreateData([], false);

        $shippingAddress = $this->getReorderQuote()->getShippingAddress();
        $this->assertEquals('01234', $shippingAddress->getPostcode());
        $this->assertEquals($customerAddressId, (int)$shippingAddress->getCustomerAddressId());
    }

    /**
     * Load the order create session's quote from the database
     *
     * @return Quote
     */
    private function getReorderQuote(): Quote
    {
        $quoteId = (int)$this->_objectManager->get(QuoteSession::class)->getQuoteId();

        return $this->_objectManager->create(Quote::class)->load($quoteId);
    }

    /**
     * Shipping address fields as the order create form posts them
     *
     * @param string $postcode
     * @param string $customerAddressId the "Select from existing customer addresses" value
     * @return array
     */
    private function postedShippingAddress(string $postcode, string $customerAddressId): array
    {
        $address = $this->getReorderQuote()->getShippingAddress();

        return [
            'customer_address_id' => $customerAddressId,
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'country_id' => $address->getCountryId(),
            'region_id' => $address->getRegionId(),
            'postcode' => $postcode,
            'telephone' => $address->getTelephone(),
        ];
    }

    /**
     * Apply posted order data and save the quote, as an order create block reload does
     *
     * Getting shipping rates posts the address and collect_shipping_rates; loading the payment methods posts no
     * address.
     *
     * @param array $orderData the posted "order" fields
     * @param bool $collectShippingRates
     * @return void
     */
    private function saveOrderCreateData(array $orderData, bool $collectShippingRates): void
    {
        $orderCreate = $this->_objectManager->get(OrderCreate::class);
        if ($orderData) {
            $orderCreate->importPostData($orderData);
        }
        if ($collectShippingRates) {
            $orderCreate->collectShippingRates();
        }
        $orderCreate->saveQuote();
    }

    /**
     * Update customer address information
     *
     * @param int $addressId
     * @param array $updateData
     * @return void
     * @throws LocalizedException
     */
    private function updateCustomerAddress(int $addressId, array $updateData): void
    {
        $address = $this->addressRepository->getById($addressId);
        foreach ($updateData as $setFieldName => $setValue) {
            $address->setData($setFieldName, $setValue);
        }
        $this->addressRepository->save($address);
    }

    /**
     * Place reorder request
     *
     * @param OrderInterface $order
     * @param string $customerEmail
     * @return void
     */
    private function reorder(OrderInterface $order, string $customerEmail): void
    {
        $this->dispatchReorderRequest((int)$order->getId());
        $this->assertRedirect($this->stringContains('backend/sales/order_create'));
        $this->quote = $this->getQuote($customerEmail);
        $this->assertNotEmpty($this->quote);
    }
}
