<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test for hold order.
 */
class OrderHoldTest extends WebapiAbstract
{
    private const SERVICE_VERSION = 'V1';

    private const SERVICE_NAME = 'salesOrderManagementV1';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
    }

    /**
     * Test hold order and check order items product options after.
     *
     * @magentoApiDataFixture Magento/Sales/_files/order_with_two_configurable_variations.php
     *
     * @return void
     */
    public function testOrderHold(): void
    {
        $order = $this->objectManager->get(Order::class)
            ->loadByIncrementId('100000001');
        $orderId = $order->getId();
        $orderItemsProductOptions = $this->getOrderItemsProductOptions($order);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/orders/' . $orderId . '/hold',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'hold',
            ],
        ];
        $requestData = ['id' => $orderId];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($result);

        $this->assertEquals(
            $orderItemsProductOptions,
            $this->getOrderItemsProductOptions($this->orderRepository->get($orderId))
        );
    }

    /**
     * Return order items product options
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getOrderItemsProductOptions(OrderInterface $order): array
    {
        $result = [];
        foreach ($order->getItems() as $orderItem) {
            $result[] = $orderItem->getProductOptions();
        }

        return $result;
    }
}
