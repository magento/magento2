<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;

/**
 * Test GuestShippingInformationManagement API validation.
 */
class GuestShippingInformationManagementValidationTest extends WebapiAbstract
{
    private const SERVICE_VERSION = 'V1';
    private const SERVICE_NAME = 'checkoutGuestShippingInformationManagementV1';
    private const RESOURCE_PATH = '/V1/guest-carts/%s/shipping-information';

    /**
     * @var ShippingInformationInterfaceFactory
     */
    private $shippingInformationFactory;

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
        parent::setUp();
        $this->shippingInformationFactory = Bootstrap::getObjectManager()
            ->get(ShippingInformationInterfaceFactory::class);
        $this->addressFactory = Bootstrap::getObjectManager()->get(AddressInterfaceFactory::class);
        $this->quoteIdMaskFactory = Bootstrap::getObjectManager()->get(QuoteIdMaskFactory::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
    ]
    /**
     * Test successful validation with valid address data
     */
    public function testSaveAddressInformationWithValidData()
    {
        $cart = $this->fixtures->get('cart');
        $cartId = $cart->getId();
        $maskedCartId = $this->getMaskedCartId($cartId);
        $shippingAddress = $this->addressFactory->create();
        $shippingAddress->setData([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'country_id' => 'US',
            'region_id' => 12,
            'region' => 'California',
            'region_code' => 'CA',
            'street' => ['123 Test Street'],
            'city' => 'Test City',
            'postcode' => '90210',
            'telephone' => '1234567890',
        ]);
        $billingAddress = $this->addressFactory->create();
        $billingAddress->setData([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'country_id' => 'US',
            'region_id' => 12,
            'region' => 'California',
            'region_code' => 'CA',
            'street' => ['123 Test Street'],
            'city' => 'Test City',
            'postcode' => '90210',
            'telephone' => '1234567890',
        ]);
        $shippingInformation = $this->shippingInformationFactory->create();
        $shippingInformation->setShippingAddress($shippingAddress);
        $shippingInformation->setBillingAddress($billingAddress);
        $shippingInformation->setShippingMethodCode('flatrate');
        $shippingInformation->setShippingCarrierCode('flatrate');
        $result = $this->callSaveAddressInformation($maskedCartId, $shippingInformation);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('payment_methods', $result);
        $this->assertArrayHasKey('totals', $result);
    }

    /**
     * Get masked cart ID for the given quote
     *
     * @param string $cartId
     * @return string
     */
    private function getMaskedCartId(string $cartId): string
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create();
        $quoteIdMask->load($cartId, 'quote_id');

        if (!$quoteIdMask->getMaskedId()) {
            $quoteIdMask->setQuoteId($cartId);
            $quoteIdMask->setMaskedId(uniqid('masked_', true));
            $quoteIdMask->save();
        }

        return $quoteIdMask->getMaskedId();
    }

    /**
     * Call the saveAddressInformation API
     *
     * @param string $cartId
     * @param ShippingInformationInterface $shippingInformation
     * @return array
     */
    private function callSaveAddressInformation(
        string $cartId,
        ShippingInformationInterface $shippingInformation
    ): array {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => sprintf(self::RESOURCE_PATH, $cartId),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'saveAddressInformation',
                'serviceVersion' => self::SERVICE_VERSION,
            ],
        ];
        $requestData = [
            'cart_id' => $cartId,
            'addressInformation' => [
                'shipping_address' => $this->addressToArray($shippingInformation->getShippingAddress()),
                'billing_address' => $this->addressToArray($shippingInformation->getBillingAddress()),
                'shipping_method_code' => $shippingInformation->getShippingMethodCode(),
                'shipping_carrier_code' => $shippingInformation->getShippingCarrierCode(),
            ],
        ];

        return $this->_webApiCall($serviceInfo, $requestData);
    }

    #[
        DataFixture(ProductFixture::class, as: 'p1'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testSaveAddressInformationWithInvalidBillingAddressPostcodeAndTelephoneRequired()
    {
        $cart = $this->fixtures->get('cart');
        $maskedCartId = $this->getMaskedCartId($cart->getId());

        $shippingAddress = $this->createValidAddress();
        $billingAddress = $this->createInvalidAddressWithEmptyPostcodeAndTelephone();
        $shippingInformation = $this->createShippingInformation($shippingAddress, $billingAddress);

        try {
            $this->callSaveAddressInformation($maskedCartId, $shippingInformation);
            $this->fail('Expected exception not thrown');
        } catch (\Exception $e) {
            $this->assertValidationError($e);
        }
    }

    /**
     * Convert address object to array for API call
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
     * Create valid address data
     *
     * @return AddressInterface
     */
    private function createValidAddress(): AddressInterface
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
            'postcode' => '90210',
            'telephone' => '1234567890',
        ]);
        return $address;
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
     * Create shipping information
     *
     * @param AddressInterface $shippingAddress
     * @param AddressInterface $billingAddress
     * @return ShippingInformationInterface
     */
    private function createShippingInformation(
        AddressInterface $shippingAddress,
        AddressInterface $billingAddress
    ): ShippingInformationInterface {
        $shippingInformation = $this->shippingInformationFactory->create();
        $shippingInformation->setShippingAddress($shippingAddress);
        $shippingInformation->setBillingAddress($billingAddress);
        $shippingInformation->setShippingMethodCode('flatrate');
        $shippingInformation->setShippingCarrierCode('flatrate');
        return $shippingInformation;
    }

    /**
     * Assert validation error for REST or SOAP
     *
     * @param \Exception $exception
     * @return void
     */
    private function assertValidationError(\Exception $exception): void
    {
        if (TESTS_WEB_API_ADAPTER === self::ADAPTER_REST) {
            $this->assertRestValidationError($exception);
        } elseif (TESTS_WEB_API_ADAPTER === self::ADAPTER_SOAP) {
            $this->assertSoapValidationError($exception);
        }
    }

    /**
     * Assert REST validation error
     *
     * @param \Exception $exception
     * @return void
     */
    private function assertRestValidationError(\Exception $exception): void
    {
        $errorData = $this->processRestExceptionResult($exception);
        $this->assertEquals(
            'The shipping information was unable to be saved. Error: "%message"',
            $errorData['message']
        );
        $this->assertArrayHasKey('parameters', $errorData);
        $this->assertArrayHasKey('message', $errorData['parameters']);
        $this->assertStringContainsString('billing address contains invalid data', $errorData['parameters']['message']);
    }

    /**
     * Assert SOAP validation error
     *
     * @param \Exception $exception
     * @return void
     */
    private function assertSoapValidationError(\Exception $exception): void
    {
        $this->assertInstanceOf('SoapFault', $exception);
        $this->assertStringContainsString(
            'The shipping information was unable to be saved. Error: "%message"',
            $exception->getMessage()
        );
        $this->assertObjectHasProperty('detail', $exception);
        $this->assertSoapFaultParameters($exception);
    }

    /**
     * Assert SOAP fault parameters
     *
     * @param \SoapFault $soapFault
     * @return void
     */
    private function assertSoapFaultParameters(\SoapFault $soapFault): void
    {
        if (!isset($soapFault->detail->GenericFault->Parameters)) {
            return;
        }

        $parameters = $soapFault->detail->GenericFault->Parameters->GenericFaultParameter;
        $messageParam = $this->extractMessageParameter($parameters);

        $this->assertNotNull($messageParam, 'Message parameter should be present in SOAP fault');
        $this->assertStringContainsString(
            'billing address contains invalid data',
            (string)$messageParam
        );
    }

    /**
     * Extract message parameter from SOAP fault parameters
     *
     * @param mixed $parameters
     * @return mixed|null
     */
    private function extractMessageParameter($parameters)
    {
        if (is_array($parameters)) {
            foreach ($parameters as $param) {
                if ($param->key === 'message') {
                    return $param->value;
                }
            }
        } elseif (isset($parameters->key) && $parameters->key === 'message') {
            return $parameters->value;
        }
        return null;
    }
}
