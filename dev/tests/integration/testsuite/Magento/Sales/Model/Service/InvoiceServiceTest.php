<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Sales\Model\Service;

use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCartFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Test\Fixture\ProductCondition as ProductConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests \Magento\Sales\Model\Service\InvoiceService
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var OrderRepositoryInterface|null
     */
    private ?OrderRepositoryInterface $orderRepository;

    /**
     * @var DataFixtureStorage|null
     */
    private ?DataFixtureStorage $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->invoiceService = Bootstrap::getObjectManager()->create(InvoiceService::class);
        $this->orderRepository = Bootstrap::getObjectManager()->create(OrderRepositoryInterface::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * @param int $invoiceQty
     * @magentoDataFixture Magento/Sales/_files/order_configurable_product.php
     * @return void
     * @dataProvider prepareInvoiceConfigurableProductDataProvider
     */
    public function testPrepareInvoiceConfigurableProduct(int $invoiceQty): void
    {
        /** @var OrderInterface $order */
        $order = Bootstrap::getObjectManager()->create(Order::class)->load('100000001', 'increment_id');
        $orderItems = $order->getItems();
        $parentItemId = 0;
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getParentItemId()) {
                $parentItemId = $orderItem->getParentItemId();
            }
        }
        $invoice = $this->invoiceService->prepareInvoice($order, [$parentItemId => $invoiceQty]);
        $invoiceItems = $invoice->getItems();
        foreach ($invoiceItems as $invoiceItem) {
            $this->assertEquals($invoiceQty, $invoiceItem->getQty());
        }
    }

    public static function prepareInvoiceConfigurableProductDataProvider()
    {
        return [
            'full invoice' => [2],
            'partial invoice' => [1]
        ];
    }

    /**
     * @param int $invoiceQty
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @return void
     * @dataProvider prepareInvoiceSimpleProductDataProvider
     */
    public function testPrepareInvoiceSimpleProduct(int $invoiceQty): void
    {
        /** @var OrderInterface $order */
        $order = Bootstrap::getObjectManager()->create(Order::class)->load('100000001', 'increment_id');
        $orderItems = $order->getItems();
        $invoiceQtys = [];
        foreach ($orderItems as $orderItem) {
            $invoiceQtys[$orderItem->getItemId()] = $invoiceQty;
        }
        $invoice = $this->invoiceService->prepareInvoice($order, $invoiceQtys);
        $invoiceItems = $invoice->getItems();
        foreach ($invoiceItems as $invoiceItem) {
            $this->assertEquals($invoiceQty, $invoiceItem->getQty());
        }
    }

    public static function prepareInvoiceSimpleProductDataProvider()
    {
        return [
            'full invoice' => [2],
            'partial invoice' => [1]
        ];
    }

    /**
     * Checks if ordered and invoiced qty of bundle product does match.
     *
     * @param array $qtyToInvoice
     * @param array $qtyInvoiced
     * @param string $errorMsg
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @magentoDataFixture Magento/Sales/_files/order_with_bundle.php
     * @dataProvider bundleProductQtyOrderedDataProvider
     */
    public function testPrepareInvoiceBundleProduct(
        array $qtyToInvoice,
        array $qtyInvoiced,
        string $errorMsg
    ): void {
        /** @var Order $order */
        $order = Bootstrap::getObjectManager()->create(Order::class)
            ->load('100000001', 'increment_id');

        $predefinedQtyToInvoice = $this->getPredefinedQtyToInvoice($order, $qtyToInvoice);
        $invoice = $this->invoiceService->prepareInvoice($order, $predefinedQtyToInvoice);

        foreach ($invoice->getItems() as $invoiceItem) {
            if (isset($qtyInvoiced[$invoiceItem->getSku()])) {
                $this->assertEquals(
                    $qtyInvoiced[$invoiceItem->getSku()],
                    $invoiceItem->getQty(),
                    sprintf($errorMsg, $invoiceItem->getSku())
                );
            }
        }
    }

    /**
     * Data provider for invoice creation with and w/o predefined qty to invoice.
     *
     * @return array
     */
    public static function bundleProductQtyOrderedDataProvider(): array
    {
        return [
            'Create invoice w/o predefined qty' => [
                'qtyToInvoice' => [],
                'qtyInvoiced' => [
                    'bundle_1' => 2,
                    'bundle_simple_1' => 10,
                ],
                'errorMsg' => 'Invoiced qty for product %s does not match.',
            ],
            'Create invoice with predefined qty' => [
                'qtyToInvoice' => [
                    'bundle_1' => 2,
                    'bundle_simple_1' => 10,
                ],
                'qtyInvoiced' => [
                    'bundle_1' => 2,
                    'bundle_simple_1' => 10,
                ],
                'errorMsg' => 'Invoiced qty for product %s does not match.',
            ],
            'Create invoice with partial predefined qty for bundle' => [
                'qtyToInvoice' => [
                    'bundle_1' => 1,
                ],
                'qtyInvoiced' => [
                    'bundle_1' => 1,
                    'bundle_simple_1' => 5,
                ],
                'errorMsg' => 'Invoiced qty for product %s does not match.',
            ],
        ];
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 10], as: 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], as: 'p2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p2$']], 'opt2'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$', '$opt2$']], 'bp1'),
        DataFixture(ProductConditionFixture::class, ['attribute' => 'sku', 'value' => '$bp1.sku$'], 'cond1'),
        DataFixture(
            RuleFixture::class,
            [
                'simple_action' => Rule::BY_PERCENT_ACTION,
                'discount_amount' => 20,
                'actions' => ['$cond1$'],
                'simple_free_shipping' => \Magento\OfflineShipping\Model\SalesRule\Rule::FREE_SHIPPING_ITEM
            ]
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 1
            ],
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testPrepareInvoiceBundleProductDynamicPriceWithDiscount(): void
    {
        $order = $this->fixtures->get('order');
        $order = $this->orderRepository->get($order->getId());
        $qtyToInvoice = [];
        foreach ($order->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                $qtyToInvoice[$item->getId()] = 1;
            }
        }
        $this->assertNotEmpty($qtyToInvoice);
        $invoice = $this->invoiceService->prepareInvoice($order, $qtyToInvoice);
        $this->assertEquals(-6, $invoice->getBaseDiscountAmount());
        $this->assertEquals(24, $invoice->getBaseGrandTotal());
    }

    /**
     * Associate product qty to invoice to order item id.
     *
     * @param Order $order
     * @param array $qtyToInvoice
     * @return array
     */
    private function getPredefinedQtyToInvoice(Order $order, array $qtyToInvoice): array
    {
        $predefinedQtyToInvoice = [];

        foreach ($order->getAllItems() as $orderItem) {
            if (array_key_exists($orderItem->getSku(), $qtyToInvoice)) {
                $predefinedQtyToInvoice[$orderItem->getId()] = $qtyToInvoice[$orderItem->getSku()];
            }
        }

        return $predefinedQtyToInvoice;
    }
}
