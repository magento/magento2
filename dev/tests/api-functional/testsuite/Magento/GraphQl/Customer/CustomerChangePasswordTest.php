<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CustomerChangePasswordTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerChangeValidPassword()
    {
        $customerEmail = 'customer@example.com';
        $oldCustomerPassword = 'password';
        $newCustomerPassword = 'anotherPassword1';

        $query = $this->getChangePassQuery($oldCustomerPassword, $newCustomerPassword);
        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $oldCustomerPassword);

        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals($customerEmail, $response['changeCustomerPassword']['email']);

        try {
            // registry contains the old password hash so needs to be reset
            $this->objectManager->get(\Magento\Customer\Model\CustomerRegistry::class)
                ->removeByEmail($customerEmail);
            $this->accountManagement->authenticate($customerEmail, $newCustomerPassword);
        } catch (LocalizedException $e) {
            $this->fail('Password was not changed: ' . $e->getMessage());
        }
    }

    public function testGuestUserCannotChangePassword()
    {
        $query = $this->getChangePassQuery('currentpassword', 'newpassword');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'GraphQL response contains errors: Current customer' . ' ' .
            'does not have access to the resource "customer"'
        );
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangeWeakPassword()
    {
        $customerEmail = 'customer@example.com';
        $oldCustomerPassword = 'password';
        $newCustomerPassword = 'weakpass';

        $query = $this->getChangePassQuery($oldCustomerPassword, $newCustomerPassword);
        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $oldCustomerPassword);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/Minimum of different classes of characters in password is.*/');

        $this->graphQlQuery($query, [], '', $headerMap);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCannotChangeWithIncorrectPassword()
    {
        $customerEmail = 'customer@example.com';
        $oldCustomerPassword = 'password';
        $newCustomerPassword = 'anotherPassword1';
        $incorrectPassword = 'password-incorrect';

        $query = $this->getChangePassQuery($incorrectPassword, $newCustomerPassword);

        // acquire authentication with correct password
        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $oldCustomerPassword);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/The password doesn\'t match this account. Verify the password.*/');

        // but try to change with incorrect 'old' password
        $this->graphQlQuery($query, [], '', $headerMap);
    }

    private function getChangePassQuery($currentPassword, $newPassword)
    {
        $query = <<<QUERY
mutation {
  changeCustomerPassword(
    currentPassword: "$currentPassword",
    newPassword: "$newPassword"
  ) {
    id
    email
    firstname
    lastname
  }
}
QUERY;

        return $query;
    }

    private function getCustomerAuthHeaders($customerEmail, $oldCustomerPassword)
    {
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($customerEmail, $oldCustomerPassword);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
    }
}
