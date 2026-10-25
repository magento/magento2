<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test to verify order shipping address does not have company name
 * when "Show Company" configuration is set to "No"
 *
 * Test Steps:
 * 1. Set Show Company to Optional, create customer with addresses containing company names
 * 2. Set Show Company to No
 * 3. Place order with new shipping address
 * 4. Verify order shipping address does not contain company name in sales_order_address table
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingAddressWithoutCompanyTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private AddressRepositoryInterface $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private AddressInterfaceFactory $addressFactory;

    /**
     * @var QuoteFactory
     */
    private QuoteFactory $quoteFactory;

    /**
     * @var CartManagementInterface
     */
    private CartManagementInterface $cartManagement;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var CustomerRegistry
     */
    private CustomerRegistry $customerRegistry;

    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var WriterInterface
     */
    private WriterInterface $configWriter;

    /**
     * @var ReinitableConfigInterface
     */
    private ReinitableConfigInterface $reinitableConfig;

    /**
     * @var int|null
     */
    private ?int $createdCustomerId = null;

    /**
     * @var string|null
     */
    private ?string $originalShowCompanyConfig = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->addressRepository = $objectManager->get(AddressRepositoryInterface::class);
        $this->addressFactory = $objectManager->get(AddressInterfaceFactory::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->cartManagement = $objectManager->get(CartManagementInterface::class);
        $this->cartRepository = $objectManager->get(CartRepositoryInterface::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
        $this->customerRegistry = $objectManager->get(CustomerRegistry::class);
        $this->registry = $objectManager->get(Registry::class);
        $this->configWriter = $objectManager->get(WriterInterface::class);
        $this->reinitableConfig = $objectManager->get(ReinitableConfigInterface::class);
    }

    /**
     * Test that order shipping address does not contain company name
     * when "Show Company" is set to "No"
     *
     * Test Steps:
     * 1. Create customer with existing addresses (with company names) when Show Company is Optional
     * 2. Change Show Company config to No
     * 3. Place order with new shipping address during checkout
     * 4. Verify order shipping address in sales_order_address table has no company
     * 5. Verify existing customer addresses still have company names (unchanged)
     *
     * @return void
     */
    #[
        Config('customer/address/company_show', 'opt'),
        DataFixture('Magento\Catalog\Test\Fixture\Product', as: 'product'),
    ]
    public function testNewShippingAddressDoesNotHaveCompanyWhenShowCompanyIsNo(): void
    {
        // Step 1: Create customer with addresses containing company names (Show Company = Optional)
        $customer = $this->createCustomerWithAddresses();
        $customerId = $customer->getId();
        $this->createdCustomerId = (int) $customerId; // Store for cleanup

        // Step 2: Verify initial addresses have company names
        $initialAddresses = $this->getCustomerAddressesFromDatabase((int) $customerId);
        $this->assertNotEmpty($initialAddresses, 'Customer should have initial addresses');
        foreach ($initialAddresses as $address) {
            $this->assertNotEmpty(
                $address['company'],
                'Initial addresses should have company names when Show Company is Optional'
            );
        }

        // Get current config value before changing it (for cleanup)
        $this->originalShowCompanyConfig = $this->getShowCompanyConfig();

        // Step 3: Change Show Company configuration to No
        $this->setShowCompanyConfig('0');

        // Step 4: Place order with NEW shipping address
        $product = $this->fixtures->get('product');
        $order = $this->placeOrderWithNewShippingAddress($customer, $product);

        // Step 5: Get the order shipping address from sales_order_address
        $orderShippingAddress = $order->getShippingAddress();
        $this->assertNotNull($orderShippingAddress, 'Order should have a shipping address');

        // Step 6: Verify order shipping address has NO company (via API)
        $this->assertEmpty(
            $orderShippingAddress->getCompany(),
            'Order shipping address should NOT have company name when Show Company is No'
        );

        // Step 7: Verify in sales_order_address table via direct database query
        $salesOrderAddressData = $this->getAddressFromSalesOrderTable((int) $order->getId(), 'shipping');
        $this->assertNotNull($salesOrderAddressData, 'Shipping address should exist in sales_order_address');
        $this->assertEmpty(
            $salesOrderAddressData['company'],
            'Shipping address in sales_order_address table should NOT have company name when Show Company is No'
        );

        // Step 8: Verify order billing address still has company (from existing customer address)
        $orderBillingAddress = $order->getBillingAddress();
        $this->assertNotEmpty(
            $orderBillingAddress->getCompany(),
            'Billing address should retain company name from existing customer address'
        );

        // Step 9: Verify existing customer addresses still have company (unchanged)
        $allAddressesAfterOrder = $this->getCustomerAddressesFromDatabase((int) $customerId);
        $this->assertNotEmpty($allAddressesAfterOrder, 'Customer addresses should still exist');
        foreach ($allAddressesAfterOrder as $address) {
            $this->assertNotEmpty(
                $address['company'],
                'Existing customer addresses should retain their company names'
            );
        }
    }

    /**
     * Create customer with shipping and billing addresses containing company names
     *
     * @return CustomerInterface
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    private function createCustomerWithAddresses(): CustomerInterface
    {
        // Create customer
        $objectManager = Bootstrap::getObjectManager();
        $customerFactory = $objectManager->get(CustomerInterfaceFactory::class);

        $customer = $customerFactory->create();
        $customer->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('customer_' . uniqid() . '@example.com')
            ->setWebsiteId(1);
        $customer = $this->customerRepository->save($customer);

        // Create shipping address with company
        $shippingAddress = $this->addressFactory->create();
        $shippingAddress->setCustomerId($customer->getId())
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setCompany('Shipping Company Inc.') // Company name set
            ->setStreet(['123 Shipping St'])
            ->setCity('Los Angeles')
            ->setRegionId(12) // California
            ->setPostcode('90001')
            ->setCountryId('US')
            ->setTelephone('555-1234')
            ->setIsDefaultShipping(true);
        $this->addressRepository->save($shippingAddress);

        // Create billing address with company
        $billingAddress = $this->addressFactory->create();
        $billingAddress->setCustomerId($customer->getId())
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setCompany('Billing Company LLC') // Company name set
            ->setStreet(['456 Billing Ave'])
            ->setCity('San Francisco')
            ->setRegionId(12) // California
            ->setPostcode('94102')
            ->setCountryId('US')
            ->setTelephone('555-5678')
            ->setIsDefaultBilling(true);
        $this->addressRepository->save($billingAddress);

        return $customer;
    }

    /**
     * Place order with a NEW shipping address (not existing customer address)
     *
     * @param CustomerInterface $customer
     * @param mixed $product
     * @return OrderInterface
     */
    private function placeOrderWithNewShippingAddress(CustomerInterface $customer, $product): OrderInterface
    {
        // Create quote/cart
        $quote = $this->quoteFactory->create();
        $quote->setStoreId(1)
            ->setCustomer($customer)
            ->setCustomerIsGuest(false);

        // Add product to cart
        $quote->addProduct($product, 1);
        $quote->collectTotals();

        // Set NEW shipping address (different from existing addresses, NO company)
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCustomerId($customer->getId())
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setCompany('') // NO company - this is the new address being tested
            ->setStreet(['789 New Shipping Blvd']) // Different street
            ->setCity('San Diego') // Different city
            ->setRegionId(12) // California
            ->setPostcode('92101') // Different postcode
            ->setCountryId('US')
            ->setTelephone('555-9999')
            ->setSaveInAddressBook(true) // Save as new customer address
            ->setCollectShippingRates(true)
            ->setShippingMethod('flatrate_flatrate');

        // Set billing address (use existing)
        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setCustomerId($customer->getId())
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setCompany('Billing Company LLC')
            ->setStreet(['456 Billing Ave'])
            ->setCity('San Francisco')
            ->setRegionId(12)
            ->setPostcode('94102')
            ->setCountryId('US')
            ->setTelephone('555-5678');

        // Set payment method
        $quote->getPayment()->setMethod('checkmo');

        // Collect totals and save quote
        $quote->collectTotals();
        $this->cartRepository->save($quote);

        // Place order
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        return $this->orderRepository->get($orderId);
    }

    /**
     * Get Show Company configuration value
     *
     * @return string|null Current config value
     */
    private function getShowCompanyConfig(): ?string
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('core_config_data');

        $select = $connection->select()
            ->from($tableName, ['value'])
            ->where('path = ?', 'customer/address/company_show')
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0);

        $result = $connection->fetchOne($select);
        return $result !== false ? $result : null;
    }

    /**
     * Set Show Company configuration
     *
     * @param string $value '0' = No, 'opt' = Optional, 'req' = Required
     * @return void
     */
    private function setShowCompanyConfig(string $value): void
    {
        $this->configWriter->save('customer/address/company_show', $value);

        // Reinitialize config
        $this->reinitableConfig->reinit();
    }

    /**
     * Get customer addresses from database
     *
     * @param int $customerId
     * @return array
     */
    private function getCustomerAddressesFromDatabase(int $customerId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('customer_address_entity');

        $select = $connection->select()
            ->from($tableName)
            ->where('parent_id = ?', $customerId);

        return $connection->fetchAll($select);
    }

    /**
     * Get address from sales_order_address table
     *
     * @param int $orderId
     * @param string $addressType 'shipping' or 'billing'
     * @return array|null
     */
    private function getAddressFromSalesOrderTable(int $orderId, string $addressType): ?array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order_address');

        $select = $connection->select()
            ->from($tableName)
            ->where('parent_id = ?', $orderId)
            ->where('address_type = ?', $addressType);

        $result = $connection->fetchRow($select);
        return $result ?: null;
    }

    /**
     * Restore Show Company configuration to original value
     *
     * @return void
     */
    private function restoreShowCompanyConfig(): void
    {
        if (!isset($this->configWriter, $this->reinitableConfig)) {
            return;
        }

        try {
            if ($this->originalShowCompanyConfig !== null) {
                $this->configWriter->save('customer/address/company_show', $this->originalShowCompanyConfig);
            } else {
                // If original config was null, delete the config entry (restore to default)
                $this->configWriter->delete('customer/address/company_show');
            }
            $this->reinitableConfig->reinit();
        } catch (Exception $e) {
            // Continue cleanup even if config restore fails
        }
    }

    /**
     * Delete customer created during test
     *
     * @return void
     */
    private function deleteCreatedCustomer(): void
    {
        if ($this->createdCustomerId === null || !isset($this->customerRepository, $this->registry)) {
            return;
        }

        try {
            // Set secure area for deletion
            $isSecureArea = $this->registry->registry('isSecureArea');
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', true);

            // Delete customer and all associated data (addresses, orders, etc.)
            try {
                $customer = $this->customerRepository->getById($this->createdCustomerId);
                $this->customerRepository->delete($customer);
            } catch (NoSuchEntityException $e) {
                // Customer already deleted
            }

            // Restore secure area flag
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', $isSecureArea);
        } catch (Exception $e) {
            // Continue cleanup even if customer deletion fails
        }
    }

    /**
     * Clean up customer registry cache
     *
     * @return void
     */
    private function cleanupCustomerRegistry(): void
    {
        if (!isset($this->customerRegistry) || $this->createdCustomerId === null) {
            return;
        }

        try {
            $this->customerRegistry->remove($this->createdCustomerId);
        } catch (Exception $e) {
            // Continue cleanup
        }
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->restoreShowCompanyConfig();
        $this->deleteCreatedCustomer();
        $this->cleanupCustomerRegistry();

        parent::tearDown();
    }
}
