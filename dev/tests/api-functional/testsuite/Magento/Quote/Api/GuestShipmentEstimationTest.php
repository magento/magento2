<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api;

use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Quote\Api\Data\AddressInterface;

class GuestShipmentEstimationTest extends WebapiAbstract
{
    const SERVICE_VERSION = 'V1';
    const SERVICE_NAME = 'quoteGuestShipmentEstimationV1';
    const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_free_shipping.php
     * @magentoApiDataFixture Magento/Sales/_files/quote.php
     */
    public function testEstimateByExtendedAddress()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test01', 'reserved_order_id');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }

        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/guest-carts/' . $cartId . '/estimate-shipping-methods',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => 'V1',
                'operation' => self::SERVICE_NAME . 'EstimateByExtendedAddress',
            ],
        ];
        if (TESTS_WEB_API_ADAPTER == self::ADAPTER_SOAP) {
            /** @var \Magento\Quote\Model\Quote\Address $address */
            $address = $quote->getBillingAddress();

            $data = [
                AddressInterface::KEY_ID => (int)$address->getId(),
                AddressInterface::KEY_REGION => $address->getRegion(),
                AddressInterface::KEY_REGION_ID => $address->getRegionId(),
                AddressInterface::KEY_REGION_CODE => $address->getRegionCode(),
                AddressInterface::KEY_COUNTRY_ID => $address->getCountryId(),
                AddressInterface::KEY_STREET => $address->getStreet(),
                AddressInterface::KEY_COMPANY => $address->getCompany(),
                AddressInterface::KEY_TELEPHONE => $address->getTelephone(),
                AddressInterface::KEY_POSTCODE => $address->getPostcode(),
                AddressInterface::KEY_CITY => $address->getCity(),
                AddressInterface::KEY_FIRSTNAME => $address->getFirstname(),
                AddressInterface::KEY_LASTNAME => $address->getLastname(),
                AddressInterface::KEY_CUSTOMER_ID => $address->getCustomerId(),
                AddressInterface::KEY_EMAIL => $address->getEmail(),
                AddressInterface::SAME_AS_BILLING => $address->getSameAsBilling(),
                AddressInterface::CUSTOMER_ADDRESS_ID => $address->getCustomerAddressId(),
                AddressInterface::SAVE_IN_ADDRESS_BOOK => $address->getSaveInAddressBook(),
            ];

            $requestData = [
                'cartId' => $cartId,
                'address' => $data
            ];
        } else {
            $requestData = [
                'address' => [
                    'country_id' => "US",
                    'postcode' => null,
                    'region' => null,
                    'region_id' => null
                ],
            ];
        }
        // Cart must be anonymous (see fixture)
        $this->assertEmpty($quote->getCustomerId());

        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertNotEmpty($result);
        $this->assertEquals(1, count($result));
        foreach ($result as $rate) {
            $this->assertEquals("flatrate", $rate['carrier_code']);
            $this->assertEquals(0, $rate['amount']);
        }
    }
}
