<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\TestFramework\TestCase\GraphQlAbstract;

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
}
