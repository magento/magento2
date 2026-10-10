<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for create customer (V2)
 */
class CreateCustomerV2Test extends GraphQlAbstract
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
     * @var array
     */
    private array $createdCustomerEmails = [];

    protected function setUp(): void
    {
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * Generate a dynamic email address
     *
     * @return string
     */
    private function generateDynamicEmail(): string
    {
        $email = 'customer_test_' . uniqid() . '@example.com';
        $this->createdCustomerEmails[] = $email;
        return $email;
    }

    /**
     * Build GraphQL input fields string from input array
     *
     * @param array $input
     * @return string
     */
    private function buildInputFieldsString(array $input): string
    {
        $inputFields = [];
        foreach ($input as $key => $value) {
            if ($value !== null) {
                $inputFields[] = is_bool($value) ?
                    "{$key}: " . ($value ? 'true' : 'false') :
                    "{$key}: \"{$value}\"";
            }
        }
        return implode("\n            ", $inputFields);
    }

    /**
     * Get the createCustomerV2 mutation with standard customer response fields
     *
     * @param array $input
     * @return string
     */
    private function getCreateCustomerV2Mutation(array $input): string
    {
        $inputString = $this->buildInputFieldsString($input);

        return <<<MUTATION
            mutation {
                createCustomerV2(
                    input: {
                        {$inputString}
                    }
                ) {
                    customer {
                        firstname
                        lastname
                        email
                        is_subscribed
                    }
                }
            }
        MUTATION;
    }

    /**
     * Get the createCustomerV2 mutation with custom response fields
     *
     * @param array $input
     * @param array $responseFields
     * @return string
     */
    private function getCreateCustomerV2MutationWithCustomFields(array $input, array $responseFields): string
    {
        $inputString = $this->buildInputFieldsString($input);
        $responseString = implode("\n            ", $responseFields);

        return <<<MUTATION
            mutation {
                createCustomerV2(
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

    #[Config('newsletter/general/active', true)]
    public function testCreateCustomerAccountWithPassword(): void
    {
        $email = $this->generateDynamicEmail();
        $response = $this->graphQlMutation($this->getCreateCustomerV2Mutation([
            'firstname' => 'Richard',
            'lastname' => 'Rowe',
            'email' => $email,
            'password' => 'test123#',
            'is_subscribed' => true
        ]));

        $expected = [
            'createCustomerV2' => [
                'customer' => [
                    'firstname' => 'Richard',
                    'lastname' => 'Rowe',
                    'email' => $email,
                    'is_subscribed' => true
                ]
            ]
        ];
        $this->assertEquals($expected, $response);
    }

    public function testCreateCustomerAccountWithoutPassword(): void
    {
        $email = $this->generateDynamicEmail();
        $response = $this->graphQlMutation($this->getCreateCustomerV2Mutation([
            'firstname' => 'Richard',
            'lastname' => 'Rowe',
            'email' => $email,
            'is_subscribed' => true
        ]));

        $expected = [
            'createCustomerV2' => [
                'customer' => [
                    'firstname' => 'Richard',
                    'lastname' => 'Rowe',
                    'email' => $email,
                    'is_subscribed' => true
                ]
            ]
        ];
        $this->assertEquals($expected, $response);
    }

    public function testCreateCustomerIfInputDataIsEmpty(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('CustomerCreateInput.email of required type String! was not provided.');

        $this->graphQlMutation($this->getCreateCustomerV2Mutation([]));
    }

    public function testCreateCustomerIfEmailMissed(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Field CustomerCreateInput.email of required type String! was not provided');

        $this->graphQlMutation($this->getCreateCustomerV2Mutation([
            'firstname' => 'Richard',
            'lastname' => 'Rowe',
            'password' => 'test123#',
            'is_subscribed' => true
        ]));
    }

    /**
     * @param string $email
     * @throws Exception
     */
    #[DataProvider('invalidEmailAddressDataProvider')]
    public function testCreateCustomerIfEmailIsNotValid(string $email): void
    {
        $this->expectExceptionMessage('"' . $email . '" is not a valid email address.');

        $this->graphQlMutation($this->getCreateCustomerV2Mutation([
            'firstname' => 'Richard',
            'lastname' => 'Rowe',
            'email' => $email,
            'password' => 'test123#',
            'is_subscribed' => true
        ]));
    }

    /**
     * Data provider for invalid email addresses
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

    public function testCreateCustomerIfPassedAttributeDoesNotExistInCustomerInput(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Field "test123" is not defined by type "CustomerCreateInput".');

        $email = $this->generateDynamicEmail();
        $query = <<<MUTATION
            mutation {
                createCustomerV2(
                    input: {
                        firstname: "Richard"
                        lastname: "Rowe"
                        test123: "123test123"
                        email: "{$email}"
                        password: "test123#"
                        is_subscribed: true
                    }
                ) {
                    customer {
                        firstname
                        lastname
                        email
                        is_subscribed
                    }
                }
            }
            MUTATION;
        $this->graphQlMutation($query);
    }

    public function testCreateCustomerIfNameEmpty(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('"First Name" is a required value.');

        $this->graphQlMutation($this->getCreateCustomerV2Mutation([
            'email' => $this->generateDynamicEmail(),
            'firstname' => '',
            'lastname' => 'Rowe',
            'password' => 'test123#',
            'is_subscribed' => true
        ]));
    }

    #[Config('newsletter/general/active', false)]
    public function testCreateCustomerSubscribed(): void
    {
        $email = $this->generateDynamicEmail();
        $response = $this->graphQlMutation($this->getCreateCustomerV2MutationWithCustomFields([
            'firstname' => 'Richard',
            'lastname' => 'Rowe',
            'email' => $email,
            'is_subscribed' => true
        ], ['email', 'is_subscribed']));

        $expected = [
            'createCustomerV2' => [
                'customer' => [
                    'email' => $email,
                    'is_subscribed' => false
                ]
            ]
        ];
        $this->assertEquals($expected, $response);
    }

    #[DataFixture(CustomerFixture::class, as: 'existing_customer')]
    public function testCreateCustomerIfCustomerWithProvidedEmailAlreadyExists(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'A customer with the same email address already exists in an associated website.'
        );

        $existingCustomer = DataFixtureStorageManager::getStorage()->get('existing_customer');
        $this->graphQlMutation($this->getCreateCustomerV2MutationWithCustomFields([
            'email' => $existingCustomer->getEmail(),
            'password' => 'test123#',
            'firstname' => 'John',
            'lastname' => 'Smith'
        ], ['firstname', 'lastname', 'email']));
    }

    /**
     * Clean up created customers
     */
    protected function tearDown(): void
    {
        if (!empty($this->createdCustomerEmails)) {
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', true);

            foreach ($this->createdCustomerEmails as $email) {
                try {
                    $customer = $this->customerRepository->get($email);
                    $this->customerRepository->delete($customer);
                } catch (Exception $exception) {
                    // Customer might not exist or already deleted
                    continue;
                }
            }

            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', false);
        }

        parent::tearDown();
    }
}
