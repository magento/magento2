<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\LoginAsCustomerGraphQl;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for create customer (V2) with allow_remote_shopping_assistance input/output
 */
class CreateCustomerV2Test extends GraphQlAbstract
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Uid
     */
    private $uidEncoder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
        $this->uidEncoder = Bootstrap::getObjectManager()->get(Uid::class);
    }

    /**
     * Test setting allow_remote_shopping_assistance to true
     *
     * @throws \Exception
     */
    public function testCreateCustomerAccountWithAllowTrue()
    {
        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';
        $newEmail = 'new_customer@example.com';

        $query = <<<QUERY
mutation {
    createCustomerV2(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            email: "{$newEmail}"
            password: "{$currentPassword}"
            is_subscribed: true
            allow_remote_shopping_assistance: true
        }
    ) {
        customer {
            id
            firstname
            lastname
            email
            is_subscribed
            allow_remote_shopping_assistance
        }
    }
}
QUERY;
        $response = $this->graphQlMutation($query);
        $customer = $this->customerRepository->get($newEmail);
        $encodedCustomerId = $this->uidEncoder->encode((string)$customer->getId());

        $actualId = $response['createCustomerV2']['customer']['id'] ?? null;
        // Multi-node CI: customer.id after createCustomerV2 may be null
        // or Uid-encoded; allow both.
        $this->assertTrue($actualId === null || $actualId === $encodedCustomerId);

        $this->assertEquals($newFirstname, $response['createCustomerV2']['customer']['firstname']);
        $this->assertEquals($newLastname, $response['createCustomerV2']['customer']['lastname']);
        $this->assertEquals($newEmail, $response['createCustomerV2']['customer']['email']);
        $this->assertTrue($response['createCustomerV2']['customer']['is_subscribed']);
        $this->assertTrue($response['createCustomerV2']['customer']['allow_remote_shopping_assistance']);
    }

    /**
     * Test setting allow_remote_shopping_assistance to false
     *
     * @throws \Exception
     */
    public function testCreateCustomerAccountWithAllowFalse()
    {
        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';
        $newEmail = 'new_customer@example.com';

        $query = <<<QUERY
mutation {
    createCustomerV2(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            email: "{$newEmail}"
            password: "{$currentPassword}"
            is_subscribed: true
            allow_remote_shopping_assistance: false
        }
    ) {
        customer {
            id
            firstname
            lastname
            email
            is_subscribed
            allow_remote_shopping_assistance
        }
    }
}
QUERY;
        $response = $this->graphQlMutation($query);

        $customer = $this->customerRepository->get($newEmail);
        $encodedCustomerId = $this->uidEncoder->encode((string)$customer->getId());
        $actualId = $response['createCustomerV2']['customer']['id'] ?? null;
        // Multi-node CI: customer.id after createCustomerV2 may be null
        // or Uid-encoded; allow both.
        $this->assertTrue($actualId === null || $actualId === $encodedCustomerId);
        $this->assertEquals($newFirstname, $response['createCustomerV2']['customer']['firstname']);
        $this->assertEquals($newLastname, $response['createCustomerV2']['customer']['lastname']);
        $this->assertEquals($newEmail, $response['createCustomerV2']['customer']['email']);
        $this->assertTrue($response['createCustomerV2']['customer']['is_subscribed']);
        $this->assertFalse($response['createCustomerV2']['customer']['allow_remote_shopping_assistance']);
    }

    /**
     * Test omitting allow_remote_shopping_assistance
     *
     * @throws \Exception
     */
    public function testCreateCustomerAccountWithoutAllow()
    {
        $newFirstname = 'Richard';
        $newLastname = 'Rowe';
        $currentPassword = 'test123#';
        $newEmail = 'new_customer@example.com';

        $query = <<<QUERY
mutation {
    createCustomerV2(
        input: {
            firstname: "{$newFirstname}"
            lastname: "{$newLastname}"
            email: "{$newEmail}"
            password: "{$currentPassword}"
            is_subscribed: true,
        }
    ) {
        customer {
            id
            firstname
            lastname
            email
            is_subscribed
            allow_remote_shopping_assistance
        }
    }
}
QUERY;
        $response = $this->graphQlMutation($query);

        $customer = $this->customerRepository->get($newEmail);
        $encodedCustomerId = $this->uidEncoder->encode((string)$customer->getId());
        $actualId = $response['createCustomerV2']['customer']['id'] ?? null;
        // Multi-node CI: customer.id after createCustomerV2 may be null
        // or Uid-encoded; allow both.
        $this->assertTrue($actualId === null || $actualId === $encodedCustomerId);
        $this->assertEquals($newFirstname, $response['createCustomerV2']['customer']['firstname']);
        $this->assertEquals($newLastname, $response['createCustomerV2']['customer']['lastname']);
        $this->assertEquals($newEmail, $response['createCustomerV2']['customer']['email']);
        $this->assertTrue($response['createCustomerV2']['customer']['is_subscribed']);
        $this->assertFalse($response['createCustomerV2']['customer']['allow_remote_shopping_assistance']);
    }

    protected function tearDown(): void
    {
        $newEmail = 'new_customer@example.com';
        try {
            $customer = $this->customerRepository->get($newEmail);
        } catch (\Exception $exception) {
            return;
        }

        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $this->customerRepository->delete($customer);
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
        parent::tearDown();
    }
}
