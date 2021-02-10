<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Service\V1;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ShipmentCreateTest
 *
 * Test shipment save API
 */
class ShipmentCreateTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/shipment';

    const SERVICE_READ_NAME = 'salesShipmentRepositoryV1';

    const SERVICE_VERSION = 'V1';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test save shipment return valid result with multiple tracks with multiple comments
     *
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testInvokeWithMultipleTrackAndComments()
    {
        $data = $this->getEntityData();
        $result = $this->_webApiCall(
            $this->getServiceInfo(),
            [
                'entity' => $data['shipment data with multiple tracking and multiple comments']]
        );
        $this->assertNotEmpty($result);
        $this->assertEquals(3, count($result['tracks']));
        $this->assertEquals(3, count($result['comments']));
    }

    /**
     * Test save shipment return valid result with multiple tracks with no comments
     *
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testInvokeWithMultipleTrackAndNoComments()
    {
        $data = $this->getEntityData();
        $result = $this->_webApiCall(
            $this->getServiceInfo(),
            [
                'entity' => $data['shipment data with multiple tracking']]
        );
        $this->assertNotEmpty($result);
        $this->assertEquals(3, count($result['tracks']));
        $this->assertEquals(0, count($result['comments']));
    }

    /**
     * Test save shipment return valid result with no tracks with multiple comments
     *
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testInvokeWithNoTrackAndMultipleComments()
    {
        $data = $this->getEntityData();
        $result = $this->_webApiCall(
            $this->getServiceInfo(),
            [
                'entity' => $data['shipment data with multiple comments']]
        );
        $this->assertNotEmpty($result);
        $this->assertEquals(0, count($result['tracks']));
        $this->assertEquals(3, count($result['comments']));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getEntityData()
    {
        $existingOrder = $this->getOrder('100000001');
        $orderItem = current($existingOrder->getAllItems());

        $items = [
            [
                'order_item_id' => $orderItem->getId(),
                'qty' => $orderItem->getQtyOrdered(),
                'additional_data' => null,
                'description' => null,
                'entity_id' => 1,
                'name' => null,
                'parent_id' => null,
                'price' => null,
                'product_id' => null,
                'row_total' => null,
                'sku' => 'simple',
                'weight' => null,
            ],
        ];
        return [
            'shipment data with multiple tracking and multiple comments' => [
                'order_id' => $existingOrder->getId(),
                'entity_id' => null,
                'store_id' => null,
                'total_weight' => null,
                'total_qty' => null,
                'email_sent' => null,
                'customer_id' => null,
                'shipping_address_id' => null,
                'billing_address_id' => null,
                'shipment_status' => null,
                'increment_id' => null,
                'created_at' => null,
                'updated_at' => null,
                'shipping_label' => null,
                'tracks' => [
                    [
                        'carrier_code' => 'UPS',
                        'order_id' => $existingOrder->getId(),
                        'title' => 'ground',
                        'description' => null,
                        'track_number' => '12345678',
                        'parent_id' => null,
                        'created_at' => null,
                        'updated_at' => null,
                        'qty' => null,
                        'weight' => null
                    ],
                    [
                        'carrier_code' => 'UPS',
                        'order_id' => $existingOrder->getId(),
                        'title' => 'ground',
                        'description' => null,
                        'track_number' => '654563221',
                        'parent_id' => null,
                        'created_at' => null,
                        'updated_at' => null,
                        'qty' => null,
                        'weight' => null
                    ],
                    [
                        'carrier_code' => 'USPS',
                        'order_id' => $existingOrder->getId(),
                        'title' => 'ground',
                        'description' => null,
                        'track_number' => '789654565',
                        'parent_id' => null,
                        'created_at' => null,
                        'updated_at' => null,
                        'qty' => null,
                        'weight' => null
                    ]
                ],
                'items' => $items,
                'comments' => [
                    [
                        'comment' => 'Shipment-related comment-1.',
                        'is_customer_notified' => null,
                        'is_visible_on_front' => null,
                        'parent_id' => null
                    ],
                    [
                        'comment' => 'Shipment-related comment-2.',
                        'is_customer_notified' => null,
                        'is_visible_on_front' => null,
                        'parent_id' => null
                    ],
                    [
                        'comment' => 'Shipment-related comment-3.',
                        'is_customer_notified' => null,
                        'is_visible_on_front' => null,
                        'parent_id' => null
                    ]

                ]
            ],
            'shipment data with multiple tracking' => [
                'order_id' => $existingOrder->getId(),
                'entity_id' => null,
                'store_id' => null,
                'total_weight' => null,
                'total_qty' => null,
                'email_sent' => null,
                'customer_id' => null,
                'shipping_address_id' => null,
                'billing_address_id' => null,
                'shipment_status' => null,
                'increment_id' => null,
                'created_at' => null,
                'updated_at' => null,
                'shipping_label' => null,
                'tracks' => [
                    [
                        'carrier_code' => 'UPS',
                        'order_id' => $existingOrder->getId(),
                        'title' => 'ground',
                        'description' => null,
                        'track_number' => '12345678',
                        'parent_id' => null,
                        'created_at' => null,
                        'updated_at' => null,
                        'qty' => null,
                        'weight' => null
                    ],
                    [
                        'carrier_code' => 'UPS',
                        'order_id' => $existingOrder->getId(),
                        'title' => 'ground',
                        'description' => null,
                        'track_number' => '654563221',
                        'parent_id' => null,
                        'created_at' => null,
                        'updated_at' => null,
                        'qty' => null,
                        'weight' => null
                    ],
                    [
                        'carrier_code' => 'USPS',
                        'order_id' => $existingOrder->getId(),
                        'title' => 'ground',
                        'description' => null,
                        'track_number' => '789654565',
                        'parent_id' => null,
                        'created_at' => null,
                        'updated_at' => null,
                        'qty' => null,
                        'weight' => null
                    ]
                ],
                'items' => $items,
                'comments' => []
            ],
            'shipment data with multiple comments' => [
                'order_id' => $existingOrder->getId(),
                'entity_id' => null,
                'store_id' => null,
                'total_weight' => null,
                'total_qty' => null,
                'email_sent' => null,
                'customer_id' => null,
                'shipping_address_id' => null,
                'billing_address_id' => null,
                'shipment_status' => null,
                'increment_id' => null,
                'created_at' => null,
                'updated_at' => null,
                'shipping_label' => null,
                'tracks' => [],
                'items' => $items,
                'comments' => [
                    [
                        'comment' => 'Shipment-related comment-1.',
                        'is_customer_notified' => null,
                        'is_visible_on_front' => null,
                        'parent_id' => null
                    ],
                    [
                        'comment' => 'Shipment-related comment-2.',
                        'is_customer_notified' => null,
                        'is_visible_on_front' => null,
                        'parent_id' => null
                    ],
                    [
                        'comment' => 'Shipment-related comment-3.',
                        'is_customer_notified' => null,
                        'is_visible_on_front' => null,
                        'parent_id' => null
                    ]

                ]
            ]
        ];
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
     * @return array
     */
    private function getServiceInfo(): array
    {
        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'save',
            ],
        ];
    }
}
