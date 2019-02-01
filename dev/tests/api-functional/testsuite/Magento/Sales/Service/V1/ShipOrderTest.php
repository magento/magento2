<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

/**
 * API test for creation of Shipment for certain Order.
 */
class ShipOrderTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_READ_NAME = 'salesShipOrderV1';
    const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->shipmentRepository = $this->objectManager->get(
            \Magento\Sales\Api\ShipmentRepositoryInterface::class
        );
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_configurable_product.php
     */
    public function testConfigurableShipOrder()
    {
        $this->markTestIncomplete('https://github.com/magento-engcom/msi/issues/1335');
        $productsQuantity = 1;

        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

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
        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

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

        /** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
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

        /** @var \Magento\Sales\Model\Order $updatedOrder */
        $updatedOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

        $this->assertNotEquals(
            $existingOrder->getStatus(),
            $updatedOrder->getStatus(),
            'Failed asserting that Order status was changed'
        );
    }

    /**
     * Tests that not providing a tracking number produces the correct error. See MAGETWO-95429
     * @expectedException \Exception
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessageRegExp /Shipment Document Validation Error\(s\):(?:\n|\\n)Please enter a tracking number./
     * @codingStandardsIgnoreEnd
     * @magentoApiDataFixture Magento/Sales/_files/order_new.php
     */
    public function testShipOrderWithoutTrackingNumberReturnsError()
    {
        $this->_markTestAsRestOnly('SOAP requires an tracking number to be provided so this case is not possible.');

        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

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
        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

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

        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

        foreach ($existingOrder->getAllItems() as $item) {
            if ($item->getItemId() == $shippedItemId) {
                $this->assertEquals(1, $item->getQtyShipped());
                continue;
            }
            $this->assertEquals(0, $item->getQtyShipped());
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    private function getServiceInfo(\Magento\Sales\Model\Order $order)
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
}
