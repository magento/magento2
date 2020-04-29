<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class reset password for customer account
 */
class ResetPasswordTest extends GraphQlAbstract
{
    const CUSTOMER_EMAIL = "customer@example.com";

    const CUSTOMER_NEW_PASSWORD = "new_password123";

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var CustomerRegistry */
    private $customerRegistry;

    /**
     * @var LockCustomer
     */
    private $lockCustomer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->customerRegistry = $this->objectManager->get(CustomerRegistry::class);
        $this->lockCustomer = Bootstrap::getObjectManager()->get(LockCustomer::class);
        parent::setUp();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws Exception
     *
     * @throws LocalizedException
     */
    public function testResetCustomerAccountPasswordSuccessfully(): void
    {
        $query = <<<QUERY
mutation {
    resetPassword (
        email: "{$this->getCustomerEmail()}"
        resetPasswordToken: "{$this->getResetPasswordToken()}"
        newPassword: "{$this->getNewPassword()}"
    )
}
QUERY;
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('resetPassword', $response);
        self::assertTrue($response['resetPassword']);
    }

    /**
     * @magentoApiDataFixture    Magento/Customer/_files/customer.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Email must be specified
     *
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws LocalizedException
     */
    public function testEmailAvailableEmptyValue()
    {
        $query = <<<QUERY
mutation {
    resetPassword (
        email: ""
        resetPasswordToken: "{$this->getResetPasswordToken()}"
        newPassword: "{$this->getNewPassword()}"
    )
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture    Magento/Customer/_files/customer.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Email is invalid
     *
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws LocalizedException
     */
    public function testEmailInvalidValue()
    {
        $query = <<<QUERY
mutation {
    resetPassword (
        email: "invalid-email"
        resetPasswordToken: "{$this->getResetPasswordToken()}"
        newPassword: "{$this->getNewPassword()}"
    )
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture    Magento/Customer/_files/customer.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage resetPasswordToken must be specified
     *
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws LocalizedException
     */
    public function testResetPasswordTokenEmptyValue()
    {
        $query = <<<QUERY
mutation {
    resetPassword (
        email: "{$this->getCustomerEmail()}"
        resetPasswordToken: ""
        newPassword: "{$this->getNewPassword()}"
    )
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture    Magento/Customer/_files/customer.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot set customer password
     *
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws LocalizedException
     */
    public function testResetPasswordTokenMismatched()
    {
        $query = <<<QUERY
mutation {
    resetPassword (
        email: "{$this->getCustomerEmail()}"
        resetPasswordToken: "1234567890XYZ"
        newPassword: "{$this->getNewPassword()}"
    )
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture    Magento/Customer/_files/customer.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage newPassword must be specified
     *
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws LocalizedException
     */
    public function testNewPasswordEmptyValue()
    {
        $query = <<<QUERY
mutation {
    resetPassword (
        email: "{$this->getCustomerEmail()}"
        resetPasswordToken: "{$this->getResetPasswordToken()}"
        newPassword: ""
    )
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * Check password reset for lock customer
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @expectedException \Exception
     * @expectedExceptionMessage The account is locked
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testPasswordResetForLockCustomer()
    {
        $this->lockCustomer->execute(1);
        $query = <<<QUERY
mutation {
    resetPassword (
        email: "{$this->getCustomerEmail()}"
        resetPasswordToken: "{$this->getResetPasswordToken()}"
        newPassword: "{$this->getNewPassword()}"
    )
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * Check type of autocomplete_on_storefront storeConfig value
     *
     * @throws Exception
     */
    public function testReturnTypeAutocompleteOnStorefrontConfig()
    {
        $query = <<<QUERY
{
    storeConfig {
        autocomplete_on_storefront
    }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('autocomplete_on_storefront', $response['storeConfig']);
        self::assertInternalType('bool',$response['storeConfig']['autocomplete_on_storefront']);
    }

    /**
     * Get reset password token
     *
     * @return string
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getResetPasswordToken()
    {
        $this->accountManagement->initiatePasswordReset(
            $this->getCustomerEmail(),
            AccountManagement::EMAIL_RESET,
            1
        );

        $customerSecure = $this->customerRegistry->retrieveSecureData(1);
        return $customerSecure->getRpToken();
    }

    /**
     * Get customer email
     *
     * @return string
     */
    private function getCustomerEmail()
    {
        return self::CUSTOMER_EMAIL;
    }

    /**
     * Get new password for customer account
     *
     * @return string
     */
    private function getNewPassword()
    {
        return self::CUSTOMER_NEW_PASSWORD;
    }
}
