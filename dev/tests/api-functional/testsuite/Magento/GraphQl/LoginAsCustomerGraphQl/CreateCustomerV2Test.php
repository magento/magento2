<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\LoginAsCustomerGraphQl;

use PHPUnit\Framework\Attributes\DataProvider;
use Exception;
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

    /**
     * @var array
     */
    private $createdEmails = [];

    protected function setUp(): void
    {
        $this->registry = Bootstrap::getObjectManager()->get(Registry::class);
        $this->customerRepository = Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
        $this->uidEncoder = Bootstrap::getObjectManager()->get(Uid::class);
    }

    /**
     * Data provider for testCreateCustomerAccountWithAllowRemoteShoppingAssistance
     *
     * @return array
     */
    public static function allowRemoteShoppingAssistanceDataProvider(): array
    {
        return [
            'with_allow_remote_shopping_assistance_true' => [
                'allowValue' => true,
                'expectedValue' => true
            ],
            'with_allow_remote_shopping_assistance_false' => [
                'allowValue' => false,
                'expectedValue' => false
            ],
            'without_allow_remote_shopping_assistance' => [
                'allowValue' => null,
                'expectedValue' => false
            ]
        ];
    }

    /**
     * Test creating customer account with various allow_remote_shopping_assistance scenarios
     *
     * @param bool|null $allowValue
     * @param bool $expectedValue
     * @throws Exception
     */
    #[DataProvider('allowRemoteShoppingAssistanceDataProvider')]
    public function testCreateCustomerAccountWithAllowRemoteShoppingAssistance(
        ?bool $allowValue,
        bool $expectedValue
    ): void {
        $email = $this->generateDynamicEmail();

        $response = $this->graphQlMutation($this->getCreateCustomerQuery($email, $allowValue));

        $customer = $this->customerRepository->get($email);
        $encodedCustomerId = $this->uidEncoder->encode((string)$customer->getId());

        $this->assertEquals([
            'createCustomerV2' => [
                'customer' => [
                    'id' => $encodedCustomerId,
                    'firstname' => 'Richard',
                    'lastname' => 'Rowe',
                    'email' => $email,
                    'is_subscribed' => true,
                    'allow_remote_shopping_assistance' => $expectedValue
                ]
            ]
        ], $response);
    }

    /**
     * Generate a dynamic customer email
     *
     * @return string
     */
    private function generateDynamicEmail(): string
    {
        return $this->createdEmails[] = 'test_customer_' . uniqid() . '@example.com';
    }

    /**
     * Get the create customer mutation query
     *
     * @param string $email
     * @param bool|null $allowRemoteShoppingAssistance
     * @return string
     */
    private function getCreateCustomerQuery(string $email, ?bool $allowRemoteShoppingAssistance): string
    {
        $input = [
            'firstname' => 'Richard',
            'lastname' => 'Rowe',
            'email' => $email,
            'password' => 'test123#',
            'is_subscribed' => true
        ];

        if ($allowRemoteShoppingAssistance !== null) {
            $input['allow_remote_shopping_assistance'] = $allowRemoteShoppingAssistance;
        }

        $inputJson = json_encode($input);
        // Convert JSON to GraphQL format (replace quotes around keys and boolean values)
        $inputGraphQL = str_replace(
            [
                '"firstname":',
                '"lastname":',
                '"email":',
                '"password":',
                '"is_subscribed":',
                '"allow_remote_shopping_assistance":',
                ':true',
                ':false'
            ],
            [
                'firstname:',
                'lastname:',
                'email:',
                'password:',
                'is_subscribed:',
                'allow_remote_shopping_assistance:',
                ': true',
                ': false'
            ],
            $inputJson
        );

        return <<<MUTATION
            mutation {
                createCustomerV2(
                    input: {$inputGraphQL}
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
        MUTATION;
    }

    /**
     * Clean up created customers after each test
     */
    protected function tearDown(): void
    {
        foreach ($this->createdEmails as $email) {
            try {
                $customer = $this->customerRepository->get($email);
                $this->registry->unregister('isSecureArea');
                $this->registry->register('isSecureArea', true);
                $this->customerRepository->delete($customer);
                $this->registry->unregister('isSecureArea');
                $this->registry->register('isSecureArea', false);
            } catch (Exception $exception) {
                // Customer may not exist, continue with cleanup
            }
        }
        $this->createdEmails = [];
        parent::tearDown();
    }
}
