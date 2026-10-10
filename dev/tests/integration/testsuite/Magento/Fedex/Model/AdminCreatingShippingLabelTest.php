<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Fedex\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetPaymentMethod;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateFactory as QuoteAddressRateFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory as RateResultMethodFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
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
 * Backend scenario translated from MFTF AdminCreatingShippingLabelTest.
 *
 * Covers:
 * FedEx configuration, product country of manufacture (DE), checkout addresses,
 * order placed with FedEx shipping method, invoice, shipment with packages and shipping label + tracking,
 * assertions on carrier title "Federal Express".
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class AdminCreatingShippingLabelTest extends TestCase
{
    private const PACKAGE_WEIGHT = 2.0;

    private const PACKAGE_LENGTH = 10;

    private const PACKAGE_WIDTH = 8;

    private const PACKAGE_HEIGHT = 6;

    private const FEDEX_TRACKING_NUMBER = '794644790202';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ShipmentRepositoryInterface
     */
    private ShipmentRepositoryInterface $shipmentRepository;

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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->shipmentRepository = $this->objectManager->get(ShipmentRepositoryInterface::class);
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
     * FedEx order, invoice, shipment with label; track shows Federal Express.
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
                'sku' => 'fedex-admin-label-product',
                'price' => 50.00,
                'weight' => self::PACKAGE_WEIGHT,
                'custom_attributes' => [
                    ['attribute_code' => 'country_of_manufacture', 'value' => 'DE'],
                ],
            ],
            'product'
        ),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
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
    public function testAdminCreatesShippingLabelScenario(): void
    {
        $product = $this->fixtures->get('product');
        $cart = $this->fixtures->get('cart');

        $productReloaded = $this->objectManager->get(ProductRepositoryInterface::class)->getById($product->getId());
        $countryAttr = $productReloaded->getCustomAttribute('country_of_manufacture');
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
        $this->assertStringContainsStringIgnoringCase('fedex', (string)$order->getShippingMethod());

        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->pay();
        $this->invoiceRepository->save($invoice);
        /** @var Order $order */
        $order = $this->orderRepository->get($order->getEntityId());

        $items = [];
        foreach ($order->getItems() as $item) {
            /** @var OrderItem $item */
            $items[$item->getId()] = $item->getQtyOrdered();
        }
        /** @var Shipment $shipment */
        $shipment = $this->shipmentFactory->create($order, $items);

        /** @var OrderItem $firstOrderItem */
        $firstOrderItem = array_values(iterator_to_array($order->getItems()))[0];
        $orderItemId = $firstOrderItem->getId();
        $shipment->setPackages([
            1 => [
                'params' => [
                    'container' => 'YOUR_PACKAGING',
                    'weight' => self::PACKAGE_WEIGHT,
                    'weight_units' => 'LB',
                    'length' => self::PACKAGE_LENGTH,
                    'width' => self::PACKAGE_WIDTH,
                    'height' => self::PACKAGE_HEIGHT,
                    'dimension_units' => 'IN',
                    'customs_value' => $product->getPrice(),
                    'delivery_confirmation' => 'NO_SIGNATURE_REQUIRED',
                ],
                'items' => [
                    [
                        'qty' => 1,
                        'customs_value' => $product->getPrice(),
                        'price' => $product->getPrice(),
                        'name' => $product->getName(),
                        'weight' => self::PACKAGE_WEIGHT,
                        'product_id' => $product->getId(),
                        'order_item_id' => $orderItemId,
                    ],
                ],
            ],
        ]);
        $shipment->setShippingLabel(base64_encode('%PDF-1.4 Test FedEx label'));

        $track = $this->objectManager->create(ShipmentTrackInterface::class);
        $track->setNumber(self::FEDEX_TRACKING_NUMBER)
            ->setTitle('Federal Express')
            ->setCarrierCode('fedex');
        $shipment->addTrack($track);

        $savedShipment = $this->shipmentRepository->save($shipment);
        $reloaded = $this->shipmentRepository->get((int)$savedShipment->getEntityId());

        $this->assertContains(
            $order->getState(),
            [Order::STATE_NEW, Order::STATE_PROCESSING, Order::STATE_COMPLETE]
        );
        $this->assertNotEmpty($reloaded->getShippingLabel());
        $this->assertCount(1, $reloaded->getPackages());
        $this->assertCount(1, $reloaded->getTracks());

        $trackRow = null;
        foreach ($reloaded->getTracks() as $t) {
            $trackRow = $t;
            break;
        }
        $this->assertNotNull($trackRow);
        $this->assertSame('fedex', $trackRow->getCarrierCode());
        $this->assertSame('Federal Express', $trackRow->getTitle());
        $this->assertSame(self::FEDEX_TRACKING_NUMBER, $trackRow->getTrackNumber());
    }

    /**
     * Add a FedEx Ground rate without calling the FedEx API.
     *
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
