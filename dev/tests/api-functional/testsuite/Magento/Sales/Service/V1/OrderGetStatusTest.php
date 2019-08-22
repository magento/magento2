<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class OrderGetStatusTest
 * @package Magento\Sales\Service\V1
 */
class OrderGetStatusTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/orders/%d/statuses';

    const SERVICE_READ_NAME = 'salesOrderManagementV1';

    const SERVICE_VERSION = 'V1';

    const ORDER_INCREMENT_ID = '100000001';

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
    public function testOrderGetStatus()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId(self::ORDER_INCREMENT_ID);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(self::RESOURCE_PATH, $order->getId()),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'getStatus',
            ],
        ];

        $this->assertEquals($order->getStatus(), $this->_webApiCall($serviceInfo, ['id' => $order->getId()]));
    }
}
