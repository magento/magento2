<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Bundle\Block\Sales\Order\Items;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCartFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\OrderItem as OrderItemFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Config\Model\ResourceModel\Config as CoreConfig;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RendererTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;
    /**
     * @var Session
     */
    protected $session;

    /** @var Renderer */
    private $block;

    /**
     * @var CoreConfig
     */
    protected $resourceConfig;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @defaultDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $layout = $this->objectManager->get(LayoutInterface::class);
        $this->block = $layout->createBlock(Renderer::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->resourceConfig = $this->objectManager->get(CoreConfig::class);
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->orderSender = $this->objectManager->get(OrderSender::class);
    }

    #[
        DbIsolation(false),
        Config('default/currency/options/base', 'USD', 'store', 'default'),
        Config('currency/options/default', 'EUR', 'store', 'default'),
        Config('currency/options/allow', 'USD, EUR', 'store', 'default'),
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$', '$p2$']], 'opt1'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$']], 'bundle1'),
        DataFixture(OrderItemFixture::class, ['items' => [['sku' => '$bundle1.sku$']]], 'order'),
    ]
    public function testOrderEmailContent(): void
    {
        $order = $this->objectManager->create(Order::class);

        $incrementId =  $this->fixtures->get('order')->getIncrementId();
        $order->loadByIncrementId($incrementId);

        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $currencyCode = $storeManager->getWebsite()->getDefaultStore()->getDefaultCurrency()->getCode();
        $storeId = $this->objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $order->setStoreId($storeId);
        $order->setOrderCurrencyCode($currencyCode);
        $order->save();

        $priceBlockHtml = [];

        $items = $order->getAllItems();
        foreach ($items as $item) {
            $item->setProductOptions([
                'bundle_options' => [
                    [
                        'value' => [
                            ['title' => '']
                        ],
                    ],
                ],
                'bundle_selection_attributes' => '{"qty":5 ,"price":99}'
            ]);
            $this->block->setItem($item);
            $priceBlockHtml[] = $this->block->getValueHtml($item);
        }

        $this->assertStringContainsString("€99", $priceBlockHtml[0]);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    #[
        DbIsolation(true),
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(ProductFixture::class, ['price' => 30], 'p3'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$', '$p2$', '$p3$']], 'opt1'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$']], 'bundle1'),
        DataFixture(OrderItemFixture::class, ['items' => [['sku' => '$bundle1.sku$']]], 'order'),
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
    ]
    public function testPlaceOrderWithOtherThanDefaultCurrencyValidateEmailHasSameCurrency(): void
    {
        $this->resourceConfig->saveConfig(
            'currency/options/default',
            'EUR',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        $this->resourceConfig->saveConfig(
            'currency/options/allow',
            'EUR',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        $this->resourceConfig->saveConfig(
            'currency/options/base',
            'USD',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        // Load customer data
        $customer = $this->fixtures->get('customer');
        $customerEmail = $customer->getEmail();

        // Login to customer
        $this->accountManagement->authenticate($customerEmail, 'password');

        // Including address data file
        $addressData = include __DIR__ . '/../../../../../Sales/_files/address_data.php';

        // Setting the billing address
        $billingAddress = $this->objectManager->create(OrderAddress::class, ['data' => $addressData]);
        $billingAddress->setAddressType('billing');

        // Setting the shipping address
        $shippingAddress = clone $billingAddress;
        $shippingAddress->setId(null)->setAddressType('shipping');

        // Place the order
        $order = $this->objectManager->create(Order::class);
        $incrementId = $this->fixtures->get('order')->getIncrementId();
        $order->loadByIncrementId($incrementId);
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $currencyCodeSymbol = $storeManager->getWebsite()->getDefaultStore()->getDefaultCurrency()->getCurrencySymbol();
        $storeId = $this->objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $order->setStoreId($storeId);
        $order->setCustomerEmail($customerEmail);
        $order->setBillingAddress($billingAddress);
        $order->setShippingAddress($shippingAddress);
        $order->save();
        $this->orderSender->send($order);
        $this->assertTrue($order->getSendEmail());

        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = Bootstrap::getObjectManager()
            ->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();

        $this->assertNotNull($sentMessage);
        $this->assertStringContainsString(
            $currencyCodeSymbol,
            quoted_printable_decode($sentMessage->getBody()->bodyToString())
        );
    }

    #[
        AppArea('frontend'),
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(
            BundleOptionFixture::class,
            [
                'product_links' => [['sku' => '$p1.sku$', 'can_change_quantity' => 1]]
            ],
            'opt1'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'product_links' => [['sku' => '$p2.sku$', 'can_change_quantity' => 1]]
            ],
            'opt2'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                '_options' => ['$opt1$', '$opt2$'],
                'price_type' => Price::PRICE_TYPE_DYNAMIC,
                'shipment_type' => AbstractType::SHIPMENT_TOGETHER,
            ],
            'b1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$b1.id$',
                'qty' => '2',
                'selections' => [
                    [['product_id' => '$p1.id$', 'qty' => 3]],
                    [['product_id' => '$p2.id$', 'qty' => 5]],
                ]
            ],
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testOrderViewForBundleDynamicPriceShipTogether(): void
    {
        $b1 = $this->fixtures->get('b1');
        $this->assertEquals(Price::PRICE_TYPE_DYNAMIC, $b1->getPriceType());
        $this->assertEquals(AbstractType::SHIPMENT_TOGETHER, $b1->getShipmentType());
        $this->assertQty(['b1' => 2, 'p1' => 6, 'p2' => 10]);
    }

    #[
        AppArea('frontend'),
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(
            BundleOptionFixture::class,
            [
                'product_links' => [['sku' => '$p1.sku$', 'can_change_quantity' => 1]]
            ],
            'opt1'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'product_links' => [['sku' => '$p2.sku$', 'can_change_quantity' => 1]]
            ],
            'opt2'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                '_options' => ['$opt1$', '$opt2$'],
                'price_type' => Price::PRICE_TYPE_DYNAMIC,
                'shipment_type' => AbstractType::SHIPMENT_SEPARATELY,
            ],
            'b1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$b1.id$',
                'qty' => '2',
                'selections' => [
                    [['product_id' => '$p1.id$', 'qty' => 3]],
                    [['product_id' => '$p2.id$', 'qty' => 5]],
                ]
            ],
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testOrderViewForBundleDynamicPriceShipSeparately(): void
    {
        $b1 = $this->fixtures->get('b1');
        $this->assertEquals(Price::PRICE_TYPE_DYNAMIC, $b1->getPriceType());
        $this->assertEquals(AbstractType::SHIPMENT_SEPARATELY, $b1->getShipmentType());
        $this->assertQty(['b1' => 2, 'p1' => 6, 'p2' => 10]);
    }

    #[
        AppArea('frontend'),
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(
            BundleOptionFixture::class,
            [
                'product_links' => [['sku' => '$p1.sku$', 'can_change_quantity' => 1]]
            ],
            'opt1'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'product_links' => [['sku' => '$p2.sku$', 'can_change_quantity' => 1]]
            ],
            'opt2'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                '_options' => ['$opt1$', '$opt2$'],
                'price_type' => Price::PRICE_TYPE_FIXED,
                'shipment_type' => AbstractType::SHIPMENT_TOGETHER,
            ],
            'b1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$b1.id$',
                'qty' => '2',
                'selections' => [
                    [['product_id' => '$p1.id$', 'qty' => 3]],
                    [['product_id' => '$p2.id$', 'qty' => 5]],
                ]
            ],
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testOrderViewForBundleFixedPriceShipTogether(): void
    {
        $b1 = $this->fixtures->get('b1');
        $this->assertEquals(Price::PRICE_TYPE_FIXED, $b1->getPriceType());
        $this->assertEquals(AbstractType::SHIPMENT_TOGETHER, $b1->getShipmentType());
        $this->assertQty(['b1' => 2, 'p1' => 6, 'p2' => 10]);
    }

    #[
        AppArea('frontend'),
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(
            BundleOptionFixture::class,
            [
                'product_links' => [['sku' => '$p1.sku$', 'can_change_quantity' => 1]]
            ],
            'opt1'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'product_links' => [['sku' => '$p2.sku$', 'can_change_quantity' => 1]]
            ],
            'opt2'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                '_options' => ['$opt1$', '$opt2$'],
                'price_type' => Price::PRICE_TYPE_FIXED,
                'shipment_type' => AbstractType::SHIPMENT_SEPARATELY,
            ],
            'b1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$b1.id$',
                'qty' => '2',
                'selections' => [
                    [['product_id' => '$p1.id$', 'qty' => 3]],
                    [['product_id' => '$p2.id$', 'qty' => 5]],
                ]
            ],
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
    ]
    public function testOrderViewForBundleFixedPriceShipSeparately(): void
    {
        $b1 = $this->fixtures->get('b1');
        $this->assertEquals(Price::PRICE_TYPE_FIXED, $b1->getPriceType());
        $this->assertEquals(AbstractType::SHIPMENT_SEPARATELY, $b1->getShipmentType());
        $this->assertQty(['b1' => 2, 'p1' => 6, 'p2' => 10]);
    }

    #[
        AppArea('frontend'),
        DataFixture(ProductFixture::class, ['price' => 10], 'p1'),
        DataFixture(ProductFixture::class, ['price' => 20], 'p2'),
        DataFixture(
            BundleOptionFixture::class,
            [
                'product_links' => [['sku' => '$p1.sku$', 'can_change_quantity' => 1]]
            ],
            'opt1'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'product_links' => [['sku' => '$p2.sku$', 'can_change_quantity' => 1]]
            ],
            'opt2'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                '_options' => ['$opt1$', '$opt2$'],
                'price_type' => Price::PRICE_TYPE_FIXED,
                'shipment_type' => AbstractType::SHIPMENT_SEPARATELY,
            ],
            'b1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$b1.id$',
                'qty' => '2',
                'selections' => [
                    [['product_id' => '$p1.id$', 'qty' => 3]],
                    [['product_id' => '$p2.id$', 'qty' => 5]],
                ]
            ],
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$']),
        DataFixture(
            ShipmentFixture::class,
            [
                'order_id' => '$order.id$',
                'items' => [
                    ['sku' => '$p1.sku$', 'qty' => 4], // Shipping 4 of 6
                    ['sku' => '$p2.sku$', 'qty' => 7], // Shipping 7 of 10
                ]
            ]
        ),
    ]
    public function testOrderViewForBundleFixedPriceShipSeparatelyAfterShipment(): void
    {
        $b1 = $this->fixtures->get('b1');
        $this->assertEquals(Price::PRICE_TYPE_FIXED, $b1->getPriceType());
        $this->assertEquals(AbstractType::SHIPMENT_SEPARATELY, $b1->getShipmentType());
        $this->assertQty(['b1' => 2, 'p1' => 6, 'p2' => 10], 'Ordered');
        // Shipped qty does not show for bundle product when shipment type is separate
        $this->assertQty(['b1' => '', 'p1' => 4, 'p2' => 7], 'Shipped');
    }

    private function getQtyFromOrderItemsGrid(\DOMXPath $xpath, string $name, string $label = 'Ordered'): string
    {
        return $xpath->evaluate(
            "string(" .
                "//tr[contains(., '$name')]" .
                "/td[contains(@class, 'qty')]" .
                "//span[text()='$label']" .
                "/following-sibling::span[contains(@class, 'content')]" .
            ")"
        );
    }

    private function getBlock(string $name): mixed
    {
        $page = $this->objectManager->get(PageFactory::class)->create();
        $page->addHandle([
            'default',
            'sales_order_view',
        ]);
        $page->getLayout()->generateXml();

        return $page->getLayout()->getBlock($name);
    }
    
    private function assertQty(
        array $expected,
        string $label = 'Ordered'
    ): void {
        $order = $this->fixtures->get('order');
        $this->objectManager->get(Registry::class)->unregister('current_order');
        $this->objectManager->get(Registry::class)->register('current_order', $order);
        $this->block = $this->getBlock('sales.order.items.renderers.bundle');
        $this->block->setItem($this->fixtures->get('order')->getAllItems()[0]);
        $html = $this->block->toHtml();

        $xpath = Xpath::getDOMXpath($html);
        foreach ($expected as $id => $qty) {
            $product = $this->fixtures->get($id);
            $this->assertEquals(
                $qty,
                trim($this->getQtyFromOrderItemsGrid($xpath, $product->getName(), $label)),
                "$label qty for product $id does not match expected"
            );
        }
    }

    /**
     * @inheriDoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->get(Registry::class)->unregister('current_order');
        parent::tearDown();
    }
}
