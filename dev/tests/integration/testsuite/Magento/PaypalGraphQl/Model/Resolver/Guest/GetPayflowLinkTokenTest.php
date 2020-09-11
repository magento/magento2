<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Guest;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test getPayflowLinkToken graphql endpoint for non-registered customer
 *
 * @magentoAppArea graphql
 */
class GetPayflowLinkTokenTest extends TestCase
{
    /** @var GraphQlRequest */
    private $graphQlRequest;

    /** @var SerializerInterface */
    private $json;

    /** @var ObjectManager */
    private $objectManager;

    /** @var GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
    }

    /**
     * Test get payflowLink secure token
     *
     * @magentoConfigFixture default_store payment/payflow_link/active 1
     * @magentoConfigFixture default_store payment/payflow_link/sandbox_flag 1
     * @magentoDataFixture Magento/Paypal/_files/order_payflow_link_with_payment.php
     * @return void
     */
    public function testResolvePayflowLinkToken() : void
    {
        $reservedQuoteId = 'test_quote';
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedQuoteId);

        $payflowLinkTokenQuery
            = <<<QUERY
 {
   getPayflowLinkToken(input: {cart_id:"$cartId"})
   {
       secure_token
       secure_token_id
       mode
       paypal_url
  }
}
QUERY;

        $response = $this->graphQlRequest->send($payflowLinkTokenQuery);
        $responseData = $this->json->unserialize($response->getContent());
        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertArrayHasKey('data', $responseData);

        $payflowLinkTokenResponse = $responseData['data']['getPayflowLinkToken'];
        $this->assertArrayHasKey('secure_token', $payflowLinkTokenResponse);
        $this->assertArrayHasKey('secure_token_id', $payflowLinkTokenResponse);
        $this->assertEquals('TEST', $payflowLinkTokenResponse['mode']);
        $this->assertEquals('https://pilot-payflowlink.paypal.com', $payflowLinkTokenResponse['paypal_url']);
    }
}
