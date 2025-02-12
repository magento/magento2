<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

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
class OrderAvailableActionTest extends GraphQlAbstract
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
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @inheridoc
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();

        $this->customerTokenService = $this->objectManager->get(CustomerTokenServiceInterface::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->orderRepository = $this->objectManager->get(OrderRepository::class);
    }

    #[
        Config('sales/cancellation/enabled', 1)
    ]
    /**
     * @dataProvider orderStatusProvider
     */
    public function testCustomerOrderAvailableActions($status, $expectedResult): void
    {
        $customerEmail = $this->fixtures->get('customer')->getEmail();
        /**
         * @var $order OrderInterface
         */
        $order = $this->fixtures->get('order');

        if ($status != 'pending') {
            $order->setStatus($status);
            $order->setState($status);
            $this->orderRepository->save($order);
        }

        $response = $this->graphQlQuery(
            $this->getCustomerOrdersQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders($customerEmail)
        );
        $result = $response['customerOrders']['items'][0]['available_actions'];

        foreach ($expectedResult as $action) {
            $this->assertContainsEquals($action, $result);
        }
    }

    #[
        Config('sales/cancellation/enabled', 0)
    ]
    public function testCustomerOrderActionWithDisabledOrderCancellation(): void
    {
        $customerEmail = $this->fixtures->get('customer')->getEmail();

        $response = $this->graphQlQuery(
            $this->getCustomerOrdersQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders($customerEmail)
        );
        $result = $response['customerOrders']['items'][0]['available_actions'];

        $this->assertEquals(['REORDER'], $result);
    }

    /**
     * @throws AuthenticationException
     */
    #[
        Config('sales/cancellation/enabled', 1),
        DataFixture(ProductFixture::class, as: 'product1'),
        DataFixture(ProductFixture::class, as: 'product2'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$quote.id$', 'product_id' => '$product1.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$quote.id$', 'product_id' => '$product2.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(
            ShipmentFixture::class,
            [
                'order_id' => '$order.id$',
                'items' => [['product_id' => '$product1.id$', 'qty' => 1]]
            ]
        )
    ]
    public function testCustomerOrderActionWithOrderPartialShipment(): void
    {
        $response = $this->graphQlQuery(
            $this->getCustomerOrdersQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
        );

        $this->assertEquals(['REORDER'], $response['customerOrders']['items'][0]['available_actions']);
    }

    /**
     * Generate graphql query body for customer orders
     *
     * @return string
     */
    private function getCustomerOrdersQuery(): string
    {
        return <<<QUERY
query {
  customerOrders {
    items {
      available_actions
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

    /**
     * @return array[]
     */
    public static function orderStatusProvider(): array
    {
        return [
            'pending status' => [
                'pending',
                ['CANCEL', 'REORDER']
            ],
            'On Hold status' => [
                Order::STATE_HOLDED,
                []
            ],
            'Canceled status' => [
                Order::STATE_CANCELED,
                ['REORDER']
            ],
            'Closed status' => [
                Order::STATE_CLOSED,
                ['REORDER']
            ],
            'Complete status' => [
                Order::STATE_COMPLETE,
                ['REORDER']
            ]
        ];
    }
}
