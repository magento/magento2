<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product\Type;

use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Adding a bundle product to the cart when no selection is made.
 *
 * @see https://github.com/magento/magento2/issues/40573
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    DataFixture(ProductFixture::class, ['price' => 10], as: 'p1'),
    DataFixture(ProductFixture::class, ['price' => 20], as: 'p2'),
    DataFixture(
        BundleSelectionFixture::class,
        ['sku' => '$p1.sku$', 'price' => 10, 'price_type' => 0],
        as: 'link1'
    ),
    DataFixture(
        BundleSelectionFixture::class,
        ['sku' => '$p2.sku$', 'price' => 20, 'price_type' => 0],
        as: 'link2'
    ),
    DataFixture(
        BundleOptionFixture::class,
        ['required' => false, 'type' => 'radio', 'product_links' => ['$link1$']],
        as: 'optional1'
    ),
    DataFixture(
        BundleOptionFixture::class,
        ['required' => false, 'type' => 'checkbox', 'product_links' => ['$link2$']],
        as: 'optional2'
    ),
    DataFixture(
        BundleOptionFixture::class,
        ['required' => true, 'type' => 'radio', 'product_links' => ['$link1$', '$link2$']],
        as: 'required1'
    ),
    DataFixture(
        BundleProductFixture::class,
        [
            'sku' => 'optional-fixed',
            'price' => 50,
            'custom_attributes' => [
                'price_view' => '0',
                'sku_type' => '0',
                'price_type' => '1',
                'weight_type' => '0',
                'shipment_type' => '0',
            ],
            '_options' => ['$optional1$', '$optional2$'],
        ],
        as: 'optional_fixed'
    ),
    DataFixture(
        BundleProductFixture::class,
        ['sku' => 'optional-dynamic', '_options' => ['$optional1$', '$optional2$']],
        as: 'optional_dynamic'
    ),
    DataFixture(
        BundleProductFixture::class,
        [
            'sku' => 'required-only',
            'price' => 50,
            'custom_attributes' => [
                'price_view' => '0',
                'sku_type' => '0',
                'price_type' => '1',
                'weight_type' => '0',
                'shipment_type' => '0',
            ],
            '_options' => ['$required1$'],
        ],
        as: 'required_only'
    ),
    DataFixture(
        BundleProductFixture::class,
        [
            'sku' => 'mixed',
            'price' => 50,
            'custom_attributes' => [
                'price_view' => '0',
                'sku_type' => '0',
                'price_type' => '1',
                'weight_type' => '0',
                'shipment_type' => '0',
            ],
            '_options' => ['$required1$', '$optional2$'],
        ],
        as: 'mixed'
    ),
    DataFixture(GuestCartFixture::class, as: 'cart'),
]
class PrepareForCartWithoutSelectionTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
    }

    /**
     * An empty selection posted for a fixed price bundle without required options adds the bundle alone.
     *
     * @param string $sku
     * @param string $selection
     * @param float $expectedSubtotal
     * @return void
     */
    #[DataProvider('optionalBundleEmptySelectionDataProvider')]
    public function testOptionalBundleWithEmptySelectionIsAdded(
        string $sku,
        string $selection,
        float $expectedSubtotal
    ): void {
        $product = $this->getProduct($sku);

        $quote = $this->addToCart($product, $this->buildBuyRequest($product, $selection));

        $items = $quote->getAllItems();
        $this->assertCount(1, $items);
        $this->assertSame((int)$product->getId(), (int)$items[0]->getProductId());
        $this->assertEmpty($items[0]->getChildren());
        $this->assertEqualsWithDelta($expectedSubtotal, (float)$quote->getSubtotal(), 0.0001);
        $this->assertEqualsWithDelta($expectedSubtotal, (float)$quote->getGrandTotal(), 0.0001);
    }

    /**
     * @return array
     */
    public static function optionalBundleEmptySelectionDataProvider(): array
    {
        return [
            'fixed price, empty bundle_option' => ['optional-fixed', 'empty', 50.0],
            'fixed price, "None" chosen' => ['optional-fixed', 'none', 50.0],
        ];
    }

    /**
     * Without any bundle_option in the request, the customer still has to configure the bundle.
     *
     * @return void
     */
    public function testOptionalBundleWithOmittedSelectionIsRejected(): void
    {
        $product = $this->getProduct('optional-fixed');

        $result = $this->getQuote()->addProduct($product, new DataObject(['qty' => 1]));

        $this->assertSame('Please specify product option(s).', $result);
        $this->assertTrue($product->getOptionsValidationFail());
        $this->assertCount(0, $this->getQuote()->getAllItems());
    }

    /**
     * Bundles with a required option, and dynamic price bundles, still reject a missing selection.
     *
     * @param string $sku
     * @param string $selection
     * @param string $expectedMessage
     * @return void
     */
    #[DataProvider('missingSelectionDataProvider')]
    public function testMissingSelectionIsRejected(
        string $sku,
        string $selection,
        string $expectedMessage
    ): void {
        $product = $this->getProduct($sku);

        $result = $this->getQuote()->addProduct($product, $this->buildBuyRequest($product, $selection));

        $this->assertSame($expectedMessage, $result);
        $this->assertCount(0, $this->getQuote()->getAllItems());
    }

    /**
     * @return array
     */
    public static function missingSelectionDataProvider(): array
    {
        return [
            'dynamic price, empty bundle_option' => ['optional-dynamic', 'empty', 'Please specify product option(s).'],
            'dynamic price, "None" chosen' => ['optional-dynamic', 'none', 'Please specify product option(s).'],
            'required only, empty bundle_option' => ['required-only', 'empty', 'Please specify product option(s).'],
            'required only, empty value posted' => ['required-only', 'none', 'Please specify product option(s).'],
            'required only, omitted bundle_option' => ['required-only', 'omitted', 'Please specify product option(s).'],
            'mixed, empty bundle_option' => ['mixed', 'empty', 'Please specify product option(s).'],
            'mixed, omitted bundle_option' => ['mixed', 'omitted', 'Please specify product option(s).'],
            'mixed, optional option only' => ['mixed', 'last', 'Please select all required options.'],
        ];
    }

    /**
     * A selection made on a bundle without required options is still added with its child.
     *
     * @param string $sku
     * @param float $expectedSubtotal
     * @return void
     */
    #[DataProvider('optionalBundleWithSelectionDataProvider')]
    public function testOptionalBundleWithSelectionIsAddedWithChild(string $sku, float $expectedSubtotal): void
    {
        $product = $this->getProduct($sku);

        $quote = $this->addToCart($product, $this->buildBuyRequest($product, 'first'));

        $this->assertCount(2, $quote->getAllItems());
        $parent = $quote->getAllVisibleItems()[0];
        $this->assertSame((int)$product->getId(), (int)$parent->getProductId());
        $this->assertCount(1, $parent->getChildren());
        $this->assertSame($this->fixtures->get('p1')->getSku(), $parent->getChildren()[0]->getSku());
        $this->assertEqualsWithDelta($expectedSubtotal, (float)$quote->getSubtotal(), 0.0001);
    }

    /**
     * @return array
     */
    public static function optionalBundleWithSelectionDataProvider(): array
    {
        return [
            'fixed price' => ['optional-fixed', 60.0],
            'dynamic price' => ['optional-dynamic', 10.0],
        ];
    }

    /**
     * The non-strict (lite) process mode still prepares a bundle without any bundle_option.
     *
     * @param string $sku
     * @return void
     */
    #[DataProvider('nonStrictModeDataProvider')]
    public function testNonStrictModeAcceptsOmittedSelection(string $sku): void
    {
        $product = $this->getProduct($sku);

        $result = $product->getTypeInstance()->prepareForCartAdvanced(
            new DataObject(['qty' => 1]),
            $product,
            AbstractType::PROCESS_MODE_LITE
        );

        $this->assertIsArray($result);
        $this->assertSame((int)$product->getId(), (int)$result[0]->getId());
    }

    /**
     * @return array
     */
    public static function nonStrictModeDataProvider(): array
    {
        return [
            'optional only' => ['optional-fixed'],
            'required only' => ['required-only'],
        ];
    }

    /**
     * A fixed price bundle added without selection can be ordered, invoiced and shipped, with no child item.
     *
     * @return void
     */
    public function testOrderBundleWithoutSelection(): void
    {
        $expectedSubtotal = 50.0;
        $product = $this->getProduct('optional-fixed');
        $quote = $this->addToCart($product, $this->buildBuyRequest($product, 'empty'));
        $cartId = (int)$quote->getId();

        $this->objectManager->get(SetBillingAddressFixture::class)->apply(['cart_id' => $cartId]);
        $this->objectManager->get(SetShippingAddressFixture::class)->apply(['cart_id' => $cartId]);
        $this->objectManager->get(SetGuestEmailFixture::class)->apply(['cart_id' => $cartId]);
        $this->objectManager->get(SetDeliveryMethodFixture::class)->apply(['cart_id' => $cartId]);
        $this->objectManager->get(SetPaymentMethodFixture::class)->apply(['cart_id' => $cartId]);
        /** @var OrderInterface $order */
        $order = $this->objectManager->get(PlaceOrderFixture::class)->apply(['cart_id' => $cartId]);

        $items = $order->getItems();
        $this->assertCount(1, $items);
        $item = reset($items);
        $this->assertSame((int)$product->getId(), (int)$item->getProductId());
        $this->assertSame('bundle', $item->getProductType());
        $this->assertEmpty($item->getChildrenItems());
        $this->assertEqualsWithDelta($expectedSubtotal, (float)$order->getSubtotal(), 0.0001);
        $this->assertEqualsWithDelta(
            $expectedSubtotal + (float)$order->getShippingAmount(),
            (float)$order->getGrandTotal(),
            0.0001
        );

        $invoice = $this->objectManager->get(InvoiceFixture::class)->apply(['order_id' => $order->getEntityId()]);
        $this->assertEqualsWithDelta((float)$order->getGrandTotal(), (float)$invoice->getGrandTotal(), 0.0001);
        $this->objectManager->get(ShipmentFixture::class)->apply(['order_id' => $order->getEntityId()]);

        $order = $this->objectManager->create(OrderRepositoryInterface::class)->get($order->getEntityId());
        $this->assertSame(Order::STATE_COMPLETE, $order->getState());
        $item = current($order->getItems());
        $this->assertEqualsWithDelta(1.0, (float)$item->getQtyInvoiced(), 0.0001);
        $this->assertEqualsWithDelta(1.0, (float)$item->getQtyShipped(), 0.0001);
    }

    /**
     * Build the buy request the storefront would post for the given selection.
     *
     * @param Product $product
     * @param string $selection empty|none|omitted|first|last
     * @return DataObject
     */
    private function buildBuyRequest(Product $product, string $selection): DataObject
    {
        $request = ['qty' => 1];
        $typeInstance = $product->getTypeInstance();
        $options = $typeInstance->getOptionsCollection($product);
        $options->appendSelections($typeInstance->getSelectionsCollection($options->getAllIds(), $product), true);
        $optionsList = array_values($options->getItems());

        switch ($selection) {
            case 'empty':
                $request['bundle_option'] = [];
                break;
            case 'none':
                $request['bundle_option'] = [$optionsList[0]->getId() => ''];
                break;
            case 'first':
            case 'last':
                $option = $selection === 'first' ? reset($optionsList) : end($optionsList);
                $selectionId = (int)$option->getSelections()[0]->getSelectionId();
                $request['bundle_option'] = [$option->getId() => [$selectionId]];
                $request['bundle_option_qty'] = [$option->getId() => [$selectionId => 1]];
                break;
        }

        return new DataObject($request);
    }

    /**
     * Add the product to the fixture cart and save it.
     *
     * @param Product $product
     * @param DataObject $buyRequest
     * @return Quote
     */
    private function addToCart(Product $product, DataObject $buyRequest): Quote
    {
        $quote = $this->getQuote();
        $result = $quote->addProduct($product, $buyRequest);
        $this->assertIsNotString($result, is_string($result) ? $result : '');
        $this->cartRepository->save($quote);

        return $this->getQuote();
    }

    /**
     * @return Quote
     */
    private function getQuote(): Quote
    {
        return $this->cartRepository->get((int)$this->fixtures->get('cart')->getId());
    }

    /**
     * @param string $sku
     * @return Product
     */
    private function getProduct(string $sku): Product
    {
        return $this->productRepository->get($sku, false, null, true);
    }
}
