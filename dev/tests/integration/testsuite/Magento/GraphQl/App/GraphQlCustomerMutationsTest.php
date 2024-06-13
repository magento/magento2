<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\GraphQl\App\State\GraphQlStateDiff;

/**
 * Tests the dispatch method in the GraphQl Controller class using a simple product query
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea graphql
 */
class GraphQlCustomerMutationsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GraphQlStateDiff|null
     */
    private ?GraphQlStateDiff $graphQlStateDiff = null;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        if (!class_exists(GraphQlStateDiff::class)) {
            $this->markTestSkipped('GraphQlStateDiff class is not available on this version of Magento.');
        }

        $this->graphQlStateDiff = new GraphQlStateDiff();
        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->graphQlStateDiff->tearDown();
        $this->graphQlStateDiff = null;
        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @dataProvider customerDataProvider
     * @return void
     */
    public function testCustomerState(
        string $query,
        array $variables,
        array $variables2,
        array $authInfo,
        string $operationName,
        string $expected,
    ) : void {
        if ($operationName === 'createCustomer') {
            $emails = [$variables['email'], $variables2['email']];
            $this->clearCustomerBeforeTest($emails);
        }
        $this->graphQlStateDiff->
            testState($query, $variables, $variables2, $authInfo, $operationName, $expected, $this);
    }

    /**
     * @param array $emails
     * @return void
     */
    private function clearCustomerBeforeTest(array $emails): void
    {
        $customerRepository = $this->graphQlStateDiff->getTestObjectManager()
            ->get(CustomerRepositoryInterface::class);
        $registry = $this->graphQlStateDiff->getTestObjectManager()->get(Registry::class);
        $registry->register('isSecureArea', true);
        foreach ($emails as $email) {
            try {
                $customer = $customerRepository->get($email);
                $customerRepository->delete($customer);
            } catch (NoSuchEntityException $e) {
                // Customer does not exist
            }
        }
        $registry->unregister('isSecureArea', false);
    }

    /**
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_virtual_product_saved.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/customer/create_empty_cart.php
     * @magentoDataFixture Magento/GraphQl/Quote/_files/add_simple_product.php
     */
    public function testMergeCarts(): void
    {
        $cartId1 = $this->graphQlStateDiff->getCartIdHash('test_order_with_virtual_product_without_address');
        $cartId2 = $this->graphQlStateDiff->getCartIdHash('test_quote');
        $query = $this->getCartMergeMutation();
        $this->graphQlStateDiff->testState(
            $query,
            ['cartId1' => $cartId1, 'cartId2' => $cartId2],
            [],
            ['email' => 'customer@example.com', 'password' => 'password'],
            'mergeCarts',
            '"data":{"mergeCarts":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testRequestPasswordResetEmail(): void
    {
        $query = $this->getRequestPasswordResetEmailMutation();
        $this->graphQlStateDiff->testState(
            $query,
            ['email' => 'customer@example.com'],
            [],
            [],
            'requestPasswordResetEmail',
            '"data":{"requestPasswordResetEmail":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testResetPassword(): void
    {
        $query = $this->getResetPasswordMutation();
        $email = 'customer@example.com';
        $this->graphQlStateDiff->testState(
            $query,
            ['email' => $email, 'newPassword' => 'new_password123', 'resetPasswordToken' =>
                $this->graphQlStateDiff->getResetPasswordToken($email)],
            [],
            [],
            'resetPassword',
            '"data":{"resetPassword":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testChangePassword(): void
    {
        $query = $this->getChangePasswordMutation();
        $this->graphQlStateDiff->testState(
            $query,
            ['currentPassword' => 'password', 'newPassword' => 'new_password123'],
            ['currentPassword' => 'new_password123', 'newPassword' => 'password_new123'],
            [['email'=>'customer@example.com', 'password' => 'password'],
            ['email'=>'customer@example.com', 'password' => 'new_password123']],
            'changeCustomerPassword',
            '"data":{"changeCustomerPassword":',
            $this
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_without_addresses.php
     * @return void
     */
    public function testCreateCustomerAddress(): void
    {
        $query = $this->getCreateCustomerAddressMutation();
        $this->graphQlStateDiff->testState(
            $query,
            [],
            [],
            ['email' => 'customer@example.com', 'password' => 'password'],
            'createCustomerAddress',
            '"data":{"createCustomerAddress":',
            $this
        );
    }

    /**
     * Queries, variables, operation names, and expected responses for test
     *
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function customerDataProvider(): array
    {
        return [
            'Create Customer' => [
                <<<'QUERY'
                mutation($firstname: String!, $lastname: String!, $email: String!, $password: String!) {
                    createCustomerV2(
                        input: {
                         firstname: $firstname,
                         lastname: $lastname,
                         email: $email,
                         password: $password
                         }
                    ) {
                        customer {
                            created_at
                            prefix
                            firstname
                            middlename
                            lastname
                            suffix
                            email
                            default_billing
                            default_shipping
                            date_of_birth
                            taxvat
                            is_subscribed
                            gender
                            allow_remote_shopping_assistance
                        }
                    }
                }
                QUERY,
                [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'email1@example.com',
                    'password' => 'Password-1',
                ],
                [
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'email2@adobe.com',
                    'password' => 'Password-2',
                ],
                [],
                'createCustomer',
                '"email":"',
            ],
            'Update Customer' => [
                <<<'QUERY'
                    mutation($allow: Boolean!) {
                        updateCustomerV2(
                            input: {
                                allow_remote_shopping_assistance: $allow
                            }
                        )
                        {
                            customer {
                                allow_remote_shopping_assistance
                            }
                        }
                    }
                QUERY,
                ['allow' => true],
                ['allow' => false],
                ['email' => 'customer@example.com', 'password' => 'password'],
                'updateCustomer',
                'allow_remote_shopping_assistance'
            ],
            'Update Customer Address' => [
                <<<'QUERY'
                    mutation($addressId: Int!, $city: String!) {
                       updateCustomerAddress(id: $addressId, input: {
                        region: {
                            region: "Alberta"
                            region_id: 66
                            region_code: "AB"
                        }
                        country_code: CA
                        street: ["Line 1 Street","Line 2"]
                        company: "Company Name"
                        telephone: "123456789"
                        fax: "123123123"
                        postcode: "7777"
                        city: $city
                        firstname: "Adam"
                        lastname: "Phillis"
                        middlename: "A"
                        prefix: "Mr."
                        suffix: "Jr."
                        vat_id: "1"
                        default_shipping: true
                        default_billing: true
                      }) {
                        id
                        customer_id
                        region {
                          region
                          region_id
                          region_code
                        }
                        country_code
                        street
                        company
                        telephone
                        fax
                        postcode
                        city
                        firstname
                        lastname
                        middlename
                        prefix
                        suffix
                        vat_id
                        default_shipping
                        default_billing
                      }
                }
                QUERY,
                ['addressId' => 1, 'city' => 'New York'],
                ['addressId' => 1, 'city' => 'Austin'],
                ['email' => 'customer@example.com', 'password' => 'password'],
                'updateCustomerAddress',
                'city'
            ],
            'Update Customer Email' => [
                <<<'QUERY'
                    mutation($email: String!, $password: String!) {
                        updateCustomerEmail(
                        email: $email
                        password: $password
                    ) {
                    customer {
                        email
                    }
                  }
                }
                QUERY,
                ['email' => 'customer2@example.com', 'password' => 'password'],
                ['email' => 'customer@example.com', 'password' => 'password'],
                [
                    ['email' => 'customer@example.com', 'password' => 'password'],
                    ['email' => 'customer2@example.com', 'password' => 'password'],
                ],
                'updateCustomerEmail',
                'email',
            ],
            'Generate Customer Token' => [
                <<<'QUERY'
                    mutation($email: String!, $password: String!) {
                        generateCustomerToken(email: $email, password: $password) {
                            token
                        }
                    }
                QUERY,
                ['email' => 'customer@example.com', 'password' => 'password'],
                ['email' => 'customer@example.com', 'password' => 'password'],
                [],
                'generateCustomerToken',
                'token'
            ],
            'Get Customer' => [
                <<<'QUERY'
                    query {
                      customer {
                        created_at
                        date_of_birth
                        default_billing
                        default_shipping
                        date_of_birth
                        email
                        firstname
                        gender
                        id
                        is_subscribed
                        lastname
                        middlename
                        prefix
                        suffix
                        taxvat
                      }
                    }
                QUERY,
                [],
                [],
                ['email' => 'customer@example.com', 'password' => 'password'],
                'getCustomer',
                '"data":{"customer":{"created_at"'
            ],
        ];
    }

    private function getCartMergeMutation(): string
    {
        return <<<'QUERY'
            mutation($cartId1: String!, $cartId2: String!) {
              mergeCarts(
                  source_cart_id: $cartId1
                  destination_cart_id: $cartId2
              ) {
                items {
                  quantity
                  product {
                    sku
                  }
                }
              }
            }
QUERY;
    }

    private function getRequestPasswordResetEmailMutation(): string
    {
        return <<<'QUERY'
            mutation($email: String!) {
              requestPasswordResetEmail(email: $email)
            }
        QUERY;
    }

    private function getResetPasswordMutation()
    {
        return <<<'QUERY'
            mutation($email: String!, $newPassword: String!, $resetPasswordToken: String!) {
              resetPassword(
                email: $email
                resetPasswordToken: $resetPasswordToken
                newPassword: $newPassword
              )
            }
        QUERY;
    }

    private function getChangePasswordMutation()
    {
        return <<<'QUERY'
            mutation($currentPassword: String!, $newPassword: String!) {
              changeCustomerPassword(
                currentPassword: $currentPassword
                newPassword: $newPassword
              ) {
                id
                email
                firstname
                lastname
              }
            }
        QUERY;
    }

    private function getCreateCustomerAddressMutation(): string
    {
        return <<<'QUERY'
            mutation {
              createCustomerAddress(
                input: {
                  region: {
                    region: "Alberta"
                    region_id: 66
                    region_code: "AB"
                  }
                  country_code: CA
                  street: ["Line 1 Street","Line 2"]
                  company: "Company Name"
                  telephone: "123456789"
                  fax: "123123123"
                  postcode: "7777"
                  city: "New York"
                  firstname: "Adam"
                  lastname: "Phillis"
                  middlename: "A"
                  prefix: "Mr."
                  suffix: "Jr."
                  vat_id: "1"
                  default_shipping: true
                  default_billing: true
                }
              ) {
                id
                customer_id
                region {
                  region
                  region_id
                  region_code
                }
                country_code
                street
                company
                telephone
                fax
                postcode
                city
                firstname
                lastname
                middlename
                prefix
                suffix
                vat_id
                default_shipping
                default_billing
              }
            }
        QUERY;
    }
}
