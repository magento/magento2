<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Multishipping\Helper\Data;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\ObjectManager;

/**
 * Test for set billing address on cart mutation
 */
class SetBillingAddressOnCartTest extends GraphQlAbstract
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->create(QuoteResource::class);
        $this->quote = $objectManager->create(Quote::class);
        $this->quoteIdToMaskedId = $objectManager->create(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testSetNewGuestBillingAddressOnCart()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
         address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "test region"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
          save_in_address_book: false
         }
      }
    }
  ) {
    cart {
      billing_address {
        firstname
        lastname
        company
        street
        city
        postcode
        telephone
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $this->assertNewAddressFields($billingAddressResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testSetNewGuestBillingAddressOnUseForShippingCart()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
         address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "test region"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
          save_in_address_book: false
         }
         use_for_shipping: true
      }
    }
  ) {
    cart {
      billing_address {
        firstname
        lastname
        company
        street
        city
        postcode
        telephone
      }
      shipping_addresses {
        firstname
        lastname
        company
        street
        city
        postcode
        telephone
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        self::assertArrayHasKey('shipping_addresses', $cartResponse);
        $shippingAddressResponse = current($cartResponse['shipping_addresses']);
        $this->assertNewAddressFields($billingAddressResponse);
        $this->assertNewAddressFields($shippingAddressResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testSetSavedBillingAddressOnCartByGuest()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
          customer_address_id: 1
        }
    }
  ) {
    cart {
      billing_address {
        firstname
        lastname
        company
        street
        city
        postcode
        telephone
      }
    }
  }
}
QUERY;
        self::expectExceptionMessage('The current customer isn\'t authorized.');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testSetNewRegisteredCustomerBillingAddressOnCart()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $this->quote->setCustomerId(1);
        $this->quoteResource->save($this->quote);

        $headerMap = $this->getHeaderMap();

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
         address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "test region"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
          save_in_address_book: false
         }
      }
    }
  ) {
    cart {
      billing_address {
        firstname
        lastname
        company
        street
        city
        postcode
        telephone
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '', $headerMap);

        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $this->assertNewAddressFields($billingAddressResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testSetSavedRegisteredCustomerBillingAddressOnCart()
    {
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $maskedQuoteId = $this->quoteIdToMaskedId->execute((int)$this->quote->getId());
        $this->quoteResource->load(
            $this->quote,
            'test_order_with_simple_product_without_address',
            'reserved_order_id'
        );
        $this->quote->setCustomerId(1);
        $this->quoteResource->save($this->quote);

        $headerMap = $this->getHeaderMap();

        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      billing_address: {
          customer_address_id: 1
       }
    }
  ) {
    cart {
      billing_address {
        firstname
        lastname
        company
        street
        city
        postcode
        telephone
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '', $headerMap);

        self::assertArrayHasKey('cart', $response['setBillingAddressOnCart']);
        $cartResponse = $response['setBillingAddressOnCart']['cart'];
        self::assertArrayHasKey('billing_address', $cartResponse);
        $billingAddressResponse = $cartResponse['billing_address'];
        $this->assertSavedBillingAddressFields($billingAddressResponse);
    }

    /**
     * Verify the all the whitelisted fields for a New Address Object
     *
     * @param array $billingAddressResponse
     */
    private function assertNewAddressFields(array $billingAddressResponse): void
    {
        $assertionMap = [
            ['response_field' => 'firstname', 'expected_value' => 'test firstname'],
            ['response_field' => 'lastname', 'expected_value' => 'test lastname'],
            ['response_field' => 'company', 'expected_value' => 'test company'],
            ['response_field' => 'street', 'expected_value' => [0 => 'test street 1', 1 => 'test street 2']],
            ['response_field' => 'city', 'expected_value' => 'test city'],
            ['response_field' => 'postcode', 'expected_value' => '887766'],
            ['response_field' => 'telephone', 'expected_value' => '88776655']
        ];

        $this->assertResponseFields($billingAddressResponse, $assertionMap);
    }

    /**
     * Verify the all the whitelisted fields for a Address Object
     *
     * @param array $billingAddressResponse
     */
    private function assertSavedBillingAddressFields(array $billingAddressResponse): void
    {
        $assertionMap = [
            ['response_field' => 'firstname', 'expected_value' => 'John'],
            ['response_field' => 'lastname', 'expected_value' => 'Smith'],
            ['response_field' => 'company', 'expected_value' => 'CompanyName'],
            ['response_field' => 'street', 'expected_value' => [0 => 'Green str, 67']],
            ['response_field' => 'city', 'expected_value' => 'CityM'],
            ['response_field' => 'postcode', 'expected_value' => '75477'],
            ['response_field' => 'telephone', 'expected_value' => '3468676']
        ];

        $this->assertResponseFields($billingAddressResponse, $assertionMap);
    }

    /**
     * @param string $username
     * @return array
     */
    private function getHeaderMap(string $username = 'customer@example.com'): array
    {
        $password = 'password';
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = ObjectManager::getInstance()
            ->get(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }

    public function tearDown()
    {
        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = ObjectManager::getInstance()->get(\Magento\Config\Model\ResourceModel\Config::class);

        //default state of multishipping config
        $config->saveConfig(
            Data::XML_PATH_CHECKOUT_MULTIPLE_AVAILABLE,
            1,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = ObjectManager::getInstance()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->reinit();
    }
}
