<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;

class ChangePasswordTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    public function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * Test that change password sends an email
     *
     * @magentoAppArea graphql
     * @throws AuthenticationException
     */
    #[
        DbIsolation(false),
        DataFixture(Customer::class, as: 'customer'),
    ]
    public function testChangePasswordSendsEmail(): void
    {
        $customer = $this->fixtures->get('customer');
        $response = Bootstrap::getObjectManager()->create(GraphQlRequest::class)->send(
            $this->getChangePasswordMutation(),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail())
        );

        $this->assertEquals(
            [
                'data' => [
                    'changeCustomerPassword' => [
                        'email' => $customer->getEmail()
                    ]
                ]
            ],
            Bootstrap::getObjectManager()->get(SerializerInterface::class)->unserialize($response->getContent())
        );

        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = Bootstrap::getObjectManager()->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();

        // Verify an email was dispatched to the correct user
        $this->assertNotNull($sentMessage);
        $this->assertEquals($customer->getName(), $sentMessage->getTo()[0]->getName());
        $this->assertEquals($customer->getEmail(), $sentMessage->getTo()[0]->getEmail());

        // Assert the email contains the expected content
        $this->assertEquals('Your Main Website Store password has been changed', $sentMessage->getSubject());
        $this->assertStringContainsString(
            'We have received a request to change the following information associated with your account',
            quoted_printable_decode($sentMessage->getBody()->bodyToString())
        );
    }

    /**
     * Return change password mutation
     *
     * @return string
     */
    private function getChangePasswordMutation(): string
    {
        return <<<MUTATION
            mutation {
              changeCustomerPassword(
                currentPassword: "password"
                newPassword: "T3stP4assw0rd"
              ) {
                email
              }
            }
        MUTATION;
    }

    /**
     * Get customer authentication headers
     *
     * @param string $email
     * @return array
     * @throws AuthenticationException
     * @throws EmailNotConfirmedException
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');

        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
