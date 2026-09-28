<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test customer ID encoding to UID format in GraphQL responses
 */
class CustomerIdTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var Uid
     */
    private $idEncoder;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp(): void
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->idEncoder = Bootstrap::getObjectManager()->get(Uid::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * Test that customer ID is returned as encoded UID in customer query
     *
     * @return void
     * @throws Exception
     */
    #[
        DataFixture(CustomerFixture::class, as: 'customer')
    ]
    public function testCustomerIdReturnedAsEncodedUid(): void
    {
        $customer = $this->fixtures->get('customer');

        self::assertEquals(
            [
                'customer' => [
                    'id' => $this->idEncoder->encode((string)$customer->getId()),
                    'firstname' => $customer->getFirstname(),
                    'lastname' => $customer->getLastname(),
                    'email' => $customer->getEmail()
                ]
            ],
            $this->graphQlQuery(
                $this->getCustomerQuery(),
                [],
                '',
                $this->getCustomerAuthHeaders($customer->getEmail())
            )
        );
    }

    /**
     * Test that encoded customer UID can be decoded back to original ID
     *
     * @return void
     * @throws Exception
     */
    #[
        DataFixture(CustomerFixture::class, as: 'customer')
    ]
    public function testEncodedCustomerUidCanBeDecoded(): void
    {
        $customer = $this->fixtures->get('customer');

        $response = $this->graphQlQuery(
            $this->getCustomerQuery(),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail())
        );

        self::assertEquals(
            (string)$customer->getId(),
            $this->idEncoder->decode($response['customer']['id'])
        );
    }

    /**
     * Test customer creation returns encoded UID
     *
     * @return void
     * @throws Exception
     */
    public function testCreateCustomerReturnsEncodedUid(): void
    {
        $email = 'test_customer_' . uniqid() . '@example.com';
        $firstname = 'Test';
        $lastname = 'Customer';

        $response = $this->graphQlMutation(
            $this->getCreateCustomerMutation($email, $firstname, $lastname)
        );

        // Verify the response structure
        $createdCustomer = $this->customerRepository->get($email);
        $customerUid = $this->idEncoder->encode((string)$createdCustomer->getId());

        self::assertEquals(
            [
                'id' => $customerUid,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email
            ],
            $response['createCustomer']['customer']
        );

        // Verify the ID is encoded (not numeric)
        $encodedId = $response['createCustomer']['customer']['id'];
        self::assertIsString($encodedId);
        self::assertNotEmpty($encodedId);

        // Verify we can decode it back to a valid customer ID
        $decodedId = $this->idEncoder->decode($encodedId);
        self::assertIsNumeric($decodedId);

        // Verify customer exists in database
        self::assertEquals($decodedId, $createdCustomer->getId());
    }

    /**
     * Test update customer mutation with encoded UID
     *
     * @return void
     * @throws Exception
     */
    #[DataFixture(CustomerFixture::class, as: 'customer')]
    public function testUpdateCustomerWithEncodedUid(): void
    {
        $customer = $this->fixtures->get('customer');
        $newFirstname = 'Updated';
        $newLastname = 'Name';

        self::assertEquals([
            'updateCustomer' => [
                'customer' => [
                    'id' => $this->idEncoder->encode((string)$customer->getId()),
                    'firstname' => $newFirstname,
                    'lastname' => $newLastname,
                    'email' => $customer->getEmail()
                ]
            ]
        ], $this->graphQlMutation(
            $this->getUpdateCustomerMutation($newFirstname, $newLastname),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail())
        ));
    }

    /**
     * Get customer GraphQL query
     *
     * @return string
     */
    private function getCustomerQuery(): string
    {
        return <<<QUERY
            query {
                customer {
                    id
                    firstname
                    lastname
                    email
                }
            }
        QUERY;
    }

    /**
     * Get create customer GraphQL mutation
     *
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @return string
     */
    private function getCreateCustomerMutation(string $email, string $firstname, string $lastname): string
    {
        return <<<MUTATION
            mutation {
                createCustomer(
                    input: {
                        firstname: "{$firstname}"
                        lastname: "{$lastname}"
                        email: "{$email}"
                        password: "Test123!"
                    }
                ) {
                    customer {
                        id
                        firstname
                        lastname
                        email
                    }
                }
            }
        MUTATION;
    }

    /**
     * Get update customer GraphQL mutation
     *
     * @param string $firstname
     * @param string $lastname
     * @return string
     */
    private function getUpdateCustomerMutation(string $firstname, string $lastname): string
    {
        return <<<MUTATION
            mutation {
                updateCustomer(
                    input: {
                        firstname: "{$firstname}"
                        lastname: "{$lastname}"
                    }
                ) {
                    customer {
                        id
                        firstname
                        lastname
                        email
                    }
                }
            }
        MUTATION;
    }

    /**
     * Get customer authentication headers
     *
     * @param string $email
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->customerTokenService->createCustomerAccessToken(
                $email,
                'password'
            )
        ];
    }
}
