<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Log;
use Magento\Customer\Model\Logger;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Customer\Model\CustomerFactory;

/**
 * API-functional tests cases for generateCustomerToken mutation
 */
class GenerateCustomerTokenTest extends GraphQlAbstract
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = Bootstrap::getObjectManager()->get(Logger::class);
        $this->customerFactory = Bootstrap::getObjectManager()->get(CustomerFactory::class);
    }

    /**
     * Verify customer token with valid credentials
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testGenerateCustomerValidToken(): void
    {
        $mutation = $this->getQuery('customer@example.com', 'wrongpassword');
        try {
            $response = $this->graphQlMutation($mutation);
        } catch (\Exception $e) {
        }
        $customer = $this->customerFactory->create()->setWebsiteId(1)
            ->loadByEmail('customer@example.com');
        $this->assertEquals(1, $customer->getFailuresNum());
        $this->assertNotNull($customer->getFirstFailure());

        $mutation = $this->getQuery();

        $response = $this->graphQlMutation($mutation);
        $customer = $this->customerFactory->create()->setWebsiteId(1)
            ->loadByEmail('customer@example.com');
        $this->assertEquals(0, $customer->getFailuresNum());
        $this->assertNull($customer->getFirstFailure());
        $this->assertArrayHasKey('generateCustomerToken', $response);
        $this->assertIsArray($response['generateCustomerToken']);
    }

    /**
     * Test customer with invalid data.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @dataProvider dataProviderInvalidCustomerInfo
     * @param string $email
     * @param string $password
     * @param string $message
     */
    public function testGenerateCustomerTokenInvalidData(string $email, string $password, string $message): void
    {
        $this->expectException(\Exception::class);

        $mutation = $this->getQuery($email, $password);
        $this->expectExceptionMessage($message);
        $this->graphQlMutation($mutation);
    }

    #[
        Config('customer/create_account/confirm', 1),
        DataFixture(
            Customer::class,
            [
                'email' => 'another@example.com',
                'confirmation' => 'account_not_confirmed'
            ],
            'customer'
        )
    ]
    public function testGenerateCustomerEmailNotConfirmed()
    {
        $this->expectException(\Exception::class);
        $customer = DataFixtureStorageManager::getStorage()->get('customer');

        $mutation = $this->getQuery($customer->getEmail());
        $this->expectExceptionMessage("This account isn't confirmed. Verify and try again.");
        $this->graphQlMutation($mutation);
    }

    /**
     * Test customer token regeneration.
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testRegenerateCustomerToken(): void
    {
        $mutation = $this->getQuery();

        $response1 = $this->graphQlMutation($mutation);
        $token1 = $response1['generateCustomerToken']['token'];

        sleep(2);

        $response2 = $this->graphQlMutation($mutation);
        $token2 = $response2['generateCustomerToken']['token'];

        $this->assertNotEquals($token1, $token2, 'Tokens should not be identical!');
    }

    /**
     * @return array
     */
    public static function dataProviderInvalidCustomerInfo(): array
    {
        return [
            'invalid_email' => [
                'invalid_email@example.com',
                'password',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ],
            'empty_email' => [
                '',
                'password',
                'Specify the "email" value.'
            ],
            'invalid_password' => [
                'customer@example.com',
                'invalid_password',
                'The account sign-in was incorrect or your account is disabled temporarily. ' .
                'Please wait and try again later.'
            ],
            'empty_password' => [
                'customer@example.com',
                '',
                'Specify the "password" value.'

            ]
        ];
    }

    /**
     * @param string $email
     * @param string $password
     * @return string
     */
    private function getQuery(string $email = 'customer@example.com', string $password = 'password'): string
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

    /**
     * Verify customer with empty email
     */
    public function testGenerateCustomerTokenWithEmptyEmail(): void
    {
        $email = '';
        $password = 'bad-password';

        $mutation = $this->getQuery($email, $password);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Specify the "email" value.');
        $this->graphQlMutation($mutation);
    }

    /**
     * Verify customer with empty password
     */
    public function testGenerateCustomerTokenWithEmptyPassword(): void
    {
        $email = 'customer@example.com';
        $password = '';

        $mutation = $this->getQuery($email, $password);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Specify the "password" value.');
        $this->graphQlMutation($mutation);
    }

    /**
     * Verify customer log after generate customer token
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerLogAfterGenerateCustomerToken(): void
    {
        $response = $this->graphQlMutation($this->getQuery());
        $this->assertArrayHasKey('generateCustomerToken', $response);
        $this->assertIsArray($response['generateCustomerToken']);

        /** @var Log $log */
        $log = $this->logger->get(1);
        $this->assertNotEmpty($log->getLastLoginAt());
    }

    /**
     * Ensure that customer log record is deleted.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        if ($this->logger->get(1)->getLastLoginAt()) {
            /** @var ResourceConnection $resource */
            $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
            /** @var AdapterInterface $connection */
            $connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
            $connection->delete(
                $resource->getTableName('customer_log'),
                ['customer_id' => 1]
            );
        }
        parent::tearDown();
    }
}
