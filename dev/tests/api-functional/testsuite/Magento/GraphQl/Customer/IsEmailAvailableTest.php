<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test email availability functionality
 */
class IsEmailAvailableTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testEmailNotAvailable()
    {
        $query =
            <<<QUERY
{
  isEmailAvailable(email: "customer@example.com") {
    is_email_available
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('isEmailAvailable', $response);
        self::assertArrayHasKey('is_email_available', $response['isEmailAvailable']);
        self::assertFalse($response['isEmailAvailable']['is_email_available']);
    }

    /**
     * Verify email availability
     */
    public function testEmailAvailable()
    {
        $query =
            <<<QUERY
{
  isEmailAvailable(email: "customer@example.com") {
    is_email_available
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        self::assertArrayHasKey('isEmailAvailable', $response);
        self::assertArrayHasKey('is_email_available', $response['isEmailAvailable']);
        self::assertTrue($response['isEmailAvailable']['is_email_available']);
    }

    /**
     */
    public function testEmailAvailableEmptyValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Email must be specified');

        $query =
            <<<QUERY
{
  isEmailAvailable(email: "") {
    is_email_available
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     */
    public function testEmailAvailableMissingValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Field "isEmailAvailable" argument "email" of type "String!" is required');

        $query =
            <<<QUERY
{
  isEmailAvailable {
    is_email_available
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     */
    public function testEmailAvailableInvalidValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Email is invalid');

        $query =
            <<<QUERY
{
  isEmailAvailable(email: "invalid-email") {
    is_email_available
  }
}
QUERY;
        $this->graphQlQuery($query);
    }
}
