<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ChangeCustomerPasswordTest extends GraphQlAbstract
{
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    protected function setUp()
    {
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->accountManagement = Bootstrap::getObjectManager()->get(AccountManagementInterface::class);
        $this->customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangePassword()
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
            $this->customerRegistry->removeByEmail($customerEmail);
            $this->accountManagement->authenticate($customerEmail, $newCustomerPassword);
        } catch (LocalizedException $e) {
            $this->fail('Password was not changed: ' . $e->getMessage());
        }
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testChangePasswordIfUserIsNotAuthorizedTest()
    {
        $query = $this->getChangePassQuery('currentpassword', 'newpassword');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangeWeakPassword()
    {
        $this->markTestIncomplete('https://github.com/magento/graphql-ce/issues/190');
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
     * @expectedException \Exception
     * @expectedExceptionMessage The password doesn't match this account. Verify the password and try again.
     */
    public function testChangePasswordIfPasswordIsInvalid()
    {
        $customerEmail = 'customer@example.com';
        $oldCustomerPassword = 'password';
        $newCustomerPassword = 'anotherPassword1';
        $incorrectPassword = 'password-incorrect';

        $query = $this->getChangePassQuery($incorrectPassword, $newCustomerPassword);

        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $oldCustomerPassword);
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

    /**
     * @param string $email
     * @param string $password
     * @return array
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
