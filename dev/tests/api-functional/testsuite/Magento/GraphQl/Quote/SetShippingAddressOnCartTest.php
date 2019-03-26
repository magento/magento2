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
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\ObjectManager;

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
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/434');
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteIdToMaskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     */
    public function testSetNewShippingAddressByGuest()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_with_simple_product_without_address');

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
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('cart', $response['setShippingAddressesOnCart']);
        $cartResponse = $response['setShippingAddressesOnCart']['cart'];
        self::assertArrayHasKey('shipping_addresses', $cartResponse);
        $shippingAddressResponse = current($cartResponse['shipping_addresses']);
        $this->assertNewShippingAddressFields($shippingAddressResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testSetShippingAddressFromAddressBookByGuest()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_with_simple_product_without_address');

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
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testSetNewShippingAddressByRegisteredCustomer()
    {
        $maskedQuoteId = $this->assignQuoteToCustomer();

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
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response['setShippingAddressesOnCart']);
        $cartResponse = $response['setShippingAddressesOnCart']['cart'];
        self::assertArrayHasKey('shipping_addresses', $cartResponse);
        $shippingAddressResponse = current($cartResponse['shipping_addresses']);
        $this->assertNewShippingAddressFields($shippingAddressResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testSetShippingAddressFromAddressBookByRegisteredCustomer()
    {
        $maskedQuoteId = $this->assignQuoteToCustomer();

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
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap());

        self::assertArrayHasKey('cart', $response['setShippingAddressesOnCart']);
        $cartResponse = $response['setShippingAddressesOnCart']['cart'];
        self::assertArrayHasKey('shipping_addresses', $cartResponse);
        $shippingAddressResponse = current($cartResponse['shipping_addresses']);
        $this->assertSavedShippingAddressFields($shippingAddressResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a address with ID "100"
     */
    public function testSetNotExistedShippingAddressFromAddressBook()
    {
        $maskedQuoteId = $this->assignQuoteToCustomer();

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
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage The shipping address must contain either "customer_address_id" or "address".
     */
    public function testSetShippingAddressWithoutAddresses()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_with_simple_product_without_address');

        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$maskedQuoteId"
      shipping_addresses: [
        {}
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
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testSetNewShippingAddressAndFromAddressBookAtSameTime()
    {
        $maskedQuoteId = $this->assignQuoteToCustomer();

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
        $this->graphQlQuery($query, [], '', $this->getHeaderMap());
    }

    /**
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage You cannot specify multiple shipping addresses.
     */
    public function testSetMultipleNewShippingAddresses()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_with_simple_product_without_address');

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
        /** @var \Magento\Config\Model\ResourceModel\Config $config */
        $config = ObjectManager::getInstance()->get(\Magento\Config\Model\ResourceModel\Config::class);
        $config->saveConfig(
            Data::XML_PATH_CHECKOUT_MULTIPLE_AVAILABLE,
            null,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = ObjectManager::getInstance()->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->reinit();

        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/three_customers.php
     * @magentoApiDataFixture Magento/Customer/_files/customer_address.php
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @expectedException \Exception
     * @expectedExceptionMessage The current user cannot use address with ID "1"
     */
    public function testSetShippingAddressIfCustomerIsNotOwnerOfAddress()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReversedQuoteId('test_order_with_simple_product_without_address');

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

        $this->graphQlQuery($query, [], '', $this->getHeaderMap('customer2@search.example.com'));
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
            ['response_field' => 'telephone', 'expected_value' => '88776655']
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
     * @param string $reversedQuoteId
     * @return string
     */
    private function getMaskedQuoteIdByReversedQuoteId(string $reversedQuoteId): string
    {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedQuoteId, 'reserved_order_id');

        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
    }

    /**
     * @param string $reversedQuoteId
     * @param int $customerId
     * @return string
     */
    private function assignQuoteToCustomer(
        string $reversedQuoteId = 'test_order_with_simple_product_without_address',
        int $customerId = 1
    ): string {
        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reversedQuoteId, 'reserved_order_id');
        $quote->setCustomerId($customerId);
        $this->quoteResource->save($quote);
        return $this->quoteIdToMaskedId->execute((int)$quote->getId());
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
