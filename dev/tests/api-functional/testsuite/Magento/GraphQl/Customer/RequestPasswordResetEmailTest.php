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
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot reset the customer's password
     */
    public function testCustomerAccountWithEmailNotAvailable()
    {
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
     *
     * @expectedException \Exception
     * @expectedExceptionMessage You must specify an email address.
     */
    public function testEmailAvailableEmptyValue()
    {
        $query = <<<QUERY
mutation {
  requestPasswordResetEmail(email: "")
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * Check if email is invalid
     *
     * @expectedException \Exception
     * @expectedExceptionMessage The email address has an invalid format.
     */
    public function testEmailAvailableInvalidValue()
    {
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
     *
     * @expectedException \Exception
     * @expectedExceptionMessage The account is locked
     */
    public function testRequestPasswordResetEmailForLockCustomer()
    {
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
