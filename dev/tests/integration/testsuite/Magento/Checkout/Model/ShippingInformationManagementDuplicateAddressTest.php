<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressCollectionFactory;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks that checkout does not duplicate an address that the customer already has.
 *
 * @see https://github.com/magento/magento2/issues/32294
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ShippingInformationManagementDuplicateAddressTest extends TestCase
{
    private const CUSTOMER_ID = 1;

    /**
     * The single address created by the Magento/Customer/_files/customer_address.php fixture.
     */
    private const EXISTING_ADDRESS_ID = 1;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var CartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @var ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * @var PaymentInformationManagementInterface
     */
    private $paymentInformationManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var AddressCollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->cartManagement = $objectManager->create(CartManagementInterface::class);
        $this->cartItemRepository = $objectManager->create(CartItemRepositoryInterface::class);
        $this->shippingInformationManagement = $objectManager->create(ShippingInformationManagementInterface::class);
        $this->paymentInformationManagement = $objectManager->create(PaymentInformationManagementInterface::class);
        $this->customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
        $this->addressFactory = $objectManager->create(AddressInterfaceFactory::class);
        $this->addressCollectionFactory = $objectManager->get(AddressCollectionFactory::class);
        $this->orderRepository = $objectManager->create(OrderRepositoryInterface::class);
    }

    /**
     * An address re-entered through "Add new address" is linked to the stored one instead of duplicated.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Catalog/_files/product_virtual_in_stock.php
     * @magentoConfigFixture current_store carriers/flatrate/active 1
     * @magentoConfigFixture current_store payment/checkmo/active 1
     *
     * @return void
     */
    public function testAddressEqualToAnExistingOneIsNotDuplicated(): void
    {
        $this->assertSame(1, $this->countCustomerAddresses(), 'The fixture has to provide one address.');

        $quoteId = $this->createCartWithItem();
        $order = $this->placeOrder($quoteId, $this->createAddressEqualToTheStoredOne());

        $this->assertSame(
            1,
            $this->countCustomerAddresses(),
            'Re-entering a stored address must not add another entry to the address book.'
        );
        // The fixture product is virtual, so a virtual order carries no shipping address; the same
        // address object was submitted as both billing and shipping, so billing is checked instead.
        $this->assertEquals(
            self::EXISTING_ADDRESS_ID,
            $order->getBillingAddress()->getCustomerAddressId(),
            'The order has to reference the address that already existed.'
        );
    }

    /**
     * A genuinely new address is still added to the address book.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Catalog/_files/product_virtual_in_stock.php
     * @magentoConfigFixture current_store carriers/flatrate/active 1
     * @magentoConfigFixture current_store payment/checkmo/active 1
     *
     * @return void
     */
    public function testDifferentAddressIsStillAddedToTheAddressBook(): void
    {
        $this->assertSame(1, $this->countCustomerAddresses(), 'The fixture has to provide one address.');

        $address = $this->createAddressEqualToTheStoredOne();
        $address->setStreet(['Blue str, 12']);

        $quoteId = $this->createCartWithItem();
        $this->placeOrder($quoteId, $address);

        $this->assertSame(
            2,
            $this->countCustomerAddresses(),
            'An address that differs from the stored one still has to be saved.'
        );
    }

    /**
     * Build the address the customer types into the "Add new address" form.
     *
     * The values are the ones of the Magento/Customer/_files/customer_address.php fixture, written the
     * way a customer would type them a second time: different case, padded street, extra empty line.
     *
     * @return AddressInterface
     */
    private function createAddressEqualToTheStoredOne(): AddressInterface
    {
        $customer = $this->customerRepository->getById(self::CUSTOMER_ID);

        /** @var AddressInterface $address */
        $address = $this->addressFactory->create();
        $address->setFirstname('john')
            ->setLastname('SMITH')
            ->setCompany('CompanyName')
            ->setStreet([' Green str, 67 ', ''])
            ->setCity('CityM')
            ->setRegionId(1)
            ->setCountryId('US')
            ->setPostcode('75477')
            ->setTelephone('3468676')
            ->setEmail($customer->getEmail())
            ->setSaveInAddressBook(1);

        return $address;
    }

    /**
     * Create a cart for the fixture customer and put a shippable product into it.
     *
     * @return int
     */
    private function createCartWithItem(): int
    {
        $quoteId = (int)$this->cartManagement->createEmptyCartForCustomer(self::CUSTOMER_ID);

        /** @var CartItemInterface $cartItem */
        $cartItem = Bootstrap::getObjectManager()->create(CartItemInterface::class);
        $cartItem->setSku('virtual-product')
            ->setQty(1)
            ->setQuoteId($quoteId);
        $this->cartItemRepository->save($cartItem);

        return $quoteId;
    }

    /**
     * Submit the address and place the order, which is where the address book is written.
     *
     * @param int $quoteId
     * @param AddressInterface $shippingAddress
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    private function placeOrder(int $quoteId, AddressInterface $shippingAddress)
    {
        /** @var ShippingInformationInterface $addressInformation */
        $addressInformation = Bootstrap::getObjectManager()->create(ShippingInformationInterface::class);
        $addressInformation->setShippingAddress($shippingAddress)
            ->setBillingAddress($shippingAddress)
            ->setShippingCarrierCode('flatrate')
            ->setShippingMethodCode('flatrate');

        $this->shippingInformationManagement->saveAddressInformation($quoteId, $addressInformation);

        /** @var PaymentInterface $payment */
        $payment = Bootstrap::getObjectManager()->create(PaymentInterface::class);
        $payment->setMethod('checkmo');
        $orderId = $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder($quoteId, $payment);

        return $this->orderRepository->get((int)$orderId);
    }

    /**
     * Count the rows the customer actually has in customer_address_entity.
     *
     * A fresh collection is used on purpose so that the assertion reads the database rather than the
     * address registry, which still holds the state from before the order was placed.
     *
     * @return int
     */
    private function countCustomerAddresses(): int
    {
        return (int)$this->addressCollectionFactory->create()
            ->addAttributeToFilter('parent_id', self::CUSTOMER_ID)
            ->getSize();
    }
}
