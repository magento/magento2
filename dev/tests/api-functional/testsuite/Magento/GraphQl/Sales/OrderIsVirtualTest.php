<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * GraphQl tests for @see \Magento\SalesStorefrontCompatibilityGraphQl\Model\Resolver\OrderIsVirtual
 */
class OrderIsVirtualTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test graphql customer orders is not virtual
     *
     * @return void
     * @throws AuthenticationException|LocalizedException
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order'),
    ]
    public function testCustomerOrderIsNotVirtual(): void
    {
        $customerEmail = $this->fixtures->get('customer')->getEmail();
        $response = $this->graphQlQuery(
            $this->getCustomerOrdersQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders($customerEmail)
        );
        self::assertArrayHasKey('customerOrders', $response);
        self::assertArrayHasKey('items', $response['customerOrders']);
        self::assertCount(1, $response['customerOrders']['items']);
        self::assertFalse($response['customerOrders']['items'][0]['is_virtual']);
    }

    /**
     * Test graphql customer orders is virtual
     *
     * @return void
     * @throws AuthenticationException|LocalizedException
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, ['type_id' => 'virtual'], as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order'),
    ]
    public function testCustomerOrderIsVirtual(): void
    {
        $customerEmail = $this->fixtures->get('customer')->getEmail();
        $response = $this->graphQlQuery(
            $this->getCustomerOrdersQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders($customerEmail)
        );
        self::assertArrayHasKey('customerOrders', $response);
        self::assertArrayHasKey('items', $response['customerOrders']);
        self::assertCount(1, $response['customerOrders']['items']);
        self::assertTrue($response['customerOrders']['items'][0]['is_virtual']);
    }

    /**
     * Generate graphql query body for customer orders with 'is_virtual' field
     *
     * @return string
     */
    private function getCustomerOrdersQuery(): string
    {
        return <<<QUERY
query {
  customerOrders {
    items {
      is_virtual
    }
  }
}
QUERY;
    }

    /**
     * Returns the header with customer token for GQL Mutation
     *
     * @param string $email
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
