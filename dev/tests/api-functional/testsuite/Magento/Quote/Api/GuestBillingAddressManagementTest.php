<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Quote\Api;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\WebapiAbstract;

class GuestBillingAddressManagementTest extends WebapiAbstract
{
    public const SERVICE_VERSION = 'V1';
    public const SERVICE_NAME = 'quoteGuestBillingAddressManagementV1';
    public const RESOURCE_PATH = '/V1/guest-carts/';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->addressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        $this->quoteIdMaskFactory = $this->objectManager->get(QuoteIdMaskFactory::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    protected function getQuoteMaskedId($quoteId)
    {
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->objectManager->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)->create();
        $quoteIdMask->load($quoteId, 'quote_id');
        return $quoteIdMask->getMaskedId();
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGetAddress()
    {
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');

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

        $cartId = $this->getQuoteMaskedId($quote->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/billing-address',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Get',
            ],
        ];

        $requestData = ["cartId" => $cartId];
        $response = $this->_webApiCall($serviceInfo, $requestData);

        asort($data);
        asort($response);
        $this->assertEquals($data, $response);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @dataProvider setAddressDataProvider
     */
    public function testSetAddress($useForShipping)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');

        $cartId = $this->getQuoteMaskedId($quote->getId());

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/billing-address',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Assign',
            ],
        ];

        $addressData = [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'email' => '',
            'company' => 'Magento Commerce Inc.',
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
            'cartId' => $cartId,
            'address' => $addressData,
            'useForShipping' => $useForShipping
        ];

        $addressId = $this->_webApiCall($serviceInfo, $requestData);

        //reset $quote to reload data
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_order_1', 'reserved_order_id');
        $address = $quote->getBillingAddress();
        $address->getRegionCode();
        $savedData  = $address->getData();
        $this->assertEquals($addressId, $savedData['address_id']);
        //custom checks for street, region and address_type
        foreach ($addressData['street'] as $streetLine) {
            $this->assertContains($streetLine, $quote->getBillingAddress()->getStreet());
        }
        unset($addressData['street']);
        unset($addressData['email']);
        $this->assertEquals('billing', $savedData['address_type']);
        //check the rest of fields
        foreach ($addressData as $key => $value) {
            $this->assertEquals($value, $savedData[$key]);
        }
        $address = $quote->getShippingAddress();
        $address->getRegionCode();
        $savedData = $address->getData();
        if ($useForShipping) {
            //check that shipping address set
            $this->assertEquals('shipping', $savedData['address_type']);
            $this->assertEquals(1, $savedData['same_as_billing']);
            //check the rest of fields
            foreach ($addressData as $key => $value) {
                $this->assertEquals($value, $savedData[$key]);
            }
        } else {
            $this->assertEquals(0, $savedData['same_as_billing']);
        }
    }

    public static function setAddressDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    #[
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$']),
    ]
    public function testSetAddressWithInvalidPostcodeAndTelephoneRequired()
    {
        $cart = $this->fixtures->get('cart');
        $maskedCartId = $this->getMaskedQuoteId($cart->getId());

        $invalidAddress = $this->createInvalidAddressWithEmptyPostcodeAndTelephone();

        try {
            $this->assignBillingAddress($maskedCartId, $invalidAddress);
            $this->fail('Expected exception not thrown');
        } catch (\Exception $e) {
            $this->assertBillingAddressValidationError($e);
        }
    }

    /**
     * Get masked quote ID
     *
     * @param string $quoteId
     * @return string
     */
    private function getMaskedQuoteId(string $quoteId): string
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $quoteIdMask->load($quoteId, 'quote_id');

        if (!$quoteIdMask->getMaskedId()) {
            $quoteIdMask->setQuoteId($quoteId);
            $quoteIdMask->setMaskedId(uniqid('masked_', true));
            $quoteIdMask->save();
        }

        return $quoteIdMask->getMaskedId();
    }

    /**
     * Create invalid address with empty postcode and telephone
     *
     * @return AddressInterface
     */
    private function createInvalidAddressWithEmptyPostcodeAndTelephone(): AddressInterface
    {
        $address = $this->addressFactory->create();
        $address->setData([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'country_id' => 'US',
            'region_id' => 12,
            'region' => 'California',
            'region_code' => 'CA',
            'street' => ['123 Test Street'],
            'city' => 'Test City',
            'postcode' => '',
            'telephone' => '',
        ]);
        return $address;
    }

    /**
     * Assign billing address to cart
     *
     * @param string $cartId
     * @param AddressInterface $address
     * @return int
     */
    private function assignBillingAddress(string $cartId, AddressInterface $address)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . $cartId . '/billing-address',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Assign',
            ],
        ];

        $requestData = [
            'cartId' => $cartId,
            'address' => $this->addressToArray($address),
        ];

        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Convert address object to array
     *
     * @param AddressInterface $address
     * @return array
     */
    private function addressToArray(AddressInterface $address): array
    {
        return [
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'email' => $address->getEmail(),
            'country_id' => $address->getCountryId(),
            'region_id' => $address->getRegionId(),
            'region' => $address->getRegion(),
            'region_code' => $address->getRegionCode(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'postcode' => $address->getPostcode(),
            'telephone' => $address->getTelephone(),
        ];
    }

    /**
     * Assert billing address validation error
     *
     * @param \Exception $exception
     * @return void
     */
    private function assertBillingAddressValidationError(\Exception $exception): void
    {
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
            $this->assertRestBillingAddressValidationError($exception);
        } elseif (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $this->assertSoapBillingAddressValidationError($exception);
        }
    }

    /**
     * Assert REST billing address validation error
     *
     * @param \Exception $exception
     * @return void
     */
    private function assertRestBillingAddressValidationError(\Exception $exception): void
    {
        $errorData = $this->processRestExceptionResult($exception);
        $this->assertStringContainsString('required', $errorData['message']);
    }

    /**
     * Assert SOAP billing address validation error
     *
     * @param \Exception $exception
     * @return void
     */
    private function assertSoapBillingAddressValidationError(\Exception $exception): void
    {
        $this->assertInstanceOf('SoapFault', $exception);
        $this->assertStringContainsString('required', $exception->getMessage());
    }
}
