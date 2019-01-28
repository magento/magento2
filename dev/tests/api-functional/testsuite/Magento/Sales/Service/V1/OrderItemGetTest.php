<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

class OrderItemGetTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/orders/items';

    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'salesOrderItemRepositoryV1';

    const ORDER_INCREMENT_ID = '100000001';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testGet()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId(self::ORDER_INCREMENT_ID);
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = current($order->getItems());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $orderItem->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'get',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['id' => $orderItem->getId()]);

        $this->assertTrue(is_array($response));
        $this->assertOrderItem($orderItem, $response);

        //check that nullable fields were marked as optional and were not sent
        foreach ($response as $fieldName => $value) {
            if ($fieldName == 'sku') {
                continue;
            }
            $this->assertNotNull($value);
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param array $response
     * @return void
     */
    protected function assertOrderItem(\Magento\Sales\Model\Order\Item $orderItem, array $response)
    {
        $this->assertSame($orderItem->getId(), $response['item_id']);
        $this->assertSame($orderItem->getOrderId(), $response['order_id']);
        $this->assertSame($orderItem->getProductId(), $response['product_id']);
        $this->assertSame($orderItem->getProductType(), $response['product_type']);
        $this->assertSame($orderItem->getBasePrice(), $response['base_price']);
        $this->assertSame($orderItem->getRowTotal(), $response['row_total']);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_with_discount.php
     */
    public function testGetOrderWithDiscount()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId(self::ORDER_INCREMENT_ID);
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = current($order->getItems());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $orderItem->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'get',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, ['id' => $orderItem->getId()]);

        $this->assertTrue(is_array($response));
        $this->assertSame(8.00, $response['row_total']);
        $this->assertSame(8.00, $response['base_row_total']);
        $this->assertSame(9.00, $response['row_total_incl_tax']);
        $this->assertSame(9.00, $response['base_row_total_incl_tax']);
    }
}
