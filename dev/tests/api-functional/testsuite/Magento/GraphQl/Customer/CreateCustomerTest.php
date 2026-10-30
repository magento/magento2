<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Registry;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\GraphQl\Query\Uid;

/**
 * Test for create customer functionality
 */
class CreateCustomerTest extends GraphQlAbstract
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @var array
     */
    private $createdCustomerEmails = [];

    protected function setUp(): void
    {
        $this->idEncoder = Bootstrap::getObjectManager()->get(Uid::class);
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * Get create customer mutation with custom response fields
     *
     * @param array $input
     * @param array $responseFields
     * @return string
     * @throws Exception
     */
    private function getCreateCustomerMutation(array $input, array $responseFields): string
    {
        $inputString = $this->getImplode($input);
        $responseString = implode("\n            ", $responseFields);

        return <<<MUTATION
            mutation {
                createCustomer(
                    input: {
            {$inputString}
                    }
                ) {
                    customer {
                        {$responseString}
                    }
                }
            }
        MUTATION;
    }

    /**
     * Create customer account with valid email addresses
     *
     * @param string $email
     * @throws Exception
     */
    #[DataProvider('validEmailAddressDataProvider')]
    public function testCreateCustomerAccountWithPassword(string $email): void
    {
        $response = $this->graphQlMutation($this->getCreateCustomerMutation([
            'firstname' => 'Richard',
            'lastname' => 'Rowe',
            'email' => $email,
            'password' => 'test123#',
            'is_subscribed' => true
        ], ['id', 'firstname', 'lastname', 'email', 'is_subscribed']));

        // Track email for cleanup if customer was created successfully
        if (!empty($response['createCustomer']['customer']['email'])) {
            $this->createdCustomerEmails[] = $email;
        }

        $this->assertEquals(
            [
                'createCustomer' => [
                    'customer' => [
                        'id' => $this->idEncoder->encode((string) $this->customerRepository->get($email)->getId()),
                        'firstname' => 'Richard',
                        'lastname' => 'Rowe',
                        'email' => $email,
                        'is_subscribed' => true
                    ]
                ]
            ],
            $response
        );
    }

    /**
     * Data provider with valid email addresses
     *
     * @return array
     */
    public static function validEmailAddressDataProvider(): array
    {
        return [
            ['customer_' . uniqid() . '@example.com'],
            ['jørgen_' . uniqid() . '@somedomain.com'],
            ['email_' . uniqid() . '@example.com']
        ];
    }

    /**
     * @throws Exception
     */
    public function testCreateCustomerAccountWithoutPassword(): void
    {
        $newEmail = 'customer_' . uniqid() . '@example.com';

        $response = $this->graphQlMutation(
            $this->getCreateCustomerMutation(
                [
                    'firstname' => 'Richard',
                    'lastname' => 'Rowe',
                    'email' => $newEmail,
                    'is_subscribed' => true
                ],
                ['id', 'firstname', 'lastname', 'email', 'is_subscribed']
            )
        );

        // Track email for cleanup if customer was created successfully
        if (!empty($response['createCustomer']['customer']['email'])) {
            $this->createdCustomerEmails[] = $newEmail;
        }

        $this->assertEquals(
            [
                'createCustomer' => [
                    'customer' => [
                        'id' => $this->idEncoder->encode((string) $this->customerRepository->get($newEmail)->getId()),
                        'firstname' => 'Richard',
                        'lastname' => 'Rowe',
                        'email' => $newEmail,
                        'is_subscribed' => true
                    ]
                ]
            ],
            $response
        );
    }

    public function testCreateCustomerIfInputDataIsEmpty(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('"input" value should be specified');

        $this->graphQlMutation(
            $this->getCreateCustomerMutation(
                [],
                ['id', 'firstname', 'lastname', 'email', 'is_subscribed']
            )
        );
    }

    public function testCreateCustomerIfEmailMissed(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The email address is required to create a customer account.');

        $this->graphQlMutation(
            $this->getCreateCustomerMutation(
                [
                    'firstname' => 'Richard',
                    'lastname' => 'Rowe',
                    'password' => 'test123#',
                    'is_subscribed' => true
                ],
                ['id', 'firstname', 'lastname', 'email', 'is_subscribed']
            )
        );
    }

    /**
     * @param string $email
     * @throws Exception
     */
    #[DataProvider('invalidEmailAddressDataProvider')]
    public function testCreateCustomerIfEmailIsNotValid(string $email): void
    {
        $this->expectExceptionMessage('"' . $email . '" is not a valid email address.');
        $this->graphQlMutation(
            $this->getCreateCustomerMutation(
                [
                    'firstname' => 'Richard',
                    'lastname' => 'Rowe',
                    'email' => $email,
                    'password' => 'test123#',
                    'is_subscribed' => true
                ],
                ['id', 'firstname', 'lastname', 'email', 'is_subscribed']
            )
        );
    }

    /**
     * Data provider with invalid email addresses
     *
     * @return array
     */
    public static function invalidEmailAddressDataProvider(): array
    {
        return [
            ['plainaddress'],
            ['#@%^%#$@#$@#.com'],
            ['@example.com'],
            ['Joe Smith <email@example.com>'],
            ['email.example.com'],
            ['email@example@example.com'],
            ['email@example.com (Joe Smith)'],
            ['email@example']
        ];
    }

    public function testCreateCustomerIfPassedAttributeDosNotExistsInCustomerInput(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Field "test123" is not defined by type "CustomerInput".');

        $this->graphQlMutation(
            $this->getCreateCustomerMutation(
                [
                    'firstname' => 'Richard',
                    'lastname' => 'Rowe',
                    'test123' => '123test123',
                    'email' => 'customer_' . uniqid() . '@example.com',
                    'password' => 'test123#',
                    'is_subscribed' => true
                ],
                ['id', 'firstname', 'lastname', 'email', 'is_subscribed']
            )
        );
    }

    public function testCreateCustomerIfNameEmpty(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('"First Name" is a required value.');

        $this->graphQlMutation(
            $this->getCreateCustomerMutation(
                [
                    'email' => 'customer_' . uniqid() . '@example.com',
                    'firstname' => '',
                    'lastname' => 'Rowe',
                    'password' => 'test123#',
                    'is_subscribed' => true
                ],
                ['id', 'firstname', 'lastname', 'email', 'is_subscribed']
            )
        );
    }

    #[Config('newsletter/general/active', false)]
    public function testCreateCustomerSubscribed(): void
    {
        $email = 'customer_' . uniqid() . '@example.com';

        $response = $this->graphQlMutation(
            $this->getCreateCustomerMutation(
                [
                    'firstname' => 'Richard',
                    'lastname' => 'Rowe',
                    'email' => $email,
                    'is_subscribed' => true
                ],
                ['email', 'is_subscribed']
            )
        );

        // Track email for cleanup if customer was created successfully
        if (!empty($response['createCustomer']['customer']['email'])) {
            $this->createdCustomerEmails[] = $email;
        }

        $expectedResponse = [
            'createCustomer' => [
                'customer' => [
                    'email' => $email,
                    'is_subscribed' => false
                ]
            ]
        ];

        $this->assertEquals($expectedResponse, $response);
    }

    #[DataFixture(Customer::class, ['email' => 'customer@example.com'], 'existing_customer')]
    public function testCreateCustomerIfCustomerWithProvidedEmailAlreadyExists(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'A customer with the same email address already exists in an associated website.'
        );

        $this->graphQlMutation(
            $this->getCreateCustomerMutation(
                [
                    'email' => 'customer@example.com',
                    'password' => 'test123#',
                    'firstname' => 'John',
                    'lastname' => 'Smith'
                ],
                ['firstname', 'lastname', 'email']
            )
        );
    }

    /**
     * Clean up created customers
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Clean up customers created via GraphQL mutations during tests
        foreach ($this->createdCustomerEmails as $email) {
            try {
                $customer = $this->customerRepository->get($email);
                $this->registry->unregister('isSecureArea');
                $this->registry->register('isSecureArea', true);
                $this->customerRepository->delete($customer);
                $this->registry->unregister('isSecureArea');
            } catch (Exception $exception) {
                // Customer might not exist or already deleted, ignore
            }
        }

        parent::tearDown();
    }

    /**
     * Helper to format input array to GraphQL input string
     *
     * @param array $input
     * @return string
     */
    private function getImplode(array $input): string
    {
        $inputFields = [];
        foreach ($input as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_bool($value)) {
                $inputFields[] = "            {$key}: " . ($value ? 'true' : 'false');
                continue;
            }

            $escapedValue = addslashes($value);
            $inputFields[] = "            {$key}: \"{$escapedValue}\"";
        }

        return implode("\n", $inputFields);
    }
}
