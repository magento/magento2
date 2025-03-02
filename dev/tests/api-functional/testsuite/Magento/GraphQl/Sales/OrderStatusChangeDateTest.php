<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Test\Fixture\Store;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;

/**
 * Test coverage for order_status_change_date for type CustomerOrder
 */
#[
    DataFixture(Store::class),
    DataFixture(ProductFixture::class, as: 'product'),
    DataFixture(GuestCart::class, as: 'cart'),
    DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
    DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
]
class OrderStatusChangeDateTest extends GraphQlAbstract
{
    /**
     * Order status mapper
     */
    private const STATUS_MAPPER = [
        Order::STATE_HOLDED => 'On Hold',
        Order::STATE_CANCELED => 'Canceled'
    ];

    public function testOrderStatusChangeDateWithStatusChange(): void
    {
        /**
         * @var $order OrderInterface
         */
        $order = DataFixtureStorageManager::getStorage()->get('order');

        $this->assertOrderStatusChangeDate($order, Order::STATE_HOLDED);
        $this->assertOrderStatusChangeDate($order, Order::STATE_CANCELED);
    }

    /**
     * Assert order_status_change_date after setting the status
     *
     * @param OrderInterface $order
     * @param string $status
     * @return void
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function assertOrderStatusChangeDate(OrderInterface $order, string $status): void
    {
        //Update order status
        $order->setStatus($status);
        $order->setState($status);
        Bootstrap::getObjectManager()->get(OrderRepository::class)->save($order);

        $updatedGuestOrder = $this->graphQlMutation($this->getQuery(
            $order->getIncrementId(),
            $order->getBillingAddress()->getEmail(),
            $order->getBillingAddress()->getLastname()
        ));
        self::assertEquals(
            self::STATUS_MAPPER[$status],
            $updatedGuestOrder['guestOrder']['status']
        );
        self::assertEquals(
            $order->getUpdatedAt(),
            $updatedGuestOrder['guestOrder']['order_status_change_date']
        );
    }

    /**
     * Generates guestOrder query with order_status_change_date
     *
     * @param string $number
     * @param string $email
     * @param string $lastname
     * @return string
     */
    private function getQuery(string $number, string $email, string $lastname): string
    {
        return <<<QUERY
            {
              guestOrder(input: {
                number: "{$number}",
                email: "{$email}",
                lastname: "{$lastname}"
              }) {
                created_at
                status
                order_status_change_date
              }
            }
        QUERY;
    }
}
