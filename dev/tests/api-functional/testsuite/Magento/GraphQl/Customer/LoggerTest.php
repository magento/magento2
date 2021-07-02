<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Model\Logger;
use Magento\Customer\Model\LogFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 *  Customer log data logger test.
 */
class LoggerTest extends GraphQlAbstract
{
    /**
     * Customer log data logger.
     *
     * @var Logger
     */
    protected $logger;

    protected function setUp(): void
    {
        $this->logger = Bootstrap::getObjectManager()->get(Logger::class);
    }

    /**
     * Verify customer log after generate customer token
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGenerateCustomerValidToken()
    {
        $response = $this->graphQlMutation($this->getQuery());
        $this->assertArrayHasKey('generateCustomerToken', $response);
        $this->assertIsArray($response['generateCustomerToken']);

        $log = $this->logger->get(1);
        $this->assertNotEmpty($log->getLastLoginAt());
    }

    /**
     * @param string $email
     * @param string $password
     * @return string
     */
    private function getQuery(string $email = 'customer@example.com', string $password = 'password') : string
    {
        return <<<MUTATION
mutation {
	generateCustomerToken(
        email: "{$email}"
        password: "{$password}"
    ) {
        token
    }
}
MUTATION;
    }
}
