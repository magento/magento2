<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class ReadServiceTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'giftMessageReadServiceV1';
    const RESOURCE_PATH = '/V1/carts/';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_message.php
     */
    public function testGet()
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('message_order_21', 'reserved_order_id');

        $cartId = $quote->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/gift-message',
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $expectedMessage = [
            'recipient' => 'Mercutio',
            'sender' => 'Romeo',
            'message' => 'I thought all for the best.',
        ];

        $requestData = ["cartId" => $cartId];
        $resultMessage = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertCount(5, $resultMessage);
        unset($resultMessage['gift_message_id']);
        unset($resultMessage['customer_id']);
        $this->assertEquals($expectedMessage, $resultMessage);
    }

    /**
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_item_message.php
     */
    public function testGetItemMessage()
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test_order_item_with_message', 'reserved_order_id');
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $product->load($product->getIdBySku('simple_with_message'));
        $itemId = $quote->getItemByProduct($product)->getId();
        /** @var  \Magento\Catalog\Model\Product $product */
        $cartId = $quote->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/gift-message/' . $itemId,
                'httpMethod' => RestConfig::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'getItemMessage',
            ],
        ];

        $expectedMessage = [
            'recipient' => 'Jane Roe',
            'sender' => 'John Doe',
            'message' => 'Gift Message Text',
        ];

        $requestData = ["cartId" => $cartId, "itemId" => $itemId];
        $resultMessage = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertCount(5, $resultMessage);
        unset($resultMessage['gift_message_id']);
        unset($resultMessage['customer_id']);
        $this->assertEquals($expectedMessage, $resultMessage);
    }
}
