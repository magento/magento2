<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Fedex\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod;
use Magento\Checkout\Test\Fixture\SetPaymentMethod;
use Magento\Checkout\Test\Fixture\SetShippingAddress;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Test\Fixture\AddProductToCart;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Shipping\Model\CarrierFactory;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for FedEx shipping label creation.
 *
 * Tests the backend logic of:
 * - Creating shipment with FedEx carrier
 * - Adding packages to shipment
 * - Storing and retrieving tracking information
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateShippingLabelTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ShipmentFactory
     */
    private $shipmentFactory;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->shipmentRepository = $this->objectManager->get(ShipmentRepositoryInterface::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->shipmentFactory = $this->objectManager->get(ShipmentFactory::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Place order from cart and return order entity.
     *
     * @param Quote $cart
     * @return Order
     */
    private function placeOrderFromCart(Quote $cart): Order
    {
        $quoteManagement = $this->objectManager->get(QuoteManagement::class);
        $orderId = $quoteManagement->placeOrder($cart->getId());
        return $this->orderRepository->get($orderId);
    }

    /**
     * Create shipment for order with all items.
     *
     * @param Order $order
     * @return Shipment
     */
    private function createShipmentForOrder(Order $order): Shipment
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }
        return $this->shipmentFactory->create($order, $items);
    }

    /**
     * Place order and create shipment from fixtures.
     *
     * @return array{order: Order, shipment: Shipment}
     */
    private function createOrderAndShipment(): array
    {
        $order = $this->placeOrderFromCart($this->fixtures->get('cart'));
        return ['order' => $order, 'shipment' => $this->createShipmentForOrder($order)];
    }

    /**
     * Add tracking to shipment and save.
     *
     * @param Shipment $shipment
     * @param array $trackingData
     * @return Shipment
     */
    private function addTrackingAndSave(Shipment $shipment, array $trackingData): Shipment
    {
        foreach ($trackingData as $data) {
            $track = $this->objectManager->create(ShipmentTrackInterface::class);
            $track->setNumber($data['number'])
                ->setTitle($data['title'])
                ->setCarrierCode($data['carrier_code']);
            if (isset($data['description'])) {
                $track->setDescription($data['description']);
            }
            $shipment->addTrack($track);
        }
        $this->shipmentRepository->save($shipment);
        return $this->shipmentRepository->get((int)$shipment->getEntityId());
    }

    /**
     * Build package data for shipment.
     *
     * @param array $orderItemsArray
     * @param ProductInterface $product1
     * @param ProductInterface $product2
     * @return array
     */
    private function buildMultiPackageData(
        array $orderItemsArray,
        ProductInterface $product1,
        ProductInterface $product2
    ): array {
        return [
            1 => [
                'params' => [
                    'container' => 'YOUR_PACKAGING', 'weight' => 5.0, 'weight_units' => 'LB',
                    'length' => 10, 'width' => 8, 'height' => 6, 'dimension_units' => 'IN',
                    'customs_value' => 100.00, 'delivery_confirmation' => 'SIGNATURE',
                ],
                'items' => [[
                    'qty' => 1, 'customs_value' => 50.00, 'price' => 50.00,
                    'name' => $product1->getName(), 'weight' => 2.5,
                    'product_id' => $product1->getId(), 'order_item_id' => $orderItemsArray[0]->getId(),
                ]],
            ],
            2 => [
                'params' => [
                    'container' => 'YOUR_PACKAGING', 'weight' => 3.0, 'weight_units' => 'LB',
                    'length' => 8, 'width' => 6, 'height' => 4, 'dimension_units' => 'IN',
                    'customs_value' => 50.00, 'delivery_confirmation' => 'NO_SIGNATURE_REQUIRED',
                ],
                'items' => [[
                    'qty' => 1, 'customs_value' => 50.00, 'price' => 50.00,
                    'name' => $product2->getName(), 'weight' => 3.0,
                    'product_id' => $product2->getId(), 'order_item_id' => $orderItemsArray[1]->getId(),
                ]],
            ],
        ];
    }

    /**
     * Test creating shipment with multiple packages.
     *
     * @return void
     */
    #[
        ConfigFixture('carriers/fedex/active', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/api_key', 'test_api_key', 'store', 'default'),
        ConfigFixture('carriers/fedex/secret_key', 'test_secret_key', 'store', 'default'),
        ConfigFixture('carriers/fedex/account', 'test_account', 'store', 'default'),
        ConfigFixture('carriers/fedex/meter_number', 'test_meter', 'store', 'default'),
        ConfigFixture('carriers/fedex/sandbox_mode', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/allowed_methods', 'FEDEX_GROUND,FEDEX_2_DAY', 'store', 'default'),
        ConfigFixture('shipping/origin/country_id', 'US'),
        ConfigFixture('shipping/origin/region_id', '12'),
        ConfigFixture('shipping/origin/postcode', '90001'),
        ConfigFixture('shipping/origin/city', 'Los Angeles'),
        ConfigFixture('shipping/origin/street_line1', '123 Test Street'),
        ConfigFixture('general/store_information/name', 'Test Store'),
        ConfigFixture('general/store_information/phone', '5551234567'),
        DataFixture(ProductFixture::class, ['sku' => 'prod-1', 'price' => 50.00, 'weight' => 2.5], 'product1'),
        DataFixture(ProductFixture::class, ['sku' => 'prod-2', 'price' => 50.00, 'weight' => 3.0], 'product2'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product1.id$', 'qty' => 1]),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product2.id$', 'qty' => 1]),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart.id$']),
    ]
    public function testCreateShipmentWithMultiplePackages(): void
    {
        ['order' => $order, 'shipment' => $shipment] = $this->createOrderAndShipment();
        $this->assertContains($order->getState(), [Order::STATE_NEW, Order::STATE_PROCESSING]);
        $this->assertInstanceOf(ShipmentInterface::class, $shipment);

        $orderItemsArray = array_values(iterator_to_array($order->getItems()));
        $packages = $this->buildMultiPackageData(
            $orderItemsArray,
            $this->fixtures->get('product1'),
            $this->fixtures->get('product2')
        );
        $shipment->setPackages($packages);
        $savedShipment = $this->shipmentRepository->save($shipment);

        $this->assertNotNull($savedShipment->getEntityId());
        $this->assertCount(2, $savedShipment->getPackages());
        $this->assertMultiPackageData($savedShipment->getPackages());
    }

    /**
     * Assert multi-package data is correct.
     *
     * @param array $savedPackages
     * @return void
     */
    private function assertMultiPackageData(array $savedPackages): void
    {
        $this->assertEquals(5.0, $savedPackages[1]['params']['weight']);
        $this->assertEquals(10, $savedPackages[1]['params']['length']);
        $this->assertEquals(8, $savedPackages[1]['params']['width']);
        $this->assertEquals(6, $savedPackages[1]['params']['height']);
        $this->assertEquals(100.00, $savedPackages[1]['params']['customs_value']);
        $this->assertEquals('SIGNATURE', $savedPackages[1]['params']['delivery_confirmation']);
        $this->assertEquals(3.0, $savedPackages[2]['params']['weight']);
        $this->assertEquals(8, $savedPackages[2]['params']['length']);
        $this->assertEquals('NO_SIGNATURE_REQUIRED', $savedPackages[2]['params']['delivery_confirmation']);
    }

    /**
     * Test shipment operations with single product.
     *
     * Tests tracking and package persistence using data provider.
     *
     * @param string $testType
     * @param array $trackingData
     * @param array $packages
     * @return void
     * @dataProvider singleProductShipmentDataProvider
     */
    #[
        ConfigFixture('carriers/fedex/active', '1', 'store', 'default'),
        DataFixture(ProductFixture::class, ['sku' => 'test-product', 'price' => 100.00], 'product'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 2]),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart.id$']),
    ]
    public function testSingleProductShipmentOperations(
        string $testType,
        array $trackingData,
        array $packages
    ): void {
        ['order' => $order, 'shipment' => $shipment] = $this->createOrderAndShipment();

        if ($testType === 'tracking') {
            $savedShipment = $this->addTrackingAndSave($shipment, $trackingData);
            $tracks = $savedShipment->getTracks();

            $this->assertCount(count($trackingData), $tracks);
            $trackNumbers = [];
            foreach ($tracks as $track) {
                $trackNumbers[] = $track->getTrackNumber();
                $this->assertEquals('fedex', $track->getCarrierCode());
            }
            foreach ($trackingData as $data) {
                $this->assertContains($data['number'], $trackNumbers);
            }
        } elseif ($testType === 'packages') {
            $shipment->setPackages($packages);
            $this->shipmentRepository->save($shipment);
            $reloadedShipment = $this->shipmentRepository->get((int)$shipment->getEntityId());

            $this->assertCount(count($packages), $reloadedShipment->getPackages());
            $reloadedPackages = $reloadedShipment->getPackages();
            foreach ($packages as $key => $package) {
                $this->assertEquals(
                    $package['params']['weight'],
                    $reloadedPackages[$key]['params']['weight']
                );
            }
        } elseif ($testType === 'validation') {
            $product = $this->fixtures->get('product');
            $orderItemId = array_values(iterator_to_array($order->getItems()))[0]->getId();
            $packages[1]['items'][0]['product_id'] = $product->getId();
            $packages[1]['items'][0]['order_item_id'] = $orderItemId;
            $packages[1]['items'][0]['name'] = $product->getName();

            $shipment->setPackages($packages);
            $savedShipment = $this->shipmentRepository->save($shipment);
            $savedPackages = $savedShipment->getPackages();

            $this->assertEquals('YOUR_PACKAGING', $savedPackages[1]['params']['container']);
            $this->assertEquals(200.00, $savedPackages[1]['params']['customs_value']);
            $this->assertEquals('SIGNATURE', $savedPackages[1]['params']['delivery_confirmation']);
        }
    }

    /**
     * Data provider for single product shipment operations.
     *
     * @return array
     */
    public static function singleProductShipmentDataProvider(): array
    {
        return [
            'tracking information' => [
                'testType' => 'tracking',
                'trackingData' => [
                    ['number' => '794644790132', 'title' => 'FedEx Ground', 'carrier_code' => 'fedex'],
                    ['number' => '794644790133', 'title' => 'FedEx Ground', 'carrier_code' => 'fedex'],
                ],
                'packages' => [],
            ],
            'package persistence' => [
                'testType' => 'packages',
                'trackingData' => [],
                'packages' => [
                    1 => [
                        'params' => [
                            'container' => 'YOUR_PACKAGING', 'weight' => 2.5, 'weight_units' => 'LB',
                            'length' => 12, 'width' => 10, 'height' => 8, 'dimension_units' => 'IN',
                        ],
                        'items' => [],
                    ],
                    2 => [
                        'params' => [
                            'container' => 'YOUR_PACKAGING', 'weight' => 1.5, 'weight_units' => 'LB',
                            'length' => 6, 'width' => 4, 'height' => 2, 'dimension_units' => 'IN',
                        ],
                        'items' => [],
                    ],
                ],
            ],
            'package data validation' => [
                'testType' => 'validation',
                'trackingData' => [],
                'packages' => [
                    1 => [
                        'params' => [
                            'container' => 'YOUR_PACKAGING', 'customs_value' => 200.00,
                            'weight' => 10.0, 'weight_units' => 'LB',
                            'length' => 20, 'width' => 15, 'height' => 10, 'dimension_units' => 'IN',
                            'delivery_confirmation' => 'SIGNATURE',
                        ],
                        'items' => [['qty' => 2, 'customs_value' => 100.00, 'price' => 100.00, 'weight' => 5.0]],
                    ],
                ],
            ],
        ];
    }

    /**
     * Test shipment is associated with correct order for storefront access.
     *
     * @return void
     */
    #[
        ConfigFixture('carriers/fedex/active', '1', 'store', 'default'),
        DataFixture(ProductFixture::class, ['sku' => 'sf-product', 'price' => 120.00], 'product'),
        DataFixture(Customer::class, as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart'),
        DataFixture(AddProductToCart::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart.id$']),
    ]
    public function testShipmentAccessibleFromOrder(): void
    {
        $customer = $this->fixtures->get('customer');
        ['order' => $order, 'shipment' => $shipment] = $this->createOrderAndShipment();
        $this->assertEquals($customer->getId(), $order->getCustomerId());

        $savedShipment = $this->addTrackingAndSave($shipment, [
            ['number' => '794644790134', 'title' => 'FedEx 2Day', 'carrier_code' => 'fedex'],
        ]);

        // Reload order (simulating storefront order view)
        $reloadedOrder = $this->orderRepository->get($order->getEntityId());
        $shipmentCollection = $reloadedOrder->getShipmentsCollection();

        $this->assertCount(1, $shipmentCollection);
        $orderShipment = $shipmentCollection->getFirstItem();
        $this->assertEquals($savedShipment->getEntityId(), $orderShipment->getEntityId());

        // Verify tracking is accessible
        $tracks = $orderShipment->getTracks();
        $this->assertCount(1, $tracks);

        $trackData = null;
        foreach ($tracks as $track) {
            $trackData = $track;
            break;
        }
        $this->assertNotNull($trackData, 'Track should exist');
        $this->assertEquals('794644790134', $trackData->getTrackNumber());
        $this->assertEquals('fedex', $trackData->getCarrierCode());
        $this->assertEquals('FedEx 2Day', $trackData->getTitle());
    }

    /**
     * Test FedEx carrier is properly configured and active.
     *
     * @return void
     */
    #[
        ConfigFixture('carriers/fedex/active', '1', 'store', 'default'),
        ConfigFixture('carriers/fedex/title', 'Federal Express', 'store', 'default'),
        ConfigFixture('carriers/fedex/allowed_methods', 'FEDEX_GROUND,FEDEX_2_DAY', 'store', 'default'),
    ]
    public function testFedExCarrierConfiguration(): void
    {
        $carrierFactory = $this->objectManager->get(CarrierFactory::class);
        $fedexCarrier = $carrierFactory->create('fedex');

        $this->assertNotFalse($fedexCarrier, 'FedEx carrier should be created');
        $this->assertEquals('fedex', $fedexCarrier->getCarrierCode());

        $allowedMethods = $fedexCarrier->getAllowedMethods();
        $this->assertArrayHasKey('FEDEX_GROUND', $allowedMethods);
        $this->assertArrayHasKey('FEDEX_2_DAY', $allowedMethods);
    }
}
