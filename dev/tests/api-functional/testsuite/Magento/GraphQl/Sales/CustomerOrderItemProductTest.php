<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CustomerOrderItemProductTest extends GraphQlAbstract
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
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->stockRegistry = Bootstrap::getObjectManager()->get(StockRegistryInterface::class);
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(
            CustomerCartFixture::class,
            ['customer_id' => '$customer.id$', 'reserved_order_id' => 'test_order_with_simple_product'],
            as: 'cart'
        ),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order')
    ]
    public function testOrderItemProductWhenOutOfStock(): void
    {
        $this->updateProductStock();

        $this->assertEquals(
            [
                'customer' => [
                    'orders' => [
                        'items' => [
                            [
                                'number' => $this->fixtures->get('order')->getIncrementId(),
                                'items' => [
                                    [
                                        'product' => [
                                            'sku' => $this->fixtures->get('product')->getSku(),
                                            'stock_status' => 'OUT_OF_STOCK'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerOrdersQuery(),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Update product stock to out of stock
     *
     * @throws Exception
     */
    private function updateProductStock(): void
    {
        /** @var ProductInterface $product */
        $product = $this->fixtures->get('product');
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        $stockItem->setData(StockItemInterface::IS_IN_STOCK, false);
        $stockItem->setData(StockItemInterface::QTY, 0);
        $stockItem->setData(StockItemInterface::MANAGE_STOCK, true);
        $stockItem->save();
    }

    /**
     * Returns the GraphQL query to fetch customer orders.
     *
     * @return string
     */
    private function getCustomerOrdersQuery(): string
    {
        return <<<QUERY
            query {
              customer {
                orders {
                  items {
                    number
                    items {
                        product {
                            sku
                            stock_status
                        }
                    }
                  }
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
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
