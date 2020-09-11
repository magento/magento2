<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * API test for creation of Shipment for certain Order.
 */
class ShipOrderTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_READ_NAME = 'salesShipOrderV1';
    const SERVICE_VERSION = 'V1';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->shipmentRepository = $this->objectManager->get(ShipmentRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_configurable_product.php
     */
    public function testConfigurableShipOrder()
    {
        $this->markTestIncomplete('https://github.com/magento-engcom/msi/issues/1335');
        $productsQuantity = 1;

        /** @var Order $existingOrder */
        $existingOrder = $this->getOrder('100000001');

        $requestData = [
            'orderId' => $existingOrder->getId(),
        ];

        $shipmentId = (int)$this->_webApiCall($this->getServiceInfo($existingOrder), $requestData);
        $this->assertNotEmpty($shipmentId);

        try {
            $shipment = $this->shipmentRepository->get($shipmentId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->fail('Failed asserting that Shipment was created');
        }

        $orderedQty = 0;
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($existingOrder->getItems() as $item) {
            if ($item->isDummy(true)) {
                continue;
            }
            $orderedQty += $item->getQtyOrdered();
        }

        $this->assertEquals(
            (int)$shipment->getTotalQty(),
            (int)$orderedQty,
            'Failed asserting that quantity of ordered and shipped items is equal'
        );
        $this->assertEquals(
            $productsQuantity,
            count($shipment->getItems()),
            'Failed asserting that quantity of products and sales shipment items is equal'
        );
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_new.php
     */
    public function testShipOrder()
    {
        /** @var Order $existingOrder */
        $existingOrder = $this->getOrder('100000001');

        $requestData = [
            'orderId' => $existingOrder->getId(),
            'items' => [],
            'comment' => [
                'comment' => 'Test Comment',
                'is_visible_on_front' => 1,
            ],
            'tracks' => [
                [
                    'track_number' => 'TEST_TRACK_0001',
                    'title' => 'Simple shipment track',
                    'carrier_code' => 'UPS'
                ]
            ]
        ];

        /** @var OrderItemInterface $item */
        foreach ($existingOrder->getAllItems() as $item) {
            $requestData['items'][] = [
                'order_item_id' => $item->getItemId(),
                'qty' => $item->getQtyOrdered(),
            ];
        }

        $result = $this->_webApiCall($this->getServiceInfo($existingOrder), $requestData);

        $this->assertNotEmpty($result);

        try {
            $this->shipmentRepository->get($result);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->fail('Failed asserting that Shipment was created');
        }

        /** @var Order $updatedOrder */
        $updatedOrder = $this->getOrder('100000001');

        $this->assertNotEquals(
            $existingOrder->getStatus(),
            $updatedOrder->getStatus(),
            'Failed asserting that Order status was changed'
        );
    }

    /**
     * Tests that not providing a tracking number produces the correct error. See MAGETWO-95429
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     * @magentoApiDataFixture Magento/Sales/_files/order_new.php
     */
    public function testShipOrderWithoutTrackingNumberReturnsError()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches(
            '/Shipment Document Validation Error\\(s\\):(?:\\n|\\\\n)Please enter a tracking number./'
        );

        $this->_markTestAsRestOnly('SOAP requires an tracking number to be provided so this case is not possible.');

        /** @var Order $existingOrder */
        $existingOrder = $this->getOrder('100000001');

        $requestData = [
            'orderId' => $existingOrder->getId(),
            'comment' => [
                'comment' => 'Test Comment',
                'is_visible_on_front' => 1,
            ],
            'tracks' => [
                [
                    'title' => 'Simple shipment track',
                    'carrier_code' => 'UPS'
                ]
            ]
        ];

        $this->_webApiCall($this->getServiceInfo($existingOrder), $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/order_with_bundle_shipped_separately.php
     */
    public function testPartialShipOrderWithBundleShippedSeparately()
    {
        $existingOrder = $this->getOrder('100000001');

        $requestData = [
            'orderId' => $existingOrder->getId(),
            'items' => [],
            'comment' => [
                'comment' => 'Test Comment',
                'is_visible_on_front' => 1,
            ],
            'tracks' => [
                [
                    'track_number' => 'TEST_TRACK_0001',
                    'title' => 'Simple shipment track',
                    'carrier_code' => 'UPS'
                ]
            ]
        ];

        $shippedItemId = null;
        foreach ($existingOrder->getAllItems() as $item) {
            if ($item->getProductType() == 'simple') {
                $requestData['items'][] = [
                    'order_item_id' => $item->getItemId(),
                    'qty' => $item->getQtyOrdered(),
                ];
                $shippedItemId = $item->getItemId();
                break;
            }
        }

        $shipmentId = $this->_webApiCall($this->getServiceInfo($existingOrder), $requestData);
        $this->assertNotEmpty($shipmentId);

        try {
            $shipment = $this->shipmentRepository->get($shipmentId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->fail('Failed asserting that Shipment was created');
        }

        $this->assertEquals(1, $shipment->getTotalQty());

        /** @var Order $existingOrder */
        $existingOrder = $this->getOrder('100000001');

        foreach ($existingOrder->getAllItems() as $item) {
            if ($item->getItemId() == $shippedItemId) {
                $this->assertEquals(1, $item->getQtyShipped());
                continue;
            }
            $this->assertEquals(0, $item->getQtyShipped());
        }
    }

    /**
     * @magentoApiDataFixture Magento/Bundle/_files/order_with_2_bundles_shipping_separately.php
     */
    public function testPartialShipOrderWithTwoBundleShippedSeparatelyContainsSameSimple()
    {
        $order = $this->getOrder('order_bundle_separately_shipped');

        $requestData = [
            'orderId' => $order->getId(),
            'items' => [],
            'comment' => [
                'comment' => 'Test Comment',
                'is_visible_on_front' => 1,
            ],
            'tracks' => [],
        ];

        $shippedItemId = null;
        $parentItemId = null;
        foreach ($order->getAllItems() as $item) {
            if ($item->getSku() === 'simple1') {
                $requestData['items'][] = [
                    'order_item_id' => $item->getItemId(),
                    'qty' => $item->getQtyOrdered(),
                ];
                $shippedItemId = $item->getItemId();
                $parentItemId = $item->getParentItemId();
                break;
            }
        }

        $shipmentId = $this->_webApiCall($this->getServiceInfo($order), $requestData);
        $this->assertNotEmpty($shipmentId);

        try {
            $shipment = $this->shipmentRepository->get($shipmentId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->fail('Failed asserting that Shipment was created');
        }

        $this->assertEquals(1, $shipment->getTotalQty());

        $order = $this->getOrder('order_bundle_separately_shipped');

        foreach ($order->getAllItems() as $item) {
            if (in_array($item->getItemId(), [$shippedItemId, $parentItemId])) {
                $this->assertEquals(1, $item->getQtyShipped());
                continue;
            }
            $this->assertEquals(0, $item->getQtyShipped());
        }

        try {
            $this->_webApiCall($this->getServiceInfo($order), $requestData);
            $this->fail('Expected exception was not raised');
        } catch (\Exception $exception) {
            $this->assertExceptionMessage(
                $exception,
                'Shipment Document Validation Error(s): You can\'t create a shipment without products.'
            );
        }
    }

    /**
     * @param Order $order
     * @return array
     */
    private function getServiceInfo(Order $order): array
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/order/' . $order->getId() . '/ship',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'execute',
            ],
        ];

        return $serviceInfo;
    }

    /**
     * Returns order by increment id.
     *
     * @param string $incrementId
     * @return Order
     */
    private function getOrder(string $incrementId): Order
    {
        return $this->objectManager->create(Order::class)->loadByIncrementId($incrementId);
    }

    /**
     * Assert correct exception message.
     *
     * @param \Exception $exception
     * @param string $expectedMessage
     * @return void
     */
    private function assertExceptionMessage(\Exception $exception, string $expectedMessage): void
    {
        $actualMessage = '';
        switch (TESTS_WEB_API_ADAPTER) {
            case self::ADAPTER_SOAP:
                $actualMessage = trim(preg_replace('/\s+/', ' ', $exception->getMessage()));
                break;
            case self::ADAPTER_REST:
                $error = $this->processRestExceptionResult($exception);
                $actualMessage = $error['message'];
                break;
        }

        $this->assertEquals($expectedMessage, $actualMessage);
    }
}
