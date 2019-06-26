<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Test order updating via webapi
 */
class OrderUpdateTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/orders';

    const SERVICE_NAME = 'salesOrderRepositoryV1';

    const SERVICE_VERSION = 'V1';

    const ORDER_INCREMENT_ID = '100000001';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Check order increment id after updating via webapi
     *
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderUpdate()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->get(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId(self::ORDER_INCREMENT_ID);

        $entityData = $this->getOrderData($order);

        $requestData = ['entity' => $entityData];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/orders',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'save',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertGreaterThan(1, count($result));

        /** @var \Magento\Sales\Model\Order $actualOrder */
        $actualOrder = $this->objectManager->get(\Magento\Sales\Model\Order::class)->load($order->getId());
        $this->assertEquals(
            $order->getData(OrderInterface::INCREMENT_ID),
            $actualOrder->getData(OrderInterface::INCREMENT_ID)
        );
    }

    /**
     * Prepare order data for request
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    private function getOrderData(\Magento\Sales\Model\Order $order)
    {
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            $entityData = $order->getData();
            unset($entityData[OrderInterface::INCREMENT_ID]);
            $entityData[OrderInterface::STATE] = 'processing';
            $entityData[OrderInterface::STATUS] = 'processing';

            $orderData = $order->getData();
            $orderData['billing_address'] = $order->getBillingAddress()->getData();
            $orderData['billing_address']['street'] = ['Street'];

            $orderItems = [];
            foreach ($order->getItems() as $item) {
                $orderItems[] = $item->getData();
            }
            $orderData['items'] = $orderItems;

            $shippingAddress = $order->getShippingAddress()->getData();
            $orderData['extension_attributes']['shipping_assignments'] =
                [
                    [
                        'shipping' => [
                            'address' => $shippingAddress,
                            'method' => 'flatrate_flatrate'
                        ],
                        'items' => $order->getItems(),
                        'stock_id' => null,
                    ]
                ];
        } else {
            $orderData = [
                OrderInterface::ENTITY_ID => $order->getId(),
                OrderInterface::STATE => 'processing',
                OrderInterface::STATUS => 'processing'
            ];
        }
        return $orderData;
    }
}
