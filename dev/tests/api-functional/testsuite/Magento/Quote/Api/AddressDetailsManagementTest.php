<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

class AddressDetailsManagementTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteAddressDetailsManagementV1';
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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testSaveAddresses()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote');
        $quote->load('test_order_1', 'reserved_order_id');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . 'mine/addresses',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'SaveAddresses',
            ],
        ];

        $addressData = [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'email' => 'cat@dog.com',
            'company' => 'eBay Inc',
            'street' => ['Typical Street', 'Tiny House 18'],
            'city' => 'Big City',
            'region_id' => 12,
            'region' => 'California',
            'region_code' => 'CA',
            'postcode' => '0985432',
            'country_id' => 'US',
            'telephone' => '88776655',
            'fax' => '44332255',
        ];
        $requestData = [
            'cart_id' => $quote->getId(),
            'billingAddress' => $addressData,
            'shippingAddress' => $addressData
        ];

        $response = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertArrayHasKey('shipping_methods', $response);
        $this->assertCount(1, $response['shipping_methods']);
        $this->assertEquals('flatrate', $response['shipping_methods'][0]['method_code']);

        $this->assertArrayHasKey('payment_methods', $response);
        $this->assertCount(2, $response['payment_methods']);
        $this->assertEquals('checkmo', $response['payment_methods'][0]['code']);
    }
}
