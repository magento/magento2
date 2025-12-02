<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\ProductStock as ProductStockFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Model\Order;
use Magento\Sales\Test\Fixture\Creditmemo as CreditmemoFixture;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for the Order Item model
 *
 * @suppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemTest extends TestCase
{
    /**
     * @param string $options
     * @param array $expectedData
     * @dataProvider getProductOptionsDataProvider
     */
    public function testGetProductOptions($options, $expectedData)
    {
        $model = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Sales\Model\Order\Item::class);
        $model->setData('product_options', $options);
        $this->assertEquals($expectedData, $model->getProductOptions());
    }

    /**
     * @return array
     */
    public static function getProductOptionsDataProvider()
    {
        return [
            [
                '{"option1":1,"option2":2}',
                ["option1" => 1, "option2" => 2]
            ],
            [
                ["option1" => 1, "option2" => 2],
                ["option1" => 1, "option2" => 2]
            ],
        ];
    }

    /**
     * Test getSimpleQtyToShip method
     *
     * @magentoDbIsolation disabled
     * @magentoAppArea adminhtml
     * @magentoConfigFixture default/catalog/seo/generate_category_product_rewrites 0
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(ProductStockFixture::class, ['prod_id' => '$product.id$', 'prod_qty' => 100, 'is_in_stock' => 1]),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 10]
        ),
        DataFixture(
            SetBillingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(
            SetShippingAddressFixture::class,
            ['cart_id' => '$cart.id$', 'address' => ['email' => 'guest@example.com']]
        ),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice'),
        DataFixture(
            ShipmentFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 1]]]
        ),
        DataFixture(
            CreditmemoFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 2]]]
        ),
        DataFixture(
            ShipmentFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 1]]]
        ),
        DataFixture(
            CreditmemoFixture::class,
            ['order_id' => '$order.id$', 'items' => [['sku' => '$product.sku$', 'qty' => 2]]]
        )
    ]
    public function testGetSimpleQtyToShip()
    {
        $fixtures = DataFixtureStorageManager::getStorage();

        /** @var Order $order */
        $order = $fixtures->get('order');

        // Get order item
        $orderItems = $order->getItems();
        /** @var Item $orderItem */
        $orderItem = reset($orderItems);

        // Verify quantities
        $this->assertEquals(10, $orderItem->getQtyOrdered(), 'Qty ordered should be 10');
        $this->assertEquals(10, $orderItem->getQtyInvoiced(), 'Qty invoiced should be 10');
        $this->assertEquals(2, $orderItem->getQtyShipped(), 'Qty shipped should be 2');
        $this->assertEquals(4, $orderItem->getQtyRefunded(), 'Qty refunded should be 4');

        // Assert getSimpleQtyToShip value
        // Qty to ship = Qty ordered (10) - Qty shipped (2) - Qty refunded (4) = 4
        $this->assertEquals(4, $orderItem->getSimpleQtyToShip(), 'Simple qty to ship should be 4');
    }
}
