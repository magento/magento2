<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Sales\Api\InvoiceOrderInterface;

/**
 * Shipping information managment test.
 */
class ShippingInformationManagementTest extends \PHPUnit\Framework\TestCase
{
    /** @var CartManagementInterface */
    private $cartManagement;

    /** @var CartItemRepositoryInterface */
    private $cartItemRepository;

    /** @var CartItemInterface */
    private $cartItem;

    /** @var ShippingInformationManagementInterface */
    private $shippingInformationManagement;

    /** @var ShippingInformationInterface */
    private $shippingInformation;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var AddressInterfaceFactory */
    private $apiAddressFactory;

    /** @var ShipmentEstimationInterface */
    private $shipmentEstimation;

    /** @var PaymentInformationManagementInterface */
    private $paymentInformationManagement;

    /** @var PaymentInterface */
    private $payment;

    /** @var InvoiceOrderInterface */
    private $invoiceOrder;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->cartManagement = $objectManager->create(CartManagementInterface::class);
        $this->cartItemRepository = $objectManager->create(CartItemRepositoryInterface::class);
        $this->cartItem = $objectManager->create(CartItemInterface::class);
        $this->shippingInformationManagement = $objectManager->create(ShippingInformationManagementInterface::class);
        $this->shippingInformation = $objectManager->create(ShippingInformationInterface::class);
        $this->customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
        $this->apiAddressFactory = $objectManager->create(AddressInterfaceFactory::class);
        $this->shipmentEstimation = $objectManager->create(ShipmentEstimationInterface::class);
        $this->paymentInformationManagement = $objectManager->create(PaymentInformationManagementInterface::class);
        $this->payment = $objectManager->create(PaymentInterface::class);
        $this->invoiceOrder = $objectManager->create(InvoiceOrderInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Catalog/_files/product_virtual_in_stock.php
     */
    public function testQuoteApiWithOnlyVirtualProducts()
    {
        $customer = $this->customerRepository->getById(1);

        // Create empty quote
        $quoteId = $this->cartManagement->createEmptyCartForCustomer($customer->getId());

        $cartItem = $this->cartItem
            ->setSku('virtual-product')
            ->setQty(1)
            ->setQuoteId($quoteId);

        // Add item to cart
        $this->cartItemRepository->save($cartItem);

        $billingAddress = $shippingAddress = null;
        foreach ($customer->getAddresses() as $address) {
            $billingAddress = $address;
            $shippingAddress = $address;
            break;
        }

        /** @var \Magento\Quote\Model\Quote\Address $apiBillingAddress */
        $apiBillingAddress = $this->apiAddressFactory->create();
        $apiBillingAddress->setRegion($billingAddress->getRegion())
            ->setRegionId($billingAddress->getRegionId())
            ->setCountryId($billingAddress->getCountryId())
            ->setStreet($billingAddress->getStreet())
            ->setPostcode($billingAddress->getPostcode())
            ->setCity($billingAddress->getCity())
            ->setFirstname($billingAddress->getFirstname())
            ->setLastname($billingAddress->getLastname())
            ->setEmail($customer->getEmail())
            ->setTelephone($billingAddress->getTelephone());

        /** @var \Magento\Quote\Model\Quote\Address $apiShippingAddress */
        $apiShippingAddress = $this->apiAddressFactory->create();
        $apiShippingAddress->setRegion($shippingAddress->getRegion())
            ->setRegionId($shippingAddress->getRegionId())
            ->setCountryId($shippingAddress->getCountryId())
            ->setStreet($shippingAddress->getStreet())
            ->setPostcode($shippingAddress->getPostcode())
            ->setCity($shippingAddress->getCity())
            ->setFirstname($shippingAddress->getFirstname())
            ->setLastname($shippingAddress->getLastname())
            ->setEmail($customer->getEmail())
            ->setTelephone($shippingAddress->getTelephone());

        // Estimate shipping
        $this->shipmentEstimation->estimateByExtendedAddress($quoteId, $apiShippingAddress);

        $addressInformation = $this->shippingInformation
            ->setBillingAddress($apiBillingAddress)
            ->setShippingAddress($apiShippingAddress)
            ->setShippingCarrierCode('flatrate')
            ->setShippingMethodCode('flatrate');

        // Set address information on quote
        $this->shippingInformationManagement->saveAddressInformation($quoteId, $addressInformation);
    }
}
