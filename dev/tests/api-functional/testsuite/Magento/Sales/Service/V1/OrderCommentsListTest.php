<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class OrderCommentsListTest extends WebapiAbstract
{
    const SERVICE_NAME = 'salesOrderManagementV1';

    const SERVICE_VERSION = 'V1';

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderCommentsList()
    {
        $comment = 'Test comment';
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $objectManager->get('Magento\Sales\Model\Order')->loadByIncrementId('100000001');
        $history = $order->addStatusHistoryComment($comment, $order->getStatus());
        $history->save();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/order/' . $order->getId() . '/comments',
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getCommentsList',
            ],
        ];
        $requestData = ['id' => $order->getId()];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        foreach ($result['items'] as $history) {
            $orderHistoryStatus = $objectManager->get('Magento\Sales\Model\Order\Status\History')
                ->load($history['entity_id']);
            $this->assertEquals($orderHistoryStatus->getComment(), $history['comment']);
            $this->assertEquals($orderHistoryStatus->getStatus(), $history['status']);
        }
    }
}
