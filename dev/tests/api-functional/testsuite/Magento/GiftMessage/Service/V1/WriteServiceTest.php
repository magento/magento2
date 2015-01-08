<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\GiftMessage\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Webapi\Model\Rest\Config as RestConfig;

class WriteServiceTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'giftMessageWriteServiceV1';
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
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_item_message.php
     */
    public function testSetForQuote()
    {
        // sales/gift_options/allow_order must be set to 1 in system configuration
        // @todo remove next statement when \Magento\TestFramework\TestCase\WebapiAbstract::_updateAppConfig is fixed
        $this->markTestIncomplete('This test relies on system configuration state.');
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test_order_item_with_message', 'reserved_order_id');

        $cartId = $quote->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/gift-message',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'SetForQuote',
            ],
        ];

        $requestData = [
            'cartId' => $cartId,
            'giftMessage' => [
                'recipient' => 'John Doe',
                'sender' => 'Jane Roe',
                'message' => 'Gift Message Text New',
            ],
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
        $quote->load('test_order_item_with_message', 'reserved_order_id');
        $quote->getGiftMessageId();
        /** @var  \Magento\GiftMessage\Model\Message $message */
        $message = $this->objectManager->create('Magento\GiftMessage\Model\Message')->load($quote->getGiftMessageId());
        $this->assertEquals('John Doe', $message->getRecipient());
        $this->assertEquals('Jane Roe', $message->getSender());
        $this->assertEquals('Gift Message Text New', $message->getMessage());
    }

    /**
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_item_message.php
     */
    public function testSetForItem()
    {
        // sales/gift_options/allow_items must be set to 1 in system configuration
        // @todo remove next statement when \Magento\TestFramework\TestCase\WebapiAbstract::_updateAppConfig is fixed
        $this->markTestIncomplete('This test relies on system configuration state.');
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Sales\Model\Quote');
        $quote->load('test_order_item_with_message', 'reserved_order_id');
        $cartId = $quote->getId();
        $product = $this->objectManager->create('Magento\Catalog\Model\Product');
        $product->load($product->getIdBySku('simple_with_message'));
        $itemId = $quote->getItemByProduct($product)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/gift-message/' .  $itemId,
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'SetForItem',
            ],
        ];

        $requestData = [
            'cartId' => $cartId,
            'itemId' => $itemId,
            'giftMessage' => [
                'recipient' => 'John Doe',
                'sender' => 'Jane Roe',
                'message' => 'Gift Message Text New',
            ],
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
//        $quote->load('test_order_item_with_message', 'reserved_order_id');
        $messageId = $quote->getItemByProduct($product)->getGiftMessageId();
        /** @var  \Magento\GiftMessage\Model\Message $message */
        $message = $this->objectManager->create('Magento\GiftMessage\Model\Message')->load($messageId);
        $this->assertEquals('John Doe', $message->getRecipient());
        $this->assertEquals('Jane Roe', $message->getSender());
        $this->assertEquals('Gift Message Text New', $message->getMessage());
    }
}
