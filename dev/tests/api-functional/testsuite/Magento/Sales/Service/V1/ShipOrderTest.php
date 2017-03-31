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
     * @magentoApiDataFixture Magento/Sales/_files/order_new.php
     */
    public function testShipOrder()
    {
        /** @var \Magento\Sales\Model\Order $existingOrder */
        $existingOrder = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/order/' . $existingOrder->getId() . '/ship',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'execute',
            ],
        ];

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

        $result = $this->_webApiCall($serviceInfo, $requestData);

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
}
