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
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for resending confirmation email
 */
class ResendConfirmationEmailTest extends GraphQlAbstract
{
    private const QUERY = <<<QUERY
mutation {
  resendConfirmationEmail(email: "%s")
}
QUERY;

    /**
     * @return void
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
    public function testResendConfirmationEmail()
    {
        $response = $this->graphQlMutation(
            sprintf(
                self::QUERY,
                'customer@example.com'
            ),
        );

        $this->assertEquals(
            [
                'resendConfirmationEmail' => true
            ],
            $response
        );
    }

    /**
     * @return void
     */
    #[
        DataFixture(
            CustomerFixture::class,
            [
                'email' => 'customer@example.com',
                'confirmation' => null,
            ],
            'customer'
        )
    ]
    public function testResendConfirmationAlreadyConfirmedEmail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Confirmation isn\'t needed.');

        $this->graphQlMutation(
            sprintf(
                self::QUERY,
                'customer@example.com'
            ),
        );
    }

    /**
     * @return void
     */
    public function testResendConfirmationWrongEmail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Email address is not valid');

        $this->graphQlMutation(
            sprintf(
                self::QUERY,
                'bad-email'
            ),
        );
    }

    /**
     * @return void
     */
    public function testResendConfirmationNonExistingEmail()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('There is no user registered with that email address.');

        $this->graphQlMutation(
            sprintf(
                self::QUERY,
                'nonexisting@example.com'
            ),
        );
    }
}
