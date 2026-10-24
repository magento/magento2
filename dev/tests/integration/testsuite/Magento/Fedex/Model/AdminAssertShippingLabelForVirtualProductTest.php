<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Fedex\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetPaymentMethod;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateFactory as QuoteAddressRateFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory as RateResultMethodFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for FedEx shipping label generation with a mixed cart (simple + virtual + configurable).
 *
 * Validates that when creating a shipment for FedEx, virtual items are properly excluded from the
 * shipment, matching the admin packaging grid behavior, while physical items (simple and configurable)
 * are included and available for shipping.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class AdminAssertShippingLabelForVirtualProductTest extends TestCase
{
    private const PACKAGE_WEIGHT = 2.0;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var ShipmentFactory
     */
    private ShipmentFactory $shipmentFactory;

    /**
     * @var InvoiceService
     */
    private InvoiceService $invoiceService;

    /**
     * @var InvoiceRepositoryInterface
     */
    private InvoiceRepositoryInterface $invoiceRepository;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $quoteRepository;

    /**
     * @var QuoteAddressRateFactory
     */
    private QuoteAddressRateFactory $addressRateFactory;

    /**
     * @var RateResultMethodFactory
     */
    private RateResultMethodFactory $resultMethodFactory;

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * Assigns shared services and fixture storage used by tests.
     *
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->shipmentFactory = $this->objectManager->get(ShipmentFactory::class);
        $this->invoiceService = $this->objectManager->get(InvoiceService::class);
        $this->invoiceRepository = $this->objectManager->get(InvoiceRepositoryInterface::class);
        $this->quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->addressRateFactory = $this->objectManager->get(QuoteAddressRateFactory::class);
        $this->resultMethodFactory = $this->objectManager->get(RateResultMethodFactory::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Mixed cart: simple (country of manufacture DE), virtual, configurable; virtual is not shippable.
     *
     * @return void
     */
    #[
        ConfigFixture('carriers/fedex/active', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/sandbox_mode', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/debug', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/showmethod', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/api_key', 'test_api_key', 'store', 'default'),
        ConfigFixture('carriers/fedex/secret_key', 'test_secret_key', 'store', 'default'),
        ConfigFixture('carriers/fedex/account', 'test_account', 'store', 'default'),
        ConfigFixture('carriers/fedex/smartpost_hubid', '55312', 'store', 'default'),
        ConfigFixture('carriers/fedex/meter_number', 'test_meter', 'store', 'default'),
        ConfigFixture('carriers/fedex/title', 'Federal Express', 'store', 'default'),
        ConfigFixture('carriers/fedex/allowed_methods', 'FEDEX_GROUND,FEDEX_2_DAY', 'store', 'default'),
        ConfigFixture('shipping/origin/country_id', 'US'),
        ConfigFixture('shipping/origin/postcode', '90001'),
        ConfigFixture('shipping/origin/city', 'Los Angeles'),
        ConfigFixture('shipping/origin/street_line1', '7700 West Parmer Lane'),
        ConfigFixture('shipping/origin/street_line2', '113'),
        ConfigFixture('shipping/origin/region_id', '12'),
        ConfigFixture('general/store_information/name', 'New Store Information', 'store', 'default'),
        ConfigFixture('general/store_information/phone', '555-55-555-55', 'store', 'default'),
        ConfigFixture('general/store_information/country_id', 'US', 'store', 'default'),
        ConfigFixture('general/store_information/city', 'Culver City', 'store', 'default'),
        ConfigFixture('general/store_information/postcode', '90230', 'store', 'default'),
        ConfigFixture('general/store_information/street_line1', '6161 West Centinela Avenue', 'store', 'default'),
        ConfigFixture('general/store_information/street_line2', '16', 'store', 'default'),
        ConfigFixture('general/store_information/region_id', '12', 'store', 'default'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'fedex-mixed-simple',
                'price' => 50.00,
                'weight' => self::PACKAGE_WEIGHT,
                'custom_attributes' => [
                    ['attribute_code' => 'country_of_manufacture', 'value' => 'DE'],
                ],
            ],
            'product'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'type_id' => Type::TYPE_VIRTUAL,
                'sku' => 'fedex-mixed-virtual',
                'name' => 'FedEx Virtual Mixed Cart',
                'price' => 9.99,
            ],
            'virtualProduct'
        ),
        DataFixture(ConfigurableAttributeFixture::class, as: 'attr'),
        DataFixture(ProductFixture::class, ['price' => 20.00, 'weight' => 1.5], as: 'p', count: 2),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'sku' => 'fedex-mixed-configurable',
                'name' => 'FedEx Configurable Mixed Cart',
                '_options' => ['$attr$'],
                '_links' => ['$p1$', '$p2$'],
            ],
            'configProduct'
        ),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(
            AddProductToCart::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$virtualProduct.id$', 'qty' => 1]
        ),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$configProduct.id$',
                'child_product_id' => '$p1.id$',
                'qty' => 1,
            ]
        ),
        DataFixture(SetBillingAddress::class, [
            'cart_id' => '$cart.id$',
            'address' => [
                AddressInterface::KEY_TELEPHONE => '555-55-555-55',
                AddressInterface::KEY_POSTCODE => '90230',
                AddressInterface::KEY_COUNTRY_ID => 'US',
                AddressInterface::KEY_CITY => 'Culver City',
                AddressInterface::KEY_COMPANY => 'Magento',
                AddressInterface::KEY_STREET => ['6161 West Centinela Avenue', '16'],
                AddressInterface::KEY_FIRSTNAME => 'John',
                AddressInterface::KEY_LASTNAME => 'Doe',
                AddressInterface::KEY_REGION_ID => 12,
            ],
        ]),
        DataFixture(SetShippingAddress::class, [
            'cart_id' => '$cart.id$',
            'address' => [
                AddressInterface::KEY_TELEPHONE => '555-55-555-55',
                AddressInterface::KEY_POSTCODE => '90230',
                AddressInterface::KEY_COUNTRY_ID => 'US',
                AddressInterface::KEY_CITY => 'Culver City',
                AddressInterface::KEY_COMPANY => 'Magento',
                AddressInterface::KEY_STREET => ['6161 West Centinela Avenue', '16'],
                AddressInterface::KEY_FIRSTNAME => 'John',
                AddressInterface::KEY_LASTNAME => 'Doe',
                AddressInterface::KEY_REGION_ID => 12,
            ],
        ]),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart.id$']),
    ]
    public function testVirtualProductExcludedFromShipmentWhileSimpleAndConfigurableShip(): void
    {
        $simpleProduct = $this->fixtures->get('product');
        $virtualProduct = $this->fixtures->get('virtualProduct');
        $configProduct = $this->fixtures->get('configProduct');
        $cart = $this->fixtures->get('cart');

        $simpleReloaded = $this->objectManager->get(ProductRepositoryInterface::class)
            ->getById($simpleProduct->getId());
        $countryAttr = $simpleReloaded->getCustomAttribute('country_of_manufacture');
        $this->assertNotNull($countryAttr);
        $this->assertSame('DE', $countryAttr->getValue());

        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cart->getId());
        $this->injectFedExGroundRate($quote);
        $quote->collectTotals();
        $quote->save();

        $quoteManagement = $this->objectManager->get(QuoteManagement::class);
        $orderId = $quoteManagement->placeOrder($cart->getId());
        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);

        $this->assertStringStartsWith('fedex_', (string)$order->getShippingMethod());

        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->pay();
        $this->invoiceRepository->save($invoice);
        $order = $this->orderRepository->get((int)$order->getEntityId());

        $cfgId = (int)$configProduct->getId();
        $lines = $this->resolveMixedCartOrderLines($order, $simpleProduct, $virtualProduct, $cfgId);

        $this->assertNotNull($lines['virtual'], 'Order should contain the virtual line item');
        $this->assertTrue((bool)$lines['virtual']->getIsVirtual());
        $this->assertNotNull($lines['simple'], 'Order should contain the simple product line');
        $this->assertFalse((bool)$lines['simple']->getIsVirtual());
        $this->assertNotNull($lines['configParent'], 'Order should contain the configurable parent line');
        $this->assertFalse((bool)$lines['configParent']->getIsVirtual());

        $qtys = $this->buildShipmentQtysForPhysicalLines($order);
        $this->assertNotEmpty($qtys, 'Expected at least one shippable order line with qty available to ship');
        $shipment = $this->shipmentFactory->create($order, $qtys);
        $this->assertInstanceOf(Shipment::class, $shipment);

        [$shipProductIds, $shipSkus] = $this->collectShipmentProductIdsAndSkus($shipment);

        $this->assertNotEmpty($shipProductIds, 'Shipment should contain at least one shippable product row');
        $this->assertNotContains(
            (int)$virtualProduct->getId(),
            $shipProductIds,
            'Virtual catalog product must not appear on shipment (packaging grid excludes it)'
        );
        $this->assertContains(
            (int)$simpleProduct->getId(),
            $shipProductIds,
            'Simple physical product should be shippable'
        );
        $this->assertTrue(
            $this->shipmentHasConfigurableLine($shipSkus, $cfgId, $order),
            'Configurable product should contribute a shippable shipment row (parent or child)'
        );
    }

    /**
     * Finds order items for the simple, virtual, and configurable parent lines created by fixtures.
     *
     * @param Order $order Order after invoice (reloaded).
     * @param DataObject $simpleProduct Simple product fixture row.
     * @param DataObject $virtualProduct Virtual product fixture row.
     * @param int $cfgProductId Configurable parent catalog product ID.
     * @return array
     */
    private function resolveMixedCartOrderLines(
        Order $order,
        DataObject $simpleProduct,
        DataObject $virtualProduct,
        int $cfgProductId
    ): array {
        $virtual = $simple = $configParent = null;
        foreach ($order->getAllItems() as $item) {
            /** @var OrderItem $item */
            if ($item->getSku() === $virtualProduct->getSku()) {
                $virtual = $item;
            }
            if ($item->getSku() === $simpleProduct->getSku()) {
                $simple = $item;
            }
            if ((int)$item->getProductId() === $cfgProductId && !$item->getParentItemId()) {
                $configParent = $item;
            }
        }

        return ['virtual' => $virtual, 'simple' => $simple, 'configParent' => $configParent];
    }

    /**
     * Builds the order item ID to quantity map for ShipmentFactory (non-virtual lines with qty to ship only).
     *
     * @param Order $order Invoiced order with shippable quantities.
     * @return array
     */
    private function buildShipmentQtysForPhysicalLines(Order $order): array
    {
        $qtys = [];
        foreach ($order->getAllItems() as $item) {
            /** @var OrderItem $item */
            if ($item->getIsVirtual()) {
                continue;
            }
            $qtyToShip = $item->getQtyToShip();
            if ($qtyToShip > 0) {
                $qtys[$item->getId()] = $qtyToShip;
            }
        }

        return $qtys;
    }

    /**
     * Collects catalog product IDs and SKUs from non-deleted shipment items.
     *
     * @param Shipment $shipment New shipment document (not persisted).
     * @return array
     */
    private function collectShipmentProductIdsAndSkus(Shipment $shipment): array
    {
        $productIds = [];
        $skus = [];
        foreach ($shipment->getAllItems() as $shipmentItem) {
            if ($shipmentItem->isDeleted()) {
                continue;
            }
            $productIds[] = (int)$shipmentItem->getProductId();
            $skus[] = $shipmentItem->getSku();
        }

        return [$productIds, $skus];
    }

    /**
     * Whether the shipment includes a row for the configurable (parent SKU or child SKU under that parent).
     *
     * @param string[] $shipSkus SKUs present on shipment items.
     * @param int $cfgProductId Configurable parent catalog product ID.
     * @param Order $order Same order used to create the shipment.
     * @return bool
     */
    private function shipmentHasConfigurableLine(array $shipSkus, int $cfgProductId, Order $order): bool
    {
        foreach ($order->getAllItems() as $orderItem) {
            /** @var OrderItem $orderItem */
            if ((int)$orderItem->getProductId() === $cfgProductId
                && in_array($orderItem->getSku(), $shipSkus, true)
            ) {
                return true;
            }
            $parent = $orderItem->getParentItem();
            if ($parent && (int)$parent->getProductId() === $cfgProductId
                && in_array($orderItem->getSku(), $shipSkus, true)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Injects a synthetic FedEx Ground rate so placeOrder succeeds without calling the carrier API.
     *
     * @param Quote $quote Active customer quote with shipping address.
     * @return void
     */
    private function injectFedExGroundRate(Quote $quote): void
    {
        $address = $quote->getShippingAddress();
        $address->removeAllShippingRates();

        $carrierMethod = $this->resultMethodFactory->create();
        $carrierMethod->setCarrier('fedex');
        $carrierMethod->setCarrierTitle('Federal Express');
        $carrierMethod->setMethod('FEDEX_GROUND');
        $carrierMethod->setMethodTitle('Ground');
        $carrierMethod->setPrice(10.0);

        $rate = $this->addressRateFactory->create()->importShippingRate($carrierMethod);
        $address->addShippingRate($rate);
        $address->setShippingMethod('fedex_FEDEX_GROUND');
        $address->setShippingDescription('Federal Express - Ground');
        $address->setShippingAmount(10.0);
        $address->setBaseShippingAmount(10.0);
        $address->setCollectShippingRates(false);
    }
}
