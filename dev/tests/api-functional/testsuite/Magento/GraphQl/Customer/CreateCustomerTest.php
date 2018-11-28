<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class CreateCustomerTest extends GraphQlAbstract
{
    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->customerRegistry = Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
    }

    /**
     * @throws \Exception
     */
    public function testCreateCustomerAccount()
    {
        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';
        $newEmail = 'customer_created' . rand(1, 2000000) . '@example.com';

        $query = <<<QUERY
mutation {
    createCustomer(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            email: "{$newEmail}"
            password: "{$currentPassword}"
          	is_subscribed: true
        }
    ) {
        customer {
            id
            firstname
            lastname
            email
            is_subscribed
        }
    }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertEquals($newFirstname, $response['createCustomer']['customer']['firstname']);
        $this->assertEquals($newLastname, $response['createCustomer']['customer']['lastname']);
        $this->assertEquals($newEmail, $response['createCustomer']['customer']['email']);
        $this->assertEquals(true, $response['createCustomer']['customer']['is_subscribed']);
    }
}
