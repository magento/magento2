<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Service\V1;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order\Interceptor;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Canceling of the order
 */
class OrderCancelTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'salesOrderManagementV1';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Gets order by increment ID.
     *
     * @param string $incrementId
     * @return Interceptor
     */
    private function getOrder(string $incrementId): Interceptor
    {
        return $this->objectManager->get(\Magento\Sales\Model\Order::class)->loadByIncrementId($incrementId);
    }

    /**
     * Send API request for canceling the order
     *
     * @param object $order
     * @return array|bool|float|int|string
     */
    private function sendCancelRequest($order)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/orders/' . $order->getId() . '/cancel',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'cancel',
            ],
        ];
        $requestData = ['id' => $order->getId()];
        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderCancel()
    {
        $order = $this->getOrder('100000001');
        $result = $this->sendCancelRequest($order);
        $this->assertTrue($result);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_state_hold.php
     */
    public function testOrderWithStateHoldedShouldNotBeCanceled()
    {
        $order = $this->getOrder('100000001');
        $result = $this->sendCancelRequest($order);
        $this->assertFalse($result);
    }
}
