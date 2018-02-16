<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class ShipmentCreateTest
 */
class ShipmentCreateTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/shipment';

    const SERVICE_READ_NAME = 'salesShipmentRepositoryV1';

    const SERVICE_VERSION = 'V1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }
    /**
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testInvoke()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('100000001');
        $orderItem = current($order->getAllItems());
        $items = [
            [
                'order_item_id' => $orderItem->getId(),
                'qty' => $orderItem->getQtyOrdered(),
                'additional_data' => null,
                'description' => null,
                'entity_id' => null,
                'name' => null,
                'parent_id' => null,
                'price' => null,
                'product_id' => null,
                'row_total' => null,
                'sku' => null,
                'weight' => null,
            ],
        ];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'save',
            ],
        ];
        $data = [
            'order_id' => $order->getId(),
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
                    'order_id' => $order->getId(),
                    'title' => 'ground',
                    'description' => null,
                    'track_number' => '12345678',
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
                    'comment' => 'Shipment-related comment.',
                    'is_customer_notified' => null,
                    'is_visible_on_front' => null,
                    'parent_id' => null
                ]
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['entity' => $data]);
        $this->assertNotEmpty($result);
    }
}
