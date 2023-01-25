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
use PHPUnit\Framework\TestCase;

/**
 * Test getHostedProUrl graphql endpoint for Paypal Hosted Pro payment method
 *
 * @magentoAppArea graphql
 */
class GetHostedProSecureUrlTest extends TestCase
{
    /** @var GraphQlRequest */
    private $graphQlRequest;

    /** @var SerializerInterface */
    private $json;

    /** @var GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->json = $objectManager->get(SerializerInterface::class);
        $this->graphQlRequest = $objectManager->create(GraphQlRequest::class);
    }

    /**
     * Test get hostedpro secure URL
     *
     * @magentoConfigFixture default_store payment/hosted_pro/active 1
     * @magentoConfigFixture default_store payment/hosted_pro/sandbox_flag 1
     * @magentoDataFixture Magento/Paypal/_files/quote_order_hostedpro.php
     * @return void
     */
    public function testResolveHostedProUrl(): void
    {
        $reservedQuoteId = 'test_quote';
        $cartId = $this->getMaskedQuoteIdByReservedOrderId->execute($reservedQuoteId);

        $payflowLinkTokenQuery
            = <<<QUERY
 {
   getHostedProUrl(input: {cart_id:"$cartId"})
   {
       secure_form_url
  }
}
QUERY;

        $response = $this->graphQlRequest->send($payflowLinkTokenQuery);
        $responseData = $this->json->unserialize($response->getContent());

        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertNotEmpty($responseData['data']['getHostedProUrl']);
        $expectedSecureUrl = 'https://hostedpro.paypal.com';
        $actualSecureUrl = $responseData['data']['getHostedProUrl']['secure_form_url'];
        $this->assertEquals($expectedSecureUrl, $actualSecureUrl);
    }
}
