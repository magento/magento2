<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class RequestPasswordResetEmailTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerAccountWithEmailAvailable()
    {
        $query =
            <<<QUERY
mutation {
  requestPasswordResetEmail(email: "customer@example.com")
}
QUERY;
        $response = $this->graphQlMutation($query);

        self::assertArrayHasKey('requestPasswordResetEmail', $response);
        self::assertTrue($response['requestPasswordResetEmail']);
    }

    /**
     * Check if customer account is not available
     */
    public function testCustomerAccountWithEmailNotAvailable()
    {
        $query =
            <<<QUERY
mutation {
  requestPasswordResetEmail(email: "customerNotAvalible@example.com")
}
QUERY;
        $this->assertMessage('No such entity with email = customerNotAvalible@example.com, websiteId = 1');
        $this->graphQlMutation($query);
    }

    /**
     * Check if email value empty
     */
    public function testEmailAvailableEmptyValue()
    {
        $query = <<<QUERY
mutation {
  requestPasswordResetEmail(email: "")
}
QUERY;
        $this->assertMessage('Email must be specified');
        $this->graphQlMutation($query);
    }

    /**
     * Check if email is invalid
     */
    public function testEmailAvailableInvalidValue()
    {
        $query = <<<QUERY
mutation {
  requestPasswordResetEmail(email: "invalid-email")
}
QUERY;
        $this->assertMessage('Email is invalid');
        $this->graphQlMutation($query);
    }

    /**
     * Check if email contain right type
     */
    public function testEmailAvailableTypeValue()
    {
        $query = <<<QUERY
mutation {
  requestPasswordResetEmail (email: 12345)
}
QUERY;
        self::expectException(\Exception::class);
        self::expectExceptionMessage(
            'GraphQL response contains errors: Field "requestPasswordResetEmail" argument "email" requires type String!'
        );
        $this->graphQlMutation($query);
    }

    /**
     * Checks Exception and ExceptionMessages
     *
     * @param $message
     */
    private function assertMessage($message)
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage("GraphQL response contains errors: {$message}");
    }
}
