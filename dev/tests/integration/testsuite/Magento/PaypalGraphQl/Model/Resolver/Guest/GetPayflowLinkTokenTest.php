<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Resolver\Guest;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Paypal\Model\Payflow\Request;
use Magento\Paypal\Model\Payflow\Service\Gateway;
use Magento\Quote\Model\QuoteIdToMaskedQuoteId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test PayflowLinkGetSecretToken graphql endpoint for non-registered customer
 *
 * @magentoAppArea graphql
 */
class GetPayflowLinkTokenTest extends TestCase
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @var QuoteIdToMaskedQuoteId
     */
    private $quoteIdToMaskedId;


    /** @var  ObjectManager */
    protected $objectManager;

    /** @var  GraphQl */
    protected $graphqlController;

    /** @var  GetMaskedQuoteIdByReservedOrderId */
    protected $getMaskedQuoteIdByReservedOrderId;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->graphqlController = $this->objectManager->get(GraphQl::class);
        $this->json = $this->objectManager->get(SerializerInterface::class);
        $this->request = $this->objectManager->create(Http::class);
    }

    /**
     * Test get payflowLink secret token
     *
     * @magentoConfigFixture default_store payment/payflow_link/active 1
     * @magentoConfigFixture default_store payment/payflow_link/sandbox_flag 1
     * @magentoDataFixture Magento/Paypal/_files/order_payflow_link.php
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
       secret_token
       secret_token_id
       mode
       paypal_url
  }
}
QUERY;

        $postData = $this->json->serialize(['query' => $payflowLinkTokenQuery]);
        $this->request->setPathInfo('/graphql');
        $this->request->setMethod('POST');
        $this->request->setContent($postData);
        $headers = $this->objectManager->create(\Zend\Http\Headers::class)
            ->addHeaders(['Content-Type' => 'application/json']);
        $this->request->setHeaders($headers);
        $response = $this->graphqlController->dispatch($this->request);
        $responseData = $this->json->unserialize($response->getContent());
        $this->assertArrayNotHasKey('errors', $responseData);
        $this->assertArrayHasKey('data', $responseData);

        $payflowLinkTokenResponse = $responseData['data']['getPayflowLinkToken'];

        $this->assertArrayHasKey('secret_token', $payflowLinkTokenResponse );
        $this->assertArrayHasKey('secret_token_id', $payflowLinkTokenResponse );

        $this->assertEquals('TEST', $payflowLinkTokenResponse['mode']);
        $this->assertEquals('https://pilot-payflowlink.paypal.com', $payflowLinkTokenResponse['paypal_url']);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(Gateway::class);
    }
}
