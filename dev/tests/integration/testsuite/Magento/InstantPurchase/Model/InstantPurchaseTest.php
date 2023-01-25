<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 */
class InstantPurchaseTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     */
    public function testAvailableWhenEverythingSetUp()
    {
        $option = $this->invokeTestInstantPurchaseOptionCalculation();

        $this->assertTrue($option->isAvailable());
        $this->assertInstanceOf(PaymentTokenInterface::class, $option->getPaymentToken());
        $this->assertInstanceOf(Address::class, $option->getShippingAddress());
        $this->assertInstanceOf(Address::class, $option->getBillingAddress());
        $this->assertInstanceOf(ShippingMethodInterface::class, $option->getShippingMethod());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testNotAvailableWithoutPaymentToken()
    {
        $option = $this->invokeTestInstantPurchaseOptionCalculation();
        $this->assertFalse($option->isAvailable());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     * @magentoConfigFixture current_store payment/fake_vault/active 0
     */
    public function testNotAvailableWhenVaultNotActive()
    {
        $option = $this->invokeTestInstantPurchaseOptionCalculation();
        $this->assertFalse($option->isAvailable());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     * @magentoConfigFixture current_store payment/fake/active 0
     */
    public function testNotAvailableWhenVaultProviderNotActive()
    {
        $option = $this->invokeTestInstantPurchaseOptionCalculation();
        $this->assertFalse($option->isAvailable());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     */
    public function testNotAvailableWithoutAddresses()
    {
        $option = $this->invokeTestInstantPurchaseOptionCalculation();
        $this->assertFalse($option->isAvailable());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     * @magentoConfigFixture current_store carriers/flatrate/active 0
     */
    public function testNotAvailableWhenShippingMethodsDisabled()
    {
        $option = $this->invokeTestInstantPurchaseOptionCalculation();
        $this->assertFalse($option->isAvailable());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     * @magentoConfigFixture current_store sales/instant_purchase/active 0
     */
    public function testNotAvailableWhenDisabledInConfig()
    {
        $option = $this->invokeTestInstantPurchaseOptionCalculation();
        $this->assertFalse($option->isAvailable());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/InstantPurchase/_files/fake_payment_token.php
     * @magentoConfigFixture current_store payment/fake_vault/instant_purchase/supported 0
     */
    public function testNotAvailableWhenSupportSwitchedOffForVault()
    {
        $option = $this->invokeTestInstantPurchaseOptionCalculation();
        $this->assertFalse($option->isAvailable());
    }

    /**
     * Run system under test
     *
     * @return InstantPurchaseOption
     */
    private function invokeTestInstantPurchaseOptionCalculation(): InstantPurchaseOption
    {
        /** @var InstantPurchaseInterface $instantPurchase */
        $instantPurchase = $this->objectManager->create(InstantPurchaseInterface::class);
        $store = $this->getFixtureStore();
        $customer = $this->getFixtureCustomer();
        $option = $instantPurchase->getOption($store, $customer);
        return $option;
    }

    /**
     * Returns Store created by fixture.
     *
     * @return Store
     */
    private function getFixtureStore(): Store
    {
        $repository = $this->objectManager->create(StoreRepositoryInterface::class);
        $store = $repository->get('default');
        return $store;
    }

    /**
     * Returns Customer created by fixture.
     *
     * @return Customer
     */
    private function getFixtureCustomer(): Customer
    {
        $repository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $customerData = $repository->getById(1);
        $customer = $this->objectManager->create(Customer::class);
        $customer->updateData($customerData);
        return $customer;
    }
}
