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

        $query = <<<QUERY
mutation {
  changePassword(
    currentPassword: "$oldCustomerPassword",
    newPassword: "$newCustomerPassword"
  ) {
    id
    email
    firstname
    lastname
  }
}
QUERY;

        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($customerEmail, $oldCustomerPassword);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals($customerEmail, $response['changePassword']['email']);

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
        $query = <<<QUERY
mutation {
  changePassword(
    currentPassword: "currentpassword",
    newPassword: "newpassword"
  ) {
    id
    email
    firstname
    lastname
  }
}
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Current customer' . ' ' .
                                     'does not have access to the resource "customer"');
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

        $query = <<<QUERY
mutation {
  changePassword(
    currentPassword: "$oldCustomerPassword",
    newPassword: "$newCustomerPassword"
  ) {
    id
    email
    firstname
    lastname
  }
}
QUERY;

        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($customerEmail, $oldCustomerPassword);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/Minimum of different classes of characters in password is.*/');

        $this->graphQlQuery($query, [], '', $headerMap);
    }

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
    }
}
