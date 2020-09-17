<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class CartRepositoryTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'giftMessageCartRepositoryV1';
    const RESOURCE_PATH = '/V1/carts/';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_message.php
     * @magentoAppIsolation enabled
     * @magentoDbIsolation disabled
     */
    public function testGet()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('message_order_21', 'reserved_order_id');

        $cartId = $quote->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/gift-message',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
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
     * @magentoApiDataFixture Magento/GiftMessage/_files/quote_with_customer_and_message.php
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

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'mine/gift-message',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
                'token' => $token,
            ],
        ];

        $expectedMessage = [
            'recipient' => 'Mercutio',
            'sender' => 'Romeo',
            'message' => 'I thought all for the best.',
        ];

        $requestData = [];
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
        // sales/gift_options/allow_order must be set to 1 in system configuration
        // @todo remove next statement when \Magento\TestFramework\TestCase\WebapiAbstract::_updateAppConfig is fixed
        $this->markTestIncomplete('This test relies on system configuration state.');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_item_with_message', 'reserved_order_id');

        $cartId = $quote->getId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/gift-message',
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
        $message = $this->objectManager->create(\Magento\GiftMessage\Model\Message::class)
            ->load($quote->getGiftMessageId());
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

        // sales/gift_options/allow_order must be set to 1 in system configuration
        // @todo remove next statement when \Magento\TestFramework\TestCase\WebapiAbstract::_updateAppConfig is fixed
        $this->markTestIncomplete('This test relies on system configuration state.');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'mine/gift-message',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
                'token' => $token,
            ],
        ];

        $requestData = [
            'giftMessage' => [
                'recipient' => 'John Doe',
                'sender' => 'Jane Roe',
                'message' => 'Gift Message Text New',
            ],
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, $requestData));

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_item_with_message', 'reserved_order_id');
        $quote->getGiftMessageId();
        /** @var  \Magento\GiftMessage\Model\Message $message */
        $message = $this->objectManager->create(\Magento\GiftMessage\Model\Message::class)
            ->load($quote->getGiftMessageId());
        $this->assertEquals('John Doe', $message->getRecipient());
        $this->assertEquals('Jane Roe', $message->getSender());
        $this->assertEquals('Gift Message Text New', $message->getMessage());
    }
}
