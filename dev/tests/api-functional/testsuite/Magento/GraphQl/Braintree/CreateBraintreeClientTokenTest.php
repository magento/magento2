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
     * @magentoApiDataFixture Magento/GraphQl/Braintree/_files/enable_braintree_payment.php
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
