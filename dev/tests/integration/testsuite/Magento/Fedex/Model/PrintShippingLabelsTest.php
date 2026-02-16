<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Fedex\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod;
use Magento\Checkout\Test\Fixture\SetPaymentMethod;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for printing FedEx shipping labels from Actions dropdown.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PrintShippingLabelsTest extends TestCase
{
    /**
     * Default package weight in pounds
     */
    private const PACKAGE_WEIGHT = 2.0;

    /**
     * Default package dimensions in inches
     */
    private const PACKAGE_LENGTH = 10;
    private const PACKAGE_WIDTH = 8;
    private const PACKAGE_HEIGHT = 6;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

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
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Complete order workflow: place order, invoice, shipment with optional label.
     *
     * @param bool $addLabel
     * @param string $trackingNumber
     * @return array{order: Order, shipment: Shipment}
     */
    private function completeOrderWorkflow(bool $addLabel = true, string $trackingNumber = '794644790135'): array
    {
        $product = $this->fixtures->get('product');
        $cart = $this->fixtures->get('cart');

        // Place order
        $quoteManagement = $this->objectManager->get(QuoteManagement::class);
        $orderId = $quoteManagement->placeOrder($cart->getId());
        $order = $this->orderRepository->get($orderId);

        // Create invoice
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->pay();
        $this->invoiceRepository->save($invoice);
        $order = $this->orderRepository->get($order->getEntityId());

        // Create shipment
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }
        $shipment = $this->shipmentFactory->create($order, $items);

        if ($addLabel) {
            $orderItemId = array_values(iterator_to_array($order->getItems()))[0]->getId();
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
            $shipment->setShippingLabel(base64_encode('%PDF-1.4 FedEx Shipping Label'));

            $track = $this->objectManager->create(ShipmentTrackInterface::class);
            $track->setNumber($trackingNumber)
                ->setTitle('FedEx Ground')
                ->setCarrierCode('fedex');
            $shipment->addTrack($track);
        }

        $savedShipment = $this->shipmentRepository->save($shipment);
        return ['order' => $this->orderRepository->get($order->getEntityId()), 'shipment' => $savedShipment];
    }

    /**
     * Check if shipment can be printed.
     *
     * @param ShipmentInterface $shipment
     * @return bool
     */
    private function canPrintLabel(ShipmentInterface $shipment): bool
    {
        return !empty($shipment->getShippingLabel())
            && count($shipment->getTracks()) > 0
            && count($shipment->getPackages()) > 0;
    }

    /**
     * Test shipping label scenarios using data provider (covers Steps 1-9).
     *
     * @param string $scenario
     * @param bool $addLabel
     * @param bool $expectPrintable
     * @param bool $verifyFullWorkflow
     * @param string $trackingNumber
     * @return void
     */
    #[DataProvider('shippingLabelScenariosProvider')]
    #[
        ConfigFixture('carriers/fedex/active', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/api_key', 'test_api_key', 'store', 'default'),
        ConfigFixture('carriers/fedex/secret_key', 'test_secret_key', 'store', 'default'),
        ConfigFixture('carriers/fedex/account', 'test_account', 'store', 'default'),
        ConfigFixture('carriers/fedex/sandbox_mode', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/allowed_methods', 'FEDEX_GROUND,FEDEX_2_DAY', 'store', 'default'),
        ConfigFixture('shipping/origin/country_id', 'US'),
        ConfigFixture('shipping/origin/region_id', '12'),
        ConfigFixture('shipping/origin/postcode', '90001'),
        ConfigFixture('shipping/origin/city', 'Los Angeles'),
        ConfigFixture('shipping/origin/street_line1', '123 Test Street'),
        ConfigFixture('general/store_information/name', 'Test Store'),
        ConfigFixture('general/store_information/phone', '5551234567'),
        DataFixture(ProductFixture::class, ['sku' => 'fedex-product', 'price' => 75.00, 'weight' => 2.0], 'product'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart.id$']),
    ]
    public function testShippingLabelScenarios(
        string $scenario,
        bool $addLabel,
        bool $expectPrintable,
        bool $verifyFullWorkflow,
        string $trackingNumber
    ): void {
        ['order' => $order, 'shipment' => $shipment] = $this->completeOrderWorkflow($addLabel, $trackingNumber);

        // Verify order state (can be new, processing, or complete depending on payment config)
        $this->assertContains(
            $order->getState(),
            [Order::STATE_NEW, Order::STATE_PROCESSING, Order::STATE_COMPLETE]
        );

        // Reload shipment from repository
        $reloadedShipment = $this->shipmentRepository->get((int)$shipment->getEntityId());

        // Verify printability
        $this->assertEquals($expectPrintable, $this->canPrintLabel($reloadedShipment), "Scenario: $scenario");

        // Verify download capability for printable labels
        if ($expectPrintable) {
            $labelContent = $reloadedShipment->getShippingLabel();
            $this->assertNotEmpty($labelContent, 'Label content should exist');
            $decoded = base64_decode($labelContent);
            $this->assertNotFalse($decoded, 'Label should be valid base64');
            $this->assertStringContainsString('PDF', $decoded, 'Label should be PDF format');
            $this->assertCount(1, $reloadedShipment->getPackages());
            $this->assertCount(1, $reloadedShipment->getTracks());
            $this->assertEquals('YOUR_PACKAGING', $reloadedShipment->getPackages()[1]['params']['container']);
        }

        // Full workflow verification
        if ($verifyFullWorkflow) {
            $customer = $this->fixtures->get('customer');
            $this->assertEquals($customer->getId(), $order->getCustomerId());
            $this->assertNotEmpty($order->getIncrementId());
            $this->assertNotNull($shipment->getEntityId());
            $this->assertCount(1, $order->getShipmentsCollection());

            $tracks = $reloadedShipment->getTracks();
            $trackData = null;
            foreach ($tracks as $track) {
                $trackData = $track;
                break;
            }
            $this->assertEquals($trackingNumber, $trackData->getTrackNumber());
            $this->assertEquals('fedex', $trackData->getCarrierCode());
        }
    }

    /**
     * Data provider for shipping label scenarios.
     *
     * @return array
     */
    public static function shippingLabelScenariosProvider(): array
    {
        return [
            'full workflow - printable and downloadable (Steps 1-9)' => [
                'scenario' => 'full_workflow',
                'addLabel' => true,
                'expectPrintable' => true,
                'verifyFullWorkflow' => true,
                'trackingNumber' => '794644790140',
            ],
            'with label - printable' => [
                'scenario' => 'with_label',
                'addLabel' => true,
                'expectPrintable' => true,
                'verifyFullWorkflow' => false,
                'trackingNumber' => '794644790135',
            ],
            'without label - not printable' => [
                'scenario' => 'without_label',
                'addLabel' => false,
                'expectPrintable' => false,
                'verifyFullWorkflow' => false,
                'trackingNumber' => '',
            ],
        ];
    }
}
