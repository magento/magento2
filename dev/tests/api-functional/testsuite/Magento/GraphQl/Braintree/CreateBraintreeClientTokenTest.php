<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Braintree;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test creating Braintree client token mutation
 */
class CreateBraintreeClientTokenTest extends GraphQlAbstract
{
    /**
     * Test creating Braintree client token
     *
     * @magentoConfigFixture default_store payment/braintree/active 1
     * @magentoConfigFixture default_store payment/braintree/environment sandbox
     * @magentoConfigFixture default_store payment/braintree/merchant_id def_merchant_id
     * @magentoConfigFixture default_store payment/braintree/public_key def_public_key
     * @magentoConfigFixture default_store payment/braintree/private_key def_private_key
     */
    public function testCreateBraintreeClientToken()
    {
        $response = $this->graphQlMutation($this->getMutation());

        self::assertArrayHasKey('createBraintreeClientToken', $response);
        self::assertNotEmpty($response['createBraintreeClientToken']);
    }

    /**
     * Test creating Braintree client token when method is disabled
     *
     * @expectedException \Exception
     * @expectedExceptionMessage payment method is not active
     */
    public function testCreateBraintreeClientTokenNotActive()
    {
        $this->graphQlMutation($this->getMutation());
    }

    private function getMutation(): string
    {
        return <<<QUERY
mutation {
  createBraintreeClientToken
}
QUERY;
    }
}
