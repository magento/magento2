<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Magento\Integration\Model\CustomerTokenService.
 */
class CustomerTokenServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $tokenService;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var TokenModel
     */
    private $tokenModel;

    /**
     * Setup CustomerTokenService
     */
    protected function setUp(): void
    {
        $this->tokenService = Bootstrap::getObjectManager()->get(
            \Magento\Integration\Model\CustomerTokenService::class
        );
        $this->accountManagement = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\AccountManagementInterface::class
        );
        $this->tokenModel = Bootstrap::getObjectManager()->get(\Magento\Integration\Model\Oauth\Token::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateCustomerAccessToken()
    {
        $customerUserName = 'customer@example.com';
        $password = 'password';
        $accessToken = $this->tokenService->createCustomerAccessToken($customerUserName, $password);
        $this->assertNotNull($accessToken);
    }

    /**
     * @dataProvider validationDataProvider
     */
    public function testCreateCustomerAccessTokenEmptyOrNullCredentials($username, $password)
    {
        try {
            $this->tokenService->createCustomerAccessToken($username, $password);
        } catch (InputException $e) {
            $this->assertInputExceptionMessages($e);
        }
    }

    /**
     */
    public function testCreateCustomerAccessTokenInvalidCustomer()
    {
        $this->expectException(\Magento\Framework\Exception\AuthenticationException::class);

        $customerUserName = 'invalid';
        $password = 'invalid';
        $this->tokenService->createCustomerAccessToken($customerUserName, $password);

        $this->expectExceptionMessage(
            'The account sign-in was incorrect or your account is disabled temporarily. '
            . 'Please wait and try again later.'
        );
    }

    #[
        Config('customer/create_account/confirm', 1, 'website'),
        DataFixture(
            Customer::class,
            [
                'email' => 'another@example.com',
                'confirmation' => 'account_not_confirmed'
            ],
            'customer'
        )
    ]
    public function testCreateCustomerAccessTokenEmailNotConfirmed()
    {
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $this->expectException(EmailNotConfirmedException::class);

        $this->tokenService->createCustomerAccessToken($customer->getEmail(), 'password');

        $this->expectExceptionMessage(
            "This account isn't confirmed. Verify and try again."
        );
    }

    /**
     * Provider to test input validation
     *
     * @return array
     */
    public static function validationDataProvider()
    {
        return [
            'Check for empty credentials' => ['', ''],
            'Check for null credentials' => [null, null]
        ];
    }

    /**
     * Assert for presence of Input exception messages
     *
     * @param InputException $e
     */
    private function assertInputExceptionMessages($e)
    {
        $this->assertEquals('One or more input exceptions have occurred.', $e->getMessage());
        $errors = $e->getErrors();
        $this->assertCount(2, $errors);
        $this->assertEquals('"username" is required. Enter and try again.', $errors[0]->getLogMessage());
        $this->assertEquals('"password" is required. Enter and try again.', $errors[1]->getLogMessage());
    }
}
