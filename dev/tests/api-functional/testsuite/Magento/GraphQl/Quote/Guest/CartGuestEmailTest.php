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
 * Test for getting guest email from cart
 */
class CartGuestEmailTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     */
    public function testGetCartGuestEmail()
    {
        $email = 'store@example.com';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute(
            'test_order_with_virtual_product_without_address'
        );

        $query = <<<QUERY
{
  cart(cart_id:"$maskedQuoteId") {    
    guest_email
  }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('cart', $response);
        $this->assertEquals($email, $response['cart']['guest_email']);
    }
}
