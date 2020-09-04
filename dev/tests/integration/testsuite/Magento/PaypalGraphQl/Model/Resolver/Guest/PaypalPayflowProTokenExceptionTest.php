<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Guest;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\PaypalGraphQl\PaypalPayflowProAbstractTest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;

/**
 * Test PaypalPayflowProTokenExceptionTest graphql endpoint for guest
 *
 * @magentoAppArea graphql
 */
class PaypalPayflowProTokenExceptionTest extends PaypalPayflowProAbstractTest
{
    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @var QuoteIdToMaskedQuoteId
     */
    private $quoteIdToMaskedId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->quoteIdToMaskedId = $this->objectManager->get(QuoteIdToMaskedQuoteId::class);
    }

    /**
     * Test create paypal token for guest
     *
     * @return void
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/set_guest_email.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_billing_address.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_flatrate_shipping_method.php
     */
    public function testResolveWithPaypalError(): void
    {
        $this->enablePaymentMethod('payflowpro');

        $reservedQuoteId = 'test_quote';
        $cart = $this->getQuoteByReservedOrderId($reservedQuoteId);

        $cartId = $this->quoteIdToMaskedId->execute((int)$cart->getId());
        $query = $this->getCreatePayflowTokenMutation($cartId);

        $expectedExceptionMessage = "Payment Gateway is unreachable at the moment. Please use another payment option.";
        $expectedException = new \Zend_Http_Client_Exception($expectedExceptionMessage);

        $this->gatewayMock
            ->method('postRequest')
            ->willThrowException($expectedException);

        $response = $this->graphQlRequest->send($query);
        $responseData = $this->json->unserialize($response->getContent());
        $this->assertArrayHasKey('createPayflowProToken', $responseData['data']);
        $this->assertEmpty($responseData['data']['createPayflowProToken']);
        $this->assertArrayHasKey('errors', $responseData);
        $actualError = $responseData['errors'][0];
        $this->assertEquals($expectedExceptionMessage, $actualError['message']);
        $this->assertEquals(GraphQlInputException::EXCEPTION_CATEGORY, $actualError['extensions']['category']);
    }
}
