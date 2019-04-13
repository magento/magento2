<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Ups;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Quote\GetQuoteShippingAddressIdByReservedQuoteId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setting "UPS" shipping method on cart. Current class covers the next UPS shipping methods:
 *
 * | Code      | Label
 * --------------------------------------
 * | 1DM       | Next Day Air Early AM
 * | 1DA       | Next Day Air
 * | 2DA       | 2nd Day Air
 * | 3DS       | 3 Day Select
 * | GND       | Ground
 * | STD       | Canada Standard
 * | XPR       | Worldwide Express
 * | WXS       | Worldwide Express Saver
 * | XDM       | Worldwide Express Plus
 * | XPD       | Worldwide Expedited
 *
 * Current class does not cover these UPS shipping methods (depends on sandbox settings)
 *
 * | Code      | Label
 * --------------------------------------
 * | 1DML      | Next Day Air Early AM Letter
 * | 1DAL      | Next Day Air Letter
 * | 1DAPI     | Next Day Air Intra (Puerto Rico)
 * | 1DP       | Next Day Air Saver
 * | 1DPL      | Next Day Air Saver Letter
 * | 2DM       | 2nd Day Air AM
 * | 2DML      | 2nd Day Air AM Letter
 * | 2DAL      | 2nd Day Air Letter
 * | GNDCOM    | Ground Commercial
 * | GNDRES    | Ground Residential
 * | XPRL      | Worldwide Express Letter
 * | XDML      | Worldwide Express Plus Letter
 */
class SetUpsShippingMethodsOnCartTest extends GraphQlAbstract
{
    /**
     * Defines carrier label for "UPS" shipping method
     */
    const CARRIER_LABEL = 'United Parcel Service';

    /**
     * Defines carrier code for "UPS" shipping method
     */
    const CARRIER_CODE = 'ups';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var GetQuoteShippingAddressIdByReservedQuoteId
     */
    private $getQuoteShippingAddressIdByReservedQuoteId;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->getQuoteShippingAddressIdByReservedQuoteId = $objectManager->get(GetQuoteShippingAddressIdByReservedQuoteId::class);
    }

    /**
     * Set "Next Day Air Early AM" UPS shipping method
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     */
    public function testSetNextDayAirEarlyAmUpsShippingMethod()
    {
        $quoteReservedId = 'test_quote';
        $methodCode = '1DM';
        $methodLabel = 'Next Day Air Early AM';

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals(
            self::CARRIER_LABEL . ' - ' . $methodLabel,
            $shippingAddress['selected_shipping_method']['label']
        );
    }

    /**
     * Set "Next Day Air" UPS shipping method
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     */
    public function testSetNextDayAirUpsShippingMethod()
    {
        $quoteReservedId = 'test_quote';
        $methodCode = '1DA';
        $methodLabel = 'Next Day Air';

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals(
            self::CARRIER_LABEL . ' - ' . $methodLabel,
            $shippingAddress['selected_shipping_method']['label']
        );
    }

    /**
     * Set "2nd Day Air" UPS shipping method
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     */
    public function testSet2ndDayAirUpsShippingMethod()
    {
        $quoteReservedId = 'test_quote';
        $methodCode = '2DA';
        $methodLabel = '2nd Day Air';

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals(
            self::CARRIER_LABEL . ' - ' . $methodLabel,
            $shippingAddress['selected_shipping_method']['label']
        );
    }

    /**
     * Set "3 Day Select" UPS shipping method
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     */
    public function testSet3DaySelectUpsShippingMethod()
    {
        $quoteReservedId = 'test_quote';
        $methodCode = '3DS';
        $methodLabel = '3 Day Select';

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals(
            self::CARRIER_LABEL . ' - ' . $methodLabel,
            $shippingAddress['selected_shipping_method']['label']
        );
    }

    /**
     * Set "Ground" UPS shipping method
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     */
    public function testSetGroundUpsShippingMethod()
    {
        $quoteReservedId = 'test_quote';
        $methodCode = 'GND';
        $methodLabel = 'Ground';

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals(
            self::CARRIER_LABEL . ' - ' . $methodLabel,
            $shippingAddress['selected_shipping_method']['label']
        );
    }

    /**
     * Set "Canada Standard" UPS shipping method
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_canada_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     */
    public function testSetCanadaStandardUpsShippingMethod()
    {
        $quoteReservedId = 'test_quote';
        $methodCode = 'STD';
        $methodLabel = 'Canada Standard';

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals(
            self::CARRIER_LABEL . ' - ' . $methodLabel,
            $shippingAddress['selected_shipping_method']['label']
        );
    }

    /**
     * Set "Worldwide Express" UPS shipping method
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_canada_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     */
    public function testSetWorldwideExpressUpsShippingMethod()
    {
        $quoteReservedId = 'test_quote';
        $methodCode = 'XPR';
        $methodLabel = 'Worldwide Express';

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals(
            self::CARRIER_LABEL . ' - ' . $methodLabel,
            $shippingAddress['selected_shipping_method']['label']
        );
    }

    /**
     * Set "Worldwide Express Saver" UPS shipping method
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_canada_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     */
    public function testSetWorldwideExpressSaverUpsShippingMethod()
    {
        $quoteReservedId = 'test_quote';
        $methodCode = 'WXS';
        $methodLabel = 'Worldwide Express Saver';

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals(
            self::CARRIER_LABEL . ' - ' . $methodLabel,
            $shippingAddress['selected_shipping_method']['label']
        );
    }

    /**
     * Set "Worldwide Express Plus" UPS shipping method
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_canada_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     */
    public function testSetWorldwideExpressPlusUpsShippingMethod()
    {
        $quoteReservedId = 'test_quote';
        $methodCode = 'XDM';
        $methodLabel = 'Worldwide Express Plus';

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals(
            self::CARRIER_LABEL . ' - ' . $methodLabel,
            $shippingAddress['selected_shipping_method']['label']
        );
    }

    /**
     * Set "Worldwide Expedited" UPS shipping method
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_canada_address.php
     * @magentoApiDataFixture Magento/GraphQl/Ups/_files/enable_ups_shipping_method.php
     */
    public function testSetWorldwideExpeditedUpsShippingMethod()
    {
        $quoteReservedId = 'test_quote';
        $methodCode = 'XPD';
        $methodLabel = 'Worldwide Expedited';

        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($quoteReservedId);
        $shippingAddressId = $this->getQuoteShippingAddressIdByReservedQuoteId->execute($quoteReservedId);

        $query = $this->getQuery($maskedQuoteId, $shippingAddressId, self::CARRIER_CODE, $methodCode);
        $response = $this->sendRequestWithToken($query);

        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingMethodsOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('selected_shipping_method', $shippingAddress);

        self::assertArrayHasKey('carrier_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals(self::CARRIER_CODE, $shippingAddress['selected_shipping_method']['carrier_code']);

        self::assertArrayHasKey('method_code', $shippingAddress['selected_shipping_method']);
        self::assertEquals($methodCode, $shippingAddress['selected_shipping_method']['method_code']);

        self::assertArrayHasKey('label', $shippingAddress['selected_shipping_method']);
        self::assertEquals(
            self::CARRIER_LABEL . ' - ' . $methodLabel,
            $shippingAddress['selected_shipping_method']['label']
        );
    }

    /**
     * Generates query for setting the specified shipping method on cart
     *
     * @param int $shippingAddressId
     * @param string $maskedQuoteId
     * @param string $carrierCode
     * @param string $methodCode
     * @return string
     */
    private function getQuery(
        string $maskedQuoteId,
        int $shippingAddressId,
        string $carrierCode,
        string $methodCode
    ): string {
        return <<<QUERY
mutation {
  setShippingMethodsOnCart(input: {
    cart_id: "$maskedQuoteId"
    shipping_methods: [
      {
        cart_address_id: $shippingAddressId
        carrier_code: "$carrierCode"
        method_code: "$methodCode"
      }
    ]
  }) {
    cart {
      shipping_addresses {
        selected_shipping_method {
          carrier_code
          method_code
          label
        }
      }
    } 
  }
}        
QUERY;
    }

    /**
     * Sends a GraphQL request with using a bearer token
     *
     * @param string $query
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function sendRequestWithToken(string $query): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken('customer@example.com', 'password');
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];

        return $this->graphQlMutation($query, [], '', $headerMap);
    }
}
