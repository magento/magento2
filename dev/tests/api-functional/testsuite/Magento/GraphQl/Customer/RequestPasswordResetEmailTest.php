<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class RequestPasswordResetEmailTest extends GraphQlAbstract
{
    /**
     * @var LockCustomer
     */
    private $lockCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lockCustomer = Bootstrap::getObjectManager()->get(LockCustomer::class);
    }
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
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot reset the customer\'s password');
        $query =
            <<<QUERY
mutation {
  requestPasswordResetEmail(email: "customerNotAvalible@example.com")
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * Check if email value empty
     */
    public function testEmailAvailableEmptyValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You must specify an email address.');
        $query = <<<QUERY
mutation {
  requestPasswordResetEmail(email: "")
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * Check if email is invalid
     */
    public function testEmailAvailableInvalidValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The email address has an invalid format.');
        $query = <<<QUERY
mutation {
  requestPasswordResetEmail(email: "invalid-email")
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * Check if email was sent for lock customer
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testRequestPasswordResetEmailForLockCustomer()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The account is locked');
        $this->lockCustomer->execute(1);
        $query =
            <<<QUERY
mutation {
  requestPasswordResetEmail(email: "customer@example.com")
}
QUERY;

        $this->graphQlMutation($query);
    }
}
