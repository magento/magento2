<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for set shipping addresses on cart mutation
 */
class SetShippingAddressOnCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetNewShippingAddressOnCartWithSimpleProduct()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: [
        {
          address: {
            firstname: "test firstname"
            lastname: "test lastname"
            company: "test company"
            street: ["test street 1", "test street 2"]
            city: "test city"
            region: "AL"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
          }
          customer_notes: "Test note"
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        firstname
        lastname
        company
        street
        city
        postcode
        telephone
        country {
          code
          label
        }
        __typename
        customer_notes
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('cart', $response['setShippingAddressesOnCart']);
        $cartResponse = $response['setShippingAddressesOnCart']['cart'];
        self::assertArrayHasKey('shipping_addresses', $cartResponse);
        $shippingAddressResponse = current($cartResponse['shipping_addresses']);
        $this->assertNewShippingAddressFields($shippingAddressResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_virtual_product.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage The Cart includes virtual product(s) only, so a shipping address is not used.
     */
    public function testSetNewShippingAddressOnCartWithVirtualProduct()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: [
        {
          address: {
            firstname: "test firstname"
            lastname: "test lastname"
            company: "test company"
            street: ["test street 1", "test street 2"]
            city: "test city"
            region: "AL"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
          }
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        city
      }
    }
  }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testSetShippingAddressFromAddressBook()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: [
        {
          customer_address_id: 1
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        city
      }
    }
  }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     * @expectedException \Exception
     */
    public function testSetShippingAddressToCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: [
        {
          customer_address_id: 1
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        postcode
      }
    }
  }
}
QUERY;
        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     * @dataProvider dataProviderUpdateWithMissedRequiredParameters
     * @param string $input
     * @param string $message
     * @throws \Exception
     */
    public function testSetNewShippingAddressWithMissedRequiredParameters(string $input, string $message)
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $input = str_replace('cart_id_value', $maskedQuoteId, $input);

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      {$input}
    }
  ) {
    cart {
        shipping_addresses {
            city
          }
    }
  }
}
QUERY;
        $this->expectExceptionMessage($message);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetNewShippingAddressOnCartWithRedundantStreetLine()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: [
        {
          address: {
            firstname: "test firstname"
            lastname: "test lastname"
            company: "test company"
            street: ["test street 1", "test street 2", "test street 3"]
            city: "test city"
            region: "AL"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
          }
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        firstname
      }
    }
  }
}
QUERY;
        self::expectExceptionMessage('"Street Address" cannot contain more than 2 lines.');
        $this->graphQlMutation($query);
    }

    /**
     * @return array
     */
    public function dataProviderUpdateWithMissedRequiredParameters(): array
    {
        return [
            'missed_shipping_addresses' => [
                'cart_id: "cart_id_value"',
                'Field SetShippingAddressesOnCartInput.shipping_addresses of required type [ShippingAddressInput]! ' .
                'was not provided.',
            ],
            'missed_city' => [
                'shipping_addresses: [ { address: { save_in_address_book: false } } ]',
                'Field CartAddressInput.city of required type String! was not provided'
            ],
            'missed_cart_id' => [
                'shipping_addresses: {}',
                'Field SetShippingAddressesOnCartInput.cart_id of required type String! was not provided.'
            ]
        ];
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage You cannot specify multiple shipping addresses.
     */
    public function testSetMultipleNewShippingAddresses()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: [
        {
          address: {
            firstname: "test firstname"
            lastname: "test lastname"
            company: "test company"
            street: ["test street 1", "test street 2"]
            city: "test city"
            region: "AL"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
          }
        },
        {
          address: {
            firstname: "test firstname 2"
            lastname: "test lastname 2"
            company: "test company 2"
            street: ["test street 1", "test street 2"]
            city: "test city"
            region: "AL"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
          }
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        city
      }
    }
  }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a cart with ID "non_existent_masked_id"
     */
    public function testSetShippingAddressOnNonExistentCart()
    {
        $maskedQuoteId = 'non_existent_masked_id';
        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: {
        address: {
          firstname: "test firstname"
          lastname: "test lastname"
          company: "test company"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "AL"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
        }
      }
    }
  ) {
    cart {
      shipping_addresses {
        city
      }
    }
  }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetShippingAddressWithLowerCaseCountry()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      shipping_addresses: [
        {
          address: {
            firstname: "John"
            lastname: "Doe"
            street: ["6161 West Centinella Avenue"]
            city: "Culver City"
            region: "CA"
            postcode: "90230"
            country_code: "us"
            telephone: "555-555-55-55"
          }
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        region {
            code
        }
        country {
            code
        }
      }
    }
  }
}
QUERY;
        $result = $this->graphQlMutation($query);

        self::assertCount(1, $result['setShippingAddressesOnCart']['cart']['shipping_addresses']);
        $address = reset($result['setShippingAddressesOnCart']['cart']['shipping_addresses']);

        $this->assertEquals('US', $address['country']['code']);
        $this->assertEquals('CA', $address['region']['code']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetShippingAddressWithLowerCaseRegion()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      shipping_addresses: [
        {
          address: {
            firstname: "John"
            lastname: "Doe"
            street: ["6161 West Centinella Avenue"]
            city: "Culver City"
            region: "ca"
            postcode: "90230"
            country_code: "US"
            telephone: "555-555-55-55"
          }
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        region {
            code
        }
        country {
            code
        }
      }
    }
  }
}
QUERY;
        $result = $this->graphQlMutation($query);

        self::assertCount(1, $result['setShippingAddressesOnCart']['cart']['shipping_addresses']);
        $address = reset($result['setShippingAddressesOnCart']['cart']['shipping_addresses']);

        $this->assertEquals('US', $address['country']['code']);
        $this->assertEquals('CA', $address['region']['code']);
    }

    /**
     * Verify the all the whitelisted fields for a New Address Object
     *
     * @param array $shippingAddressResponse
     */
    private function assertNewShippingAddressFields(array $shippingAddressResponse): void
    {
        $assertionMap = [
            ['response_field' => 'firstname', 'expected_value' => 'test firstname'],
            ['response_field' => 'lastname', 'expected_value' => 'test lastname'],
            ['response_field' => 'company', 'expected_value' => 'test company'],
            ['response_field' => 'street', 'expected_value' => [0 => 'test street 1', 1 => 'test street 2']],
            ['response_field' => 'city', 'expected_value' => 'test city'],
            ['response_field' => 'postcode', 'expected_value' => '887766'],
            ['response_field' => 'telephone', 'expected_value' => '88776655'],
            ['response_field' => 'country', 'expected_value' => ['code' => 'US', 'label' => 'US']],
            ['response_field' => '__typename', 'expected_value' => 'ShippingCartAddress'],
            ['response_field' => 'customer_notes', 'expected_value' => 'Test note']
        ];

        $this->assertResponseFields($shippingAddressResponse, $assertionMap);
    }
}
