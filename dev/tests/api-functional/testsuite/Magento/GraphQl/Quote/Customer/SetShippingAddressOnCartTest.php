<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for set shipping addresses on cart mutation
 */
class SetShippingAddressOnCartTest extends GraphQlAbstract
{
    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedId;

    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
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
            region: "test region"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
            save_in_address_book: false
          }
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
          label
          code
        }
        __typename
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response['setShippingAddressesOnCart']);
        $cartResponse = $response['setShippingAddressesOnCart']['cart'];
        self::assertArrayHasKey('shipping_addresses', $cartResponse);
        $shippingAddressResponse = current($cartResponse['shipping_addresses']);
        $this->assertNewShippingAddressFields($shippingAddressResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
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
            region: "test region"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
            save_in_address_book: false
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
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
        $response = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response['setShippingAddressesOnCart']);
        $cartResponse = $response['setShippingAddressesOnCart']['cart'];
        self::assertArrayHasKey('shipping_addresses', $cartResponse);
        $shippingAddressResponse = current($cartResponse['shipping_addresses']);
        $this->assertSavedShippingAddressFields($shippingAddressResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a address with ID "100"
     */
    public function testSetNonExistentShippingAddressFromAddressBook()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: [
        {
          customer_address_id: 100
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetNewShippingAddressAndFromAddressBookAtSameTime()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: [
        {
          customer_address_id: 1,
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
        self::expectExceptionMessage(
            'The shipping address cannot contain "customer_address_id" and "address" at the same time.'
        );
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Current customer does not have permission to address with ID "1"
     */
    public function testSetShippingAddressIfCustomerIsNotOwnerOfAddress()
    {
        $maskedQuoteId = $this->assignQuoteToCustomer('test_order_with_simple_product_without_address', 2);

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

        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customer2@search.example.com'));
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     *
     * @expectedException \Exception
     */
    public function testSetShippingAddressToAnotherCustomerCart()
    {
        $maskedQuoteId = $this->assignQuoteToCustomer('test_order_with_simple_product_without_address', 1);

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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap('customer2@search.example.com'));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
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
                'Required parameter "cart_id" is missing'
            ]
        ];
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
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
            region: "test region"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
            save_in_address_book: false
          }
        },
        {
          address: {
            firstname: "test firstname 2"
            lastname: "test lastname 2"
            company: "test company 2"
            street: ["test street 1", "test street 2"]
            city: "test city"
            region: "test region"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
            save_in_address_book: false
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
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
            region: "test region"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
            save_in_address_book: false
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
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
        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testSetShippingAddressToGuestCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: {
          customer_address_id: 1
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
        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"{$maskedQuoteId}\""
        );

        $this->graphQlMutation($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
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
        $result = $this->graphQlMutation($query, [], '', $this->getHeaderMap());

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
            ['response_field' => '__typename', 'expected_value' => 'ShippingCartAddress']
        ];

        $this->assertResponseFields($shippingAddressResponse, $assertionMap);
    }

    /**
     * Verify the all the whitelisted fields for a Address Object
     *
     * @param array $shippingAddressResponse
     */
    private function assertSavedShippingAddressFields(array $shippingAddressResponse): void
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

        $this->assertResponseFields($shippingAddressResponse, $assertionMap);
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     */
    private function getHeaderMap(string $username = 'customer@example.com', string $password = 'password'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }

    /**
     * @param string $reservedOrderId
     * @param int $customerId
     * @return string
     */
    private function assignQuoteToCustomer(
        string $reservedOrderId = 'test_order_with_simple_product_without_address',
        int $customerId = 1
    ): string {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $quote->setCustomerId($customerId);
        $this->quoteResource->save($quote);
        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }
}
