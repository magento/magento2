<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class ItemRepositoryTest extends WebapiAbstract
{
    public const SERVICE_VERSION = 'V1';
    public const SERVICE_NAME = 'giftMessageItemRepositoryV1';
    public const RESOURCE_PATH = '/V1/carts/';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_item_message.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testGet()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_item_with_message', 'reserved_order_id');
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load($product->getIdBySku('simple_with_message'));
        $itemId = $quote->getItemByProduct($product)->getId();
        /** @var  \Magento\Catalog\Model\Product $product */
        $cartId = $quote->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/gift-message/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
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

    /**
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_item_message.php
     */
    public function testGetForMyCart()
    {
        $this->_markTestAsRestOnly();

        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_item_with_message', 'reserved_order_id');
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load($product->getIdBySku('simple_with_message'));
        $itemId = $quote->getItemByProduct($product)->getId();
        /** @var  \Magento\Catalog\Model\Product $product */
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'mine/gift-message/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $token,
            ],
        ];

        $expectedMessage = [
            'recipient' => 'Jane Roe',
            'sender' => 'John Doe',
            'message' => 'Gift Message Text',
        ];

        $requestData = ["itemId" => $itemId];
        $resultMessage = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertCount(5, $resultMessage);
        unset($resultMessage['gift_message_id']);
        unset($resultMessage['customer_id']);
        $this->assertEquals($expectedMessage, $resultMessage);
    }

    /**
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_item_message.php
     */
    public function testSave()
    {
        $this->markTestSkipped('This test relies on system configuration state.');
        // sales/gift_options/allow_items must be set to 1 in system configuration
        // @todo remove above statement when \Magento\TestFramework\TestCase\WebapiAbstract::_updateAppConfig is fixed

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_item_with_message', 'reserved_order_id');
        $cartId = $quote->getId();
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load($product->getIdBySku('simple_with_message'));
        $itemId = $quote->getItemByProduct($product)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/gift-message/' .  $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
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
        $messageId = $quote->getItemByProduct($product)->getGiftMessageId();
        /** @var  \Magento\GiftMessage\Model\Message $message */
        $message = $this->objectManager->create(\Magento\GiftMessage\Model\Message::class)->load($messageId);
        $this->assertEquals('John Doe', $message->getRecipient());
        $this->assertEquals('Jane Roe', $message->getSender());
        $this->assertEquals('Gift Message Text New', $message->getMessage());
    }

    /**
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_item_message.php
     */
    public function testSaveForMyCart()
    {
        $this->_markTestAsRestOnly();

        // get customer ID token
        /** @var \Magento\Integration\Api\CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(
            \Magento\Integration\Api\CustomerTokenServiceInterface::class
        );
        $token = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        $this->markTestSkipped('This test relies on system configuration state.');
        // sales/gift_options/allow_items must be set to 1 in system configuration
        // @todo remove above statement when \Magento\TestFramework\TestCase\WebapiAbstract::_updateAppConfig is fixed

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_item_with_message', 'reserved_order_id');
        $product = $this->objectManager->create(\Magento\Catalog\Model\Product::class);
        $product->load($product->getIdBySku('simple_with_message'));
        $itemId = $quote->getItemByProduct($product)->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'mine/gift-message/' .  $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                'token' => $token,
            ],
        ];

        $requestData = [
            'itemId' => $itemId,
            'giftMessage' => [
                'recipient' => 'John Doe',
                'sender' => 'Jane Roe',
                'message' => 'Gift Message Text New',
            ],
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));
        $messageId = $quote->getItemByProduct($product)->getGiftMessageId();
        /** @var  \Magento\GiftMessage\Model\Message $message */
        $message = $this->objectManager->create(\Magento\GiftMessage\Model\Message::class)->load($messageId);
        $this->assertEquals('John Doe', $message->getRecipient());
        $this->assertEquals('Jane Roe', $message->getSender());
        $this->assertEquals('Gift Message Text New', $message->getMessage());
    }
}
