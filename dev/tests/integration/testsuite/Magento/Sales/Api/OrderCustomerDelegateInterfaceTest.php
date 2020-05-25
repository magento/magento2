<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Api;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\AttributeInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Sales\Api\OrderCustomerDelegateInterface class.
 *
 * @magentoAppIsolation enabled
 */
class OrderCustomerDelegateInterfaceTest extends TestCase
{
    /**
     * @var OrderCustomerDelegateInterface
     */
    private $delegate;

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
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->delegate = Bootstrap::getObjectManager()->get(
            OrderCustomerDelegateInterface::class
        );
        $this->orderRepository = Bootstrap::getObjectManager()->get(
            OrderRepositoryInterface::class
        );
        $this->customerFactory = Bootstrap::getObjectManager()->get(
            CustomerInterfaceFactory::class
        );
        $this->accountManagement = Bootstrap::getObjectManager()->get(
            AccountManagementInterface::class
        );
        $this->orderFactory = Bootstrap::getObjectManager()->get(
            OrderFactory::class
        );
    }

    /**
     * @param OrderAddressInterface $orderAddress
     * @param AddressInterface $address
     *
     * @return void
     */
    private function compareAddresses(
        OrderAddressInterface $orderAddress,
        AddressInterface $address
    ): void {
        $this->assertEquals(
            $orderAddress->getFirstname(),
            $address->getFirstname()
        );
        $this->assertEquals(
            $orderAddress->getLastname(),
            $address->getLastname()
        );
        $this->assertEquals(
            $orderAddress->getCompany(),
            $address->getCompany()
        );
        $this->assertEquals(
            $orderAddress->getStreet(),
            $address->getStreet()
        );
        $this->assertEquals(
            $orderAddress->getCity(),
            $address->getCity()
        );
        if (!$address->getRegionId()) {
            $this->assertEmpty($address->getRegionId());
        } else {
            $this->assertEquals(
                $orderAddress->getRegionId(),
                $address->getRegionId()
            );
        }
        $this->assertEquals(
            $orderAddress->getPostcode(),
            $address->getPostcode()
        );
        $this->assertEquals(
            $orderAddress->getCountryId(),
            $address->getCountryId()
        );
        $this->assertEquals(
            $orderAddress->getTelephone(),
            $address->getTelephone()
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_address.php
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @return void
     */
    public function testDelegateNew(): void
    {
        $orderAutoincrementId = '100000001';
        /** @var Order $orderModel */
        $orderModel = $this->orderFactory->create();
        $orderModel->loadByIncrementId($orderAutoincrementId);
        $orderId = (int)$orderModel->getId();
        unset($orderModel);

        $this->delegate->delegateNew($orderId);

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

        //Testing that addresses from order and the order itself are assigned
        //to customer.
        $order = $this->orderRepository->get($orderId);
        $this->assertCount(1, $createdCustomer->getAddresses());
        $this->assertNotNull($createdCustomer->getDefaultBilling());
        $this->assertNotNull($createdCustomer->getDefaultShipping());
        foreach ($createdCustomer->getAddresses() as $address) {
            $this->assertTrue(
                $address->isDefaultBilling() || $address->isDefaultShipping()
            );
            if ($address->isDefaultBilling()) {
                $this->compareAddresses($order->getBillingAddress(), $address);
            } elseif ($address->isDefaultShipping()) {
                $this->compareAddresses($order->getShippingAddress(), $address);
            }
        }
        $this->assertEquals($order->getCustomerId(), $createdCustomer->getId());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order_different_addresses.php
     * @return void
     */
    public function testDelegateNewDifferentAddresses(): void
    {
        $orderAutoincrementId = '100000001';
        /** @var Order $orderModel */
        $orderModel = $this->orderFactory->create();
        $orderModel->loadByIncrementId($orderAutoincrementId);
        $orderId = (int)$orderModel->getId();
        unset($orderModel);

        $this->delegate->delegateNew($orderId);

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

        //Testing that addresses from order and the order itself are assigned
        //to customer.
        $order = $this->orderRepository->get($orderId);
        $this->assertCount(2, $createdCustomer->getAddresses());
        $this->assertNotNull($createdCustomer->getDefaultBilling());
        $this->assertNotNull($createdCustomer->getDefaultShipping());
        foreach ($createdCustomer->getAddresses() as $address) {
            $this->assertTrue(
                $address->isDefaultBilling() || $address->isDefaultShipping()
            );
            if ($address->isDefaultBilling()) {
                $this->compareAddresses($order->getBillingAddress(), $address);
            } elseif ($address->isDefaultShipping()) {
                $this->compareAddresses($order->getShippingAddress(), $address);
            }
        }

        $this->assertEquals($order->getCustomerId(), $createdCustomer->getId());
    }
}
