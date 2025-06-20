<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Eav\Test\Fixture\Attribute;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;

#[
    DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'is_required' => true,
        ],
        'customer_attribute'
    )
]
class CustomerEmailUpdateTest extends GraphQlAbstract
{
    /**
     * Test customer email update
     *
     * @return void
     * @throws Exception
     */
    public function testUpdateCustomerEmail(): void
    {
        $response = $this->graphQlMutation(
            $this->getQuery('newcustomer@example.com', 'password'),
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );

        $this->assertEquals('newcustomer@example.com', $response['updateCustomerEmail']['customer']['email']);
    }

    /**
     * Test customer email update with empty fields
     *
     * @param string $email
     * @param string $password
     * @param string $message
     * @dataProvider customerInputFieldDataProvider
     * @return void
     * @throws AuthenticationException
     */
    public function testUpdateCustomerEmailWithEmptyFields(
        string $email,
        string $password,
        string $message
    ): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);
        $this->graphQlMutation(
            $this->getQuery($email, $password),
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', 'password')
        );
    }

    /**
     * Data provider for testUpdateCustomerEmailWithEmptyFields
     *
     * @return array
     */
    public static function customerInputFieldDataProvider(): array
    {
        return [
            [
                'email' => 'newcustomer@example.com',
                'password' => '',
                'message' => 'Provide the current "password" to change "email".',
            ],
            [
                'email' => '',
                'password' => 'password',
                'message' => '"" is not a valid email address.',
            ]
        ];
    }

    /**
     * Get customer email update mutation
     *
     * @param string $newEmail
     * @param string $currentPassword
     * @return string
     */
    private function getQuery(string $newEmail, string $currentPassword): string
    {
        return <<<MUTATION
            mutation {
                updateCustomerEmail(
                    email: "{$newEmail}"
                    password: "{$currentPassword}"
                ) {
                    customer {
                        email
                    }
                }
            }
        MUTATION;
    }

    /**
     * Get customer authorization headers
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class)
            ->createCustomerAccessToken($email, $password);

        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
