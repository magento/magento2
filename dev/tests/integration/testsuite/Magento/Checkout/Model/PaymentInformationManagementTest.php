<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
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
 * Verifies that checkout rejects billing addresses belonging to other customers.
 * @suppressWarning(PHPMD.CouplingBetweenObjects)
 */
#[
    DataFixture(
        CustomerFixture::class,
        ['email' => 'owner_customer@example.com'],
        as: 'owner_customer'
    ),
    DataFixture(
        CustomerFixture::class,
        ['email' => 'other_customer@example.com', 'addresses' => [[]]],
        as: 'other_customer'
    ),
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(
        CustomerCartFixture::class,
        ['customer_id' => '$owner_customer.id$'],
        as: 'cart'
    ),
    DataFixture(
        AddProductToCartFixture::class,
        ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']
    ),
    DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(
        SetPaymentMethodFixture::class,
        ['cart_id' => '$cart.id$', 'method' => 'checkmo']
    )
]
class PaymentInformationManagementTest extends TestCase
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
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->paymentManagement = $objectManager->get(
            PaymentInformationManagementInterface::class
        );
        $this->addressFactory = $objectManager->get(
            AddressInterfaceFactory::class
        );
        $this->quoteRepository = $objectManager->get(
            CartRepositoryInterface::class
        );
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    public function testRejectsInvalidCustomerAddressIdInRequestPayload(): void
    {
        $cartId = (int)$this->fixtures->get('cart')->getId();
        $otherAddressId = $this->getFirstAddressIdFromFixtureCustomer(
            'other_customer'
        );

        $billingAddress = $this->addressFactory->create();
        $billingAddress->setCustomerAddressId($otherAddressId);

        $payment = $this->createPayment();

        try {
            $this->paymentManagement->savePaymentInformationAndPlaceOrder(
                $cartId,
                $payment,
                $billingAddress
            );
            $this->fail('NoSuchEntityException was expected.');
        } catch (NoSuchEntityException $exception) {
            $this->assertStringContainsString(
                'Invalid customer address id',
                $exception->getMessage()
            );
        }

        $this->assertTrue(
            (bool)$this->quoteRepository->get($cartId)->getIsActive(),
            'Quote should remain active after rejected payment.'
        );
    }

    private function createPayment(): PaymentInterface
    {
        $payment = Bootstrap::getObjectManager()->create(
            PaymentInterface::class
        );
        $payment->setMethod('checkmo');
        return $payment;
    }

    private function getFirstAddressIdFromFixtureCustomer(
        string $fixtureKey
    ): int {
        $customer = $this->fixtures->get($fixtureKey);
        $addresses = $customer->getAddresses();
        $address = reset($addresses);

        $this->assertNotFalse(
            $address,
            'Expected fixture customer to have at least one address.'
        );

        return (int)$address->getId();
    }
}
