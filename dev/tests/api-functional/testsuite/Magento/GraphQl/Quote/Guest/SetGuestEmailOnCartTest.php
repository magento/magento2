<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for setGuestEmailOnCart mutation
 */
class SetGuestEmailOnCartTest extends GraphQlAbstract
{
    /**
     * @var GetMaskedQuoteIdByReservedOrderId
     */
    private $getMaskedQuoteIdByReservedOrderId;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->quoteFactory = $objectManager->get(QuoteFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testSetGuestEmailOnCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $email = 'some@user.com';

        $query = $this->getQuery($maskedQuoteId, $email);
        $response = $this->graphQlMutation($query);

        $this->assertArrayHasKey('setGuestEmailOnCart', $response);
        $this->assertArrayHasKey('cart', $response['setGuestEmailOnCart']);
        $this->assertEquals($email, $response['setGuestEmailOnCart']['cart']['email']);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     */
    public function testSetGuestEmailOnCartWithDifferentEmailAddress()
    {
        $reservedOrderId = 'test_quote';
        $secondEmail = 'attempt2@example.com';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $email = 'attempt1@example.com';
        $query = $this->getQuery($maskedQuoteId, $email);
        $this->graphQlMutation($query);

        $query = $this->getQuery($maskedQuoteId, $secondEmail);
        $this->graphQlMutation($query);

        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $reservedOrderId, 'reserved_order_id');
        $addresses = $quote->getAddressesCollection();
        $this->assertEquals(2, $addresses->count());
        foreach ($addresses as $address) {
            if ($address->getAddressType() === Address::ADDRESS_TYPE_SHIPPING) {
                $this->assertEquals($secondEmail, $address->getEmail());
            }
        }
    }

    /**
     * _security
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     */
    public function testSetGuestEmailOnCustomerCart()
    {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $email = 'some@user.com';

        $query = $this->getQuery($maskedQuoteId, $email);

        $this->expectExceptionMessage(
            "The current user cannot perform operations on cart \"$maskedQuoteId\""
        );
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     *
     * @dataProvider incorrectEmailDataProvider
     * @param string $email
     * @param string $exceptionMessage
     */
    public function testSetGuestEmailOnCartWithIncorrectEmail(
        string $email,
        string $exceptionMessage
    ) {
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');

        $query = $this->getQuery($maskedQuoteId, $email);
        $this->expectExceptionMessage($exceptionMessage);
        $this->graphQlMutation($query);
    }

    /**
     * @return array
     */
    public function incorrectEmailDataProvider(): array
    {
        return [
            'wrong_email' => ['some', 'Invalid email format'],
            'no_email' => ['', 'Required parameter "email" is missing'],
        ];
    }

    /**
     */
    public function testSetGuestEmailOnNonExistentCart()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Could not find a cart with ID "non_existent_masked_id"');

        $maskedQuoteId = 'non_existent_masked_id';
        $email = 'some@user.com';

        $query = $this->getQuery($maskedQuoteId, $email);
        $this->graphQlMutation($query);
    }

    /**
     */
    public function testSetGuestEmailWithEmptyCartId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Required parameter "cart_id" is missing');

        $maskedQuoteId = '';
        $email = 'some@user.com';

        $query = $this->getQuery($maskedQuoteId, $email);
        $this->graphQlMutation($query);
    }

    /**
     * Returns GraphQl mutation query for setting email address for a guest
     *
     * @param string $maskedQuoteId
     * @param string $email
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $email): string
    {
        return <<<QUERY
mutation {
  setGuestEmailOnCart(input: {
    cart_id: "$maskedQuoteId"
    email: "$email"
  }) {
    cart {
      email
    }
  }
}
QUERY;
    }
}
