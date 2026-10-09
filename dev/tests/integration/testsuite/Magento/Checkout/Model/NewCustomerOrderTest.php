<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration coverage for the checkout order-placement flow of a customer with no address on file.
 *
 * Each test guards an invariant that must hold when such a customer completes checkout, e.g. how the checkout
 * address is persisted to their address book.
 *
 * @suppressWarning(PHPMD.CouplingBetweenObjects)
 */
#[
    DataFixture(CustomerFixture::class, ['email' => 'new_customer@example.com'], as: 'customer'),
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
    DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
    DataFixture(
        SetShippingAddressFixture::class,
        ['cart_id' => '$cart.id$', 'address' => ['save_in_address_book' => 1]]
    ),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$'])
]
class NewCustomerOrderTest extends TestCase
{
    /**
     * @var PaymentInformationManagementInterface
     */
    private PaymentInformationManagementInterface $paymentManagement;

    /**
     * @var AddressInterfaceFactory
     */
    private AddressInterfaceFactory $addressFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private AddressRepositoryInterface $customerAddressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->paymentManagement = $objectManager->get(PaymentInformationManagementInterface::class);
        $this->addressFactory = $objectManager->get(AddressInterfaceFactory::class);
        $this->customerAddressRepository = $objectManager->get(AddressRepositoryInterface::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * A new customer's first checkout with a single saved address must store it once (no duplicate on the next
     * checkout) and mark it default for both billing and shipping.
     *
     * @return void
     */
    public function testFirstOrderStoresSingleDefaultAddress(): void
    {
        $cartId = (int)$this->fixtures->get('cart')->getId();
        $customerId = (int)$this->fixtures->get('customer')->getId();

        $payment = Bootstrap::getObjectManager()->create(PaymentInterface::class);
        $payment->setMethod('checkmo');

        $orderId = $this->paymentManagement->savePaymentInformationAndPlaceOrder(
            $cartId,
            $payment,
            $this->createBillingAddress()
        );

        $this->assertGreaterThan(0, (int)$orderId, 'The first order must be placed successfully.');

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('parent_id', $customerId)
            ->create();
        $customerAddresses = $this->customerAddressRepository->getList($searchCriteria)->getItems();

        $this->assertCount(
            1,
            $customerAddresses,
            'A new customer must have a single address stored after their first successful order.'
        );

        $addressId = (int)reset($customerAddresses)->getId();
        $customer = $this->customerRepository->getById($customerId);
        $this->assertEquals(
            $addressId,
            (int)$customer->getDefaultShipping(),
            'The single stored address must be the default shipping address.'
        );
        $this->assertEquals(
            $addressId,
            (int)$customer->getDefaultBilling(),
            'The single stored address must be the default billing address.'
        );
    }

    /**
     * Build the storefront billing address matching the shipping fixture, flagged to save in the address book.
     *
     * @return AddressInterface
     */
    private function createBillingAddress(): AddressInterface
    {
        /** @var AddressInterface $billingAddress */
        $billingAddress = $this->addressFactory->create();
        $billingAddress->setFirstname('John');
        $billingAddress->setLastname('Doe');
        $billingAddress->setCompany('Magento');
        $billingAddress->setStreet(['Green str, 67']);
        $billingAddress->setCity('Montgomery');
        $billingAddress->setRegionId(1);
        $billingAddress->setPostcode('36104');
        $billingAddress->setCountryId('US');
        $billingAddress->setTelephone('3340000000');
        $billingAddress->setSaveInAddressBook(1);
        return $billingAddress;
    }
}
