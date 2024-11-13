<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Api;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Test GuestShippingInformationManagement API.
 */
class GuestShippingInformationManagementTest extends TestCase
{
    /**
     * @var GuestShippingInformationManagementInterface
     */
    private $management;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepo;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepo;

    /**
     * @var ShippingInformationInterfaceFactory
     */
    private $shippingFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteria;

    /**
     * @var QuoteIdMaskFactory
     */
    private $maskFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->management = $objectManager->get(GuestShippingInformationManagementInterface::class);
        $this->cartRepo = $objectManager->get(CartRepositoryInterface::class);
        $this->customerRepo = $objectManager->get(CustomerRepositoryInterface::class);
        $this->shippingFactory = $objectManager->get(ShippingInformationInterfaceFactory::class);
        $this->searchCriteria = $objectManager->get(SearchCriteriaBuilder::class);
        $this->maskFactory = $objectManager->get(QuoteIdMaskFactory::class);
    }

    /**
     * Test using another address for quote.
     *
     * @param bool $swapShipping Whether to swap shipping or billing addresses.
     * @return void
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer_with_addresses.php
     * @dataProvider getAddressesVariation
     */
    public function testDifferentAddresses(bool $swapShipping): void
    {
        $carts = $this->cartRepo->getList(
            $this->searchCriteria->addFilter('reserved_order_id', 'test01')->create()
        )->getItems();
        $cart = array_pop($carts);
        $otherCustomer = $this->customerRepo->get('customer_with_addresses@test.com');
        $otherAddresses = $otherCustomer->getAddresses();
        $otherAddress = array_pop($otherAddresses);

        //Setting invalid IDs.
        /** @var ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $cart->getExtensionAttributes()->getShippingAssignments()[0];
        $shippingAddress = $shippingAssignment->getShipping()->getAddress();
        $billingAddress = $cart->getBillingAddress();
        if ($swapShipping) {
            $address = $shippingAddress;
        } else {
            $address = $billingAddress;
        }
        $address->setCustomerAddressId($otherAddress->getId());
        $address->setCustomerId($otherCustomer->getId());
        $address->setId(null);
        /** @var ShippingInformationInterface $shippingInformation */
        $shippingInformation = $this->shippingFactory->create();
        $shippingInformation->setBillingAddress($billingAddress);
        $shippingInformation->setShippingAddress($shippingAddress);
        $shippingInformation->setShippingMethodCode('flatrate');
        /** @var QuoteIdMask $idMask */
        $idMask = $this->maskFactory->create();
        $idMask->load($cart->getId(), 'quote_id');

        $this->expectExceptionMessage(
            sprintf(
                'The shipping information was unable to be saved. Error: "Invalid customer address id %s"',
                $address->getCustomerAddressId()
            )
        );
        $this->expectException(InputException::class);
        $this->management->saveAddressInformation($idMask->getMaskedId(), $shippingInformation);
    }

    /**
     * Test save address information with customer custom address attribute for quote
     *
     * @return void
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Customer/_files/customer_address_with_custom_text_attribute.php
     */
    public function testSaveAddressInformationWithCustomerCustomAddressAttribute(): void
    {
        $carts = $this->cartRepo->getList(
            $this->searchCriteria->addFilter('reserved_order_id', 'test01')->create()
        )->getItems();
        $currentQuote = array_pop($carts);
        $guestCustomer = $this->customerRepo->get('JohnDoe@mail.com');

        $customerCustomAddressAttribute = $guestCustomer->getCustomAttributes();

        /** @var ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $currentQuote->getExtensionAttributes()->getShippingAssignments()[0];
        $shippingAddress = $shippingAssignment->getShipping()->getAddress();
        $billingAddress = $currentQuote->getBillingAddress();

        if ($customerCustomAddressAttribute) {
            $shippingAddress->setCustomAttributes($customerCustomAddressAttribute);
            $billingAddress->setCustomAttributes($customerCustomAddressAttribute);
        }

        /** @var ShippingInformationInterface $shippingInformation */
        $shippingInformation = $this->shippingFactory->create();
        $shippingInformation->setBillingAddress($billingAddress);
        $shippingInformation->setShippingAddress($shippingAddress);
        $shippingInformation->setShippingMethodCode('flatrate');
        $shippingInformation->setShippingCarrierCode('flatrate');
        /** @var QuoteIdMask $idMask */
        $idMask = $this->maskFactory->create();
        $idMask->load($currentQuote->getId(), 'quote_id');

        $paymentDetails = $this->management->saveAddressInformation($idMask->getMaskedId(), $shippingInformation);
        $this->assertNotEmpty($paymentDetails);
        $this->assertEquals($currentQuote->getGrandTotal(), $paymentDetails->getTotals()->getSubtotal());
    }

    /**
     * Different variations for addresses test.
     *
     * @return array
     */
    public static function getAddressesVariation(): array
    {
        return [
            'Shipping address swap' => [true],
            'Billing address swap' => [false]
        ];
    }
}
