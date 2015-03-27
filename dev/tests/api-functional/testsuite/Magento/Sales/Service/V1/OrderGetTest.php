<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Service\V1;

use Magento\TestFramework\TestCase\WebapiAbstract;

class OrderGetTest extends WebapiAbstract
{
    const RESOURCE_PATH = '/V1/orders';

    const SERVICE_READ_NAME = 'salesOrderRepositoryV1';

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
     * @magentoApiDataFixture Magento/GiftMessage/_files/order_with_message.php
     */
    public function testOrderGet()
    {
        $expectedOrderData = [
            'base_subtotal' => '100.0000',
            'subtotal' => '100.0000',
            'customer_is_guest' => '1',
            'increment_id' => self::ORDER_INCREMENT_ID,
        ];
        $expectedPayments = ['method' => 'checkmo'];
        $expectedBillingAddressNotEmpty = [
            'city',
            'postcode',
            'lastname',
            'street',
            'region',
            'telephone',
            'country_id',
            'firstname',
        ];
        $expectedExtensionAttributes = [
            'gift_message' => [
                'sender' => 'Romeo',
                'recipient' => 'Mercutio',
                'message' => 'I thought all for the best.'
            ]
        ];

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order');
        $order->loadByIncrementId(self::ORDER_INCREMENT_ID);

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $order->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_READ_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_READ_NAME . 'get',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['id' => $order->getId()]);

        foreach ($expectedOrderData as $field => $value) {
            $this->assertArrayHasKey($field, $result);
            $this->assertEquals($value, $result[$field]);
        }

        $this->assertArrayHasKey('payments', $result);
        foreach ($expectedPayments as $field => $value) {
            $paymentsKey = key($result['payments']);
            $this->assertArrayHasKey($field, $result['payments'][$paymentsKey]);
            $this->assertEquals($value, $result['payments'][$paymentsKey][$field]);
        }

        $this->assertArrayHasKey('billing_address', $result);
        $this->assertArrayHasKey('shipping_address', $result);
        foreach ($expectedBillingAddressNotEmpty as $field) {
            $this->assertArrayHasKey($field, $result['billing_address']);
            $this->assertArrayHasKey($field, $result['shipping_address']);
        }

        $this->assertArrayHasKey('gift_message', $result['extension_attributes']);
        $expectedGiftMessage = $expectedExtensionAttributes['gift_message'];
        $actualGiftMessage = $result['extension_attributes']['gift_message'];
        $this->assertEquals($expectedGiftMessage['sender'], $actualGiftMessage['sender']);
        $this->assertEquals($expectedGiftMessage['recipient'], $actualGiftMessage['recipient']);
        $this->assertEquals($expectedGiftMessage['message'], $actualGiftMessage['message']);

        $this->assertArrayHasKey('gift_message', $result['items'][0]['extension_attributes']);
        $expectedGiftMessage = $expectedExtensionAttributes['gift_message'];
        $actualGiftMessage = $result['items'][0]['extension_attributes']['gift_message'];
        $this->assertEquals($expectedGiftMessage['sender'], $actualGiftMessage['sender']);
        $this->assertEquals($expectedGiftMessage['recipient'], $actualGiftMessage['recipient']);
        $this->assertEquals($expectedGiftMessage['message'], $actualGiftMessage['message']);
    }
}
