<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for confirm customer email
 */
#[
    DataFixture(
        CustomerFixture::class,
        [
            'email' => 'customer@example.com',
            'confirmation' => 'abcde',
        ],
        'customer'
    )
]
class ConfirmEmailTest extends GraphQlAbstract
{
    private const QUERY = <<<QUERY
mutation {
  confirmEmail(input: {
    email: "%s"
    confirmation_key: "%s"
  }) {
    customer {
      email
    }
  }
}
QUERY;

    /**
     * @var string
     */
    private const PASSWORD = 'password';

    /**
     * @return void
     * @throws AuthenticationException
     */
    public function testConfirmEmail()
    {
        $response = $this->graphQlMutation(
            sprintf(
                self::QUERY,
                'customer@example.com',
                'abcde',
            ),
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', self::PASSWORD)
        );

        $this->assertEquals(
            [
                'confirmEmail' => [
                    'customer' => [
                        'email' => 'customer@example.com'
                    ]
                ]
            ],
            $response
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The account is already active.');

        $this->graphQlMutation(
            sprintf(
                self::QUERY,
                'customer@example.com',
                'abcde',
            ),
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', self::PASSWORD)
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     */
    public function testConfirmEmailWrongEmail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Email is invalid');

        $this->graphQlMutation(
            sprintf(
                self::QUERY,
                'bad-email',
                'abcde',
            ),
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', self::PASSWORD)
        );
    }

    /**
     * @return void
     * @throws AuthenticationException
     */
    public function testConfirmEmailWrongConfirmation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The confirmation token is invalid. Verify the token and try again.');

        $this->graphQlMutation(
            sprintf(
                self::QUERY,
                'customer@example.com',
                'wrong-confirmation',
            ),
            [],
            '',
            $this->getCustomerAuthHeaders('customer@example.com', self::PASSWORD)
        );
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = Bootstrap::getObjectManager()->get(
            CustomerTokenServiceInterface::class
        )->createCustomerAccessToken(
            $email,
            $password
        );
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
