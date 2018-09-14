<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Newsletter\Model\Subscriber;

class CustomerAccountEditTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var FilterBuilder */
    private $filterBuilder;

    /**
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerChangeAccountData()
    {
        $customerEmail = 'customer@example.com';
        $customerPassword = 'password';

        $customerNewFirstname = "John";
        $customerNewLastname = "Doe";
        $query = $this->getChangeAccountInformationQuery($customerNewFirstname, $customerNewLastname);

        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $customerPassword);
        $response = $this->graphQlQuery($query, [], '', $headerMap);

        $this->assertEquals($customerNewFirstname, $response['customerUpdate']['firstname']);
        $this->assertEquals($customerNewLastname, $response['customerUpdate']['lastname']);
    }

    /**
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerChangeEmail()
    {
        $customerEmail = 'customer@example.com';
        $customerPassword = 'password';
        $newEmailAddress = 'customer2@example.com';

        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $customerPassword);

        $query = $this->getChangeEmailQuery($newEmailAddress, $customerPassword);
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals($newEmailAddress, $response['customerUpdate']['email']);

        /**
         * Roll back email address to default
         */
        $query = $this->getChangeEmailQuery($customerEmail, $customerPassword);
        $response = $this->graphQlQuery($query, [], '', $headerMap);
        $this->assertEquals($customerEmail, $response['customerUpdate']['email']);
    }

    /**
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    public function testCustomerCheckSubscription()
    {
        $customerEmail = 'customer@example.com';
        $customerPassword = 'password';

        $isSubscribed = "true";

        $query = $this->getSubscriptionQuery($isSubscribed);
        $headerMap = $this->getCustomerAuthHeaders($customerEmail, $customerPassword);

        $this->graphQlQuery($query, [], '', $headerMap);

        $subscriberModel = ObjectManager::getInstance()->get(Subscriber::class);
        $subscriber = $subscriberModel->loadByEmail($customerEmail);

        $this->assertEquals(true, $subscriber->isSubscribed());
    }
    private function getSubscriptionQuery($isSubscribed)
    {
        $query = <<<QUERY
mutation {
  customerUpdate(
    is_subscribed: $isSubscribed) {
      firstname
	}
}
QUERY;
        return $query;
    }
    private function getChangeAccountInformationQuery($customerNewFirstname, $customerNewLastname)
    {
        $query = <<<QUERY
mutation {
  customerUpdate(
    firstname: "$customerNewFirstname", 
    lastname: "$customerNewLastname") {
      firstname
      lastname
	}
}
QUERY;
        return $query;
    }
    private function getChangeEmailQuery($customerEmail, $customerPassword)
    {
        $query = <<<QUERY
mutation {
  customerUpdate(
    email: "$customerEmail", 
    password: "$customerPassword") {
      email
	}
}
QUERY;
        return $query;
    }

    private function getCustomerAuthHeaders($customerEmail, $customerPassword)
    {
        /** @var CustomerTokenServiceInterface $customerTokenService */
        $customerTokenService = $this->objectManager->create(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken($customerEmail, $customerPassword);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->searchCriteriaBuilder = $objectManager->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->filterBuilder = $objectManager->get(
            \Magento\Framework\Api\FilterBuilder::class
        );
    }
}
