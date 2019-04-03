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
 * Test for setGuestEmailOnCart mutation
 */
class SetGuestEmailOnCartTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Quote/_files/empty_quote.php
     */
    public function testSetGuestEmailOnCart()
    {
        $reservedOrderId = 'reserved_order_id';
        $email = 'some@user.com';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);

        $query = $this->getSetGuestEmailOnCartMutation($maskedQuoteId, $email);
        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('setGuestEmailOnCart', $response);
        $this->assertArrayHasKey('cart', $response['setGuestEmailOnCart']);
        $this->assertEquals($email, $response['setGuestEmailOnCart']['cart']['guest_email']);
    }

    /**
     * @magentoApiDataFixture Magento/Quote/_files/empty_quote.php
     * @dataProvider incorrectInputDataProvider
     * @param string|null $maskedQuoteId
     * @param string $email
     * @param string $exceptionMessage
     */
    public function testSetGuestEmailOnCartWithIncorrectInputData(
        ?string $maskedQuoteId,
        string $email,
        string $exceptionMessage
    ) {
        if (null === $maskedQuoteId) { // Generate ID in case if no provided by data provider
            $reservedOrderId = 'reserved_order_id';
            $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedOrderId);
        }

        $query = $this->getSetGuestEmailOnCartMutation($maskedQuoteId, $email);
        $this->expectExceptionMessage($exceptionMessage);
        $this->graphQlQuery($query);
    }

    public function incorrectInputDataProvider(): array
    {
        return [
            'wrong_email' => [null, 'some', 'Invalid email format'],
            'no_email' => [null, '', 'Required parameter "email" is missing'],
            'wrong_quote_id' =>  ['xxx', 'some@user.com', 'Could not find a cart with ID "xxx"'],
            'no_quote_id' =>  ['', 'some@user.com', 'Required parameter "cart_id" is missing']
        ];
    }

    /**
     * Returns GraphQl mutation query for setting email address for a guest
     *
     * @param string $maskedQuoteId
     * @param string $email
     * @return string
     */
    private function getSetGuestEmailOnCartMutation(string $maskedQuoteId, string $email): string
    {
        return <<<QUERY
mutation {
  setGuestEmailOnCart(input: {
    cart_id:"$maskedQuoteId"
    email: "$email"
  }) {
    cart {
      guest_email
    }
  }
}
QUERY;
    }
}
