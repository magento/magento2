<?php
/**
 * Copyright 2025 Adobe
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
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for customer.orders.items.comments
 */
class OrderDetailsWithCommentsTest extends GraphQlAbstract
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
     * @return void
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test customerOrders query with order comments
     *
     * @throws AuthenticationException
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
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], as: 'order'),
        Config('sales/cancellation/enabled', 1)
    ]
    public function testOrderCustomerComments(): void
    {
        $customerAuthHeaders = $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail());

        // cancel order
        $this->graphQlMutation(
            $this->getCancelOrderMutation($this->fixtures->get('order')->getEntityId()),
            [],
            '',
            $customerAuthHeaders
        );

        // fetch order comments
        $response = $this->graphQlQuery(
            $this->getCustomerOrdersQuery(),
            [],
            '',
            $customerAuthHeaders
        );

        // validate order comments
        $this->assertEquals(
            $this->getOrderComments(),
            $response['customer']['orders']['items'][0]['comments']
        );
    }

    /**
     * To get order comments
     *
     * @return array
     * @throws LocalizedException
     */
    private function getOrderComments(): array
    {
        $comments = [];
        foreach ($this->fixtures->get('order')->getStatusHistories() as $comment) {
            if ($comment->getIsVisibleOnFront()) {
                $comments[] = [
                    'message' => $comment->getComment(),
                    'timestamp' => $comment->getCreatedAt()
                ];
            }
        }
        return $comments;
    }

    /**
     * Get cancel order mutation
     *
     * @param string $orderId
     * @return string
     */
    private function getCancelOrderMutation(string $orderId): string
    {
        return <<<MUTATION
            mutation {
                cancelOrder(input: {
                    order_id: "{$orderId}",
                    reason: "Cancel sample reason"
                }) {
                    errorV2 {
                        message
                    }
                    order {
                        status
                    }
                }
            }
        MUTATION;
    }

    /**
     * Get Customer Orders query with comments
     *
     * @return string
     */
    private function getCustomerOrdersQuery(): string
    {
        return <<<QUERY
            {
                customer {
                    orders(pageSize: 10) {
                        items {
                            comments {
                                message
                                timestamp
                            }
                        }
                    }
                }
            }
        QUERY;
    }

    /**
     * Get Customer Auth Headers
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
