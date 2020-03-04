<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetGraphQl\Model\Resolver\Guest;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests SetPaymentMethod mutation for guest via authorizeNet payment
 *
 * @magentoAppArea graphql
 * @magentoDbIsolation disabled
 */
class SetAuthorizeNetPaymentMethodOnCartTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var GetMaskedQuoteIdByReservedOrderId */
    private $getMaskedQuoteIdByReservedOrderId;

    /** @var SerializerInterface */
    private $jsonSerializer;

    /** @var GraphQlRequest */
    private $graphQlRequest;

    protected function setUp() : void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->jsonSerializer = $this->objectManager->get(SerializerInterface::class);
        $this->getMaskedQuoteIdByReservedOrderId = $this->objectManager->get(GetMaskedQuoteIdByReservedOrderId::class);
        $this->graphQlRequest = $this->objectManager->create(GraphQlRequest::class);
    }

    /**
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/active 1
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/environment sandbox
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/login someusername
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_key somepassword
     * @magentoConfigFixture default_store payment/authorizenet_acceptjs/trans_signature_key abc
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/guest/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/set_new_shipping_address.php
     */
    public function testDispatchToSetPaymentMethodWithAuthorizenet(): void
    {
        $methodCode = 'authorizenet_acceptjs';
        $maskedQuoteId = $this->getMaskedQuoteIdByReservedOrderId->execute('test_quote');
        $query
            = <<<QUERY
 mutation {
  setPaymentMethodOnCart(input: {
      cart_id: "$maskedQuoteId"
      payment_method: {
          code: "$methodCode"
          authorizenet_acceptjs: 
            {opaque_data_descriptor: "COMMON.ACCEPT.INAPP.PAYMENT",
             opaque_data_value: "abx",
             cc_last_4: 1111}
      }
  }) {    
    cart {
      selected_payment_method {
        code
      }
    }
  }
}
QUERY;

        $response = $this->graphQlRequest->send($query);
        $output = $this->jsonSerializer->unserialize($response->getContent());
        $this->assertArrayNotHasKey('errors', $output, 'Response has errors');
        $this->assertArrayHasKey('setPaymentMethodOnCart', $output['data']);
        $selectedPaymentMethod = $output['data']['setPaymentMethodOnCart']['cart']['selected_payment_method'];
        $this->assertEquals($methodCode, $selectedPaymentMethod['code']);
    }
}
