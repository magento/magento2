<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Shipping a bundle whose selection quantity is above 1 records the full quantity on the child item.
 *
 * @see https://github.com/magento/magento2/issues/38029
 */
class ShipOrderBundleSelectionQtyTest extends WebapiAbstract
{
    private const SERVICE_NAME = 'salesShipOrderV1';

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Ship everything, bundle items shipped together.
     *
     * @return void
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['price' => 10, 'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]],
            'product'
        ),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product.sku$', 'qty' => 3], 'selection'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection$']], 'opt'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt$']], 'bundle'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            AddBundleProductToCart::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$bundle.id$', 'selections' => [['$product.id$']], 'qty' => 1]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$']),
    ]
    public function testShipAllTogether(): void
    {
        $this->assertItemsShipped(
            false,
            ['bundle ordered=1 invoiced=1 shipped=1', 'simple ordered=3 invoiced=3 shipped=3']
        );
    }

    /**
     * Ship the bundle item by id (as the admin form does), bundle items shipped together.
     *
     * @return void
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['price' => 10, 'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]],
            'product'
        ),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product.sku$', 'qty' => 3], 'selection'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection$']], 'opt'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt$']], 'bundle'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            AddBundleProductToCart::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$bundle.id$', 'selections' => [['$product.id$']], 'qty' => 1]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$']),
    ]
    public function testShipBundleItemTogether(): void
    {
        $this->assertItemsShipped(
            true,
            ['bundle ordered=1 invoiced=1 shipped=1', 'simple ordered=3 invoiced=3 shipped=3']
        );
    }

    /**
     * Ship two bundles by the bundle item id, bundle items shipped together.
     *
     * @return void
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['price' => 10, 'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]],
            'product'
        ),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product.sku$', 'qty' => 3], 'selection'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection$']], 'opt'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt$']], 'bundle'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            AddBundleProductToCart::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$bundle.id$', 'selections' => [['$product.id$']], 'qty' => 2]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$']),
    ]
    public function testShipBundleItemTogetherTwoBundles(): void
    {
        $this->assertItemsShipped(
            true,
            ['bundle ordered=2 invoiced=2 shipped=2', 'simple ordered=6 invoiced=6 shipped=6'],
            2
        );
    }

    /**
     * Selection quantity chosen by the customer.
     *
     * @return void
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['price' => 10, 'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]],
            'product'
        ),
        DataFixture(
            BundleSelectionFixture::class,
            ['sku' => '$product.sku$', 'qty' => 3, 'can_change_quantity' => 1],
            'selection'
        ),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection$']], 'opt'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt$']], 'bundle'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle.id$',
                'selections' => [[['product_id' => '$product.id$', 'qty' => 3]]],
                'qty' => 1
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$']),
    ]
    public function testShipUserDefinedSelectionQty(): void
    {
        $this->assertItemsShipped(
            true,
            ['bundle ordered=1 invoiced=1 shipped=1', 'simple ordered=3 invoiced=3 shipped=3']
        );
    }

    /**
     * Ship everything, bundle items shipped separately.
     *
     * @return void
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['price' => 10, 'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]],
            'product'
        ),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product.sku$', 'qty' => 3], 'selection'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection$']], 'opt'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt$'], 'shipment_type' => '1'], 'bundle'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            AddBundleProductToCart::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$bundle.id$', 'selections' => [['$product.id$']], 'qty' => 1]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$']),
    ]
    public function testShipAllSeparately(): void
    {
        $this->assertItemsShipped(
            false,
            ['bundle ordered=1 invoiced=1 shipped=1', 'simple ordered=3 invoiced=3 shipped=3']
        );
    }

    /**
     * Ship everything, two bundles, items shipped separately.
     *
     * @return void
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['price' => 10, 'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]],
            'product'
        ),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product.sku$', 'qty' => 3], 'selection'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection$']], 'opt'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt$'], 'shipment_type' => '1'], 'bundle'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            AddBundleProductToCart::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$bundle.id$', 'selections' => [['$product.id$']], 'qty' => 2]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$']),
    ]
    public function testShipAllSeparatelyTwoBundles(): void
    {
        $this->assertItemsShipped(
            false,
            ['bundle ordered=2 invoiced=2 shipped=2', 'simple ordered=6 invoiced=6 shipped=6']
        );
    }

    /**
     * Two bundles, items shipped separately, in two shipments of one bundle's items each.
     *
     * @return void
     */
    #[
        DataFixture(
            ProductFixture::class,
            ['price' => 10, 'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]],
            'product'
        ),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product.sku$', 'qty' => 3], 'selection'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection$']], 'opt'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt$'], 'shipment_type' => '1'], 'bundle'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(
            AddBundleProductToCart::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$bundle.id$', 'selections' => [['$product.id$']], 'qty' => 2]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$']),
    ]
    public function testShipSeparatelyInTwoShipments(): void
    {
        $childItemId = null;
        foreach ($this->fixtures->get('order')->getItems() as $item) {
            if ($item->getProductType() === 'simple') {
                $childItemId = $item->getItemId();
            }
        }
        $this->ship([['order_item_id' => $childItemId, 'qty' => 3]]);
        $this->assertSame(
            ['bundle ordered=2 invoiced=2 shipped=1', 'simple ordered=6 invoiced=6 shipped=3'],
            $this->getItemRows(),
            'Three of six items: one bundle shipped.'
        );
        $this->ship([['order_item_id' => $childItemId, 'qty' => 3]]);
        $this->assertSame(
            ['bundle ordered=2 invoiced=2 shipped=2', 'simple ordered=6 invoiced=6 shipped=6'],
            $this->getItemRows(),
            'All six items: both bundles shipped.'
        );
    }

    /**
     * Ship the order and assert the item quantities
     *
     * @param bool $byBundleItem pass the bundle order item id, as the admin shipment form does
     * @param string[] $expected
     * @param float $bundleQty
     * @return void
     */
    private function assertItemsShipped(bool $byBundleItem, array $expected, float $bundleQty = 1): void
    {
        $items = [];
        if ($byBundleItem) {
            foreach ($this->fixtures->get('order')->getItems() as $item) {
                if ($item->getProductType() === 'bundle') {
                    $items[] = ['order_item_id' => $item->getItemId(), 'qty' => $bundleQty];
                }
            }
        }
        $this->ship($items);
        $this->assertSame($expected, $this->getItemRows());
    }

    /**
     * Ship the fixture order
     *
     * @param array $items
     * @return void
     */
    private function ship(array $items): void
    {
        $orderId = $this->fixtures->get('order')->getEntityId();
        $this->_webApiCall(
            [
                'rest' => [
                    'resourcePath' => '/V1/order/' . $orderId . '/ship',
                    'httpMethod' => Request::HTTP_METHOD_POST,
                ],
                'soap' => [
                    'service' => self::SERVICE_NAME,
                    'serviceVersion' => 'V1',
                    'operation' => self::SERVICE_NAME . 'execute',
                ],
            ],
            ['orderId' => $orderId, 'items' => $items]
        );
    }

    /**
     * Product type and quantities of each order item
     *
     * @return string[]
     */
    private function getItemRows(): array
    {
        $order = Bootstrap::getObjectManager()->create(OrderRepositoryInterface::class)
            ->get($this->fixtures->get('order')->getEntityId());
        $rows = [];
        foreach ($order->getItems() as $item) {
            $rows[] = sprintf(
                '%s ordered=%s invoiced=%s shipped=%s',
                $item->getProductType(),
                (float)$item->getQtyOrdered(),
                (float)$item->getQtyInvoiced(),
                (float)$item->getQtyShipped()
            );
        }

        return $rows;
    }
}
