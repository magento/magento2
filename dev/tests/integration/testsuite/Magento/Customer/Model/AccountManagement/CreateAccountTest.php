<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\AccountManagement;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Validator\Exception;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for customer creation via customer account management service.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class CreateAccountTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var array
     */
    private $defaultCustomerData = [
        'email' => 'customer@example.com',
        'firstname' => 'First name',
        'lastname' => 'Last name',
    ];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->customerFactory = $this->objectManager->get(CustomerInterfaceFactory::class);
        $this->dataObjectHelper = $this->objectManager->create(DataObjectHelper::class);
        parent::setUp();
    }

    /**
     * @dataProvider createInvalidAccountDataProvider
     * @param array $customerData
     * @param string $password
     * @param string $errorType
     * @param string $errorMessage
     * @return void
     */
    public function testCreateAccountWithInvalidFields(
        array $customerData,
        string $password,
        string $errorType,
        array $errorMessage
    ): void {
        $data = array_merge($this->defaultCustomerData, $customerData);
        $customerEntity = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray($customerEntity, $data, CustomerInterface::class);
        $this->expectException($errorType);
        $this->expectExceptionMessage((string)__(...$errorMessage));
        $this->accountManagement->createAccount($customerEntity, $password);
    }

    /**
     * @return array
     */
    public function createInvalidAccountDataProvider(): array
    {
        return [
            'empty_firstname' => [
                'customer_data' => ['firstname' => ''],
                'password' => '_aPassword1',
                'error_type' =>  Exception::class,
                'error_message' => ['"%1" is a required value.', 'First Name'],
            ],
            'empty_lastname' => [
                'customer_data' => ['lastname' => ''],
                'password' => '_aPassword1',
                'error_type' =>  Exception::class,
                'error_message' => ['"%1" is a required value.', 'Last Name'],
            ],
            'empty_email' => [
                'customer_data' => ['email' => ''],
                'password' => '_aPassword1',
                'error_type' => Exception::class,
                'error_message' => ['The customer email is missing. Enter and try again.'],
            ],
            'invalid_email' => [
                'customer_data' => ['email' => 'zxczxczxc'],
                'password' => '_aPassword1',
                'error_type' => Exception::class,
                'error_message' => ['"%1" is not a valid email address.', 'Email'],
            ],
            'empty_password' => [
                'customer_data' => [],
                'password' => '',
                'error_type' => InputException::class,
                'error_message' => ['The password needs at least 8 characters. Create a new password and try again.'],
            ],
            'invalid_password_minimum_length' => [
                'customer_data' => [],
                'password' => 'test',
                'error_type' => InputException::class,
                'error_message' => ['The password needs at least 8 characters. Create a new password and try again.'],
            ],
            'invalid_password_maximum_length' => [
                'customer_data' => [],
                'password' => $this->getRandomNumericString(257),
                'error_type' => InputException::class,
                'error_message' => ['Please enter a password with at most 256 characters.'],
            ],
            'invalid_password_without_minimum_characters_classes' => [
                'customer_data' => [],
                'password' => 'test_password',
                'error_type' => InputException::class,
                'error_message' => [
                    'Minimum of different classes of characters in password is %1.'
                    . ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                    3,
                ],
            ],
            'password_same_as_email' => [
                'customer_data' => ['email' => 'test1@test.com'],
                'password' => 'test1@test.com',
                'error_type' => LocalizedException::class,
                'error_message' => [
                    'The password can\'t be the same as the email address. Create a new password and try again.',
                ],
            ],
        ];
    }

    /**
     * Returns random numeric string with given length.
     *
     * @param int $length
     * @return string
     */
    private function getRandomNumericString(int $length): string
    {
        $string = '';
        for ($i = 0; $i <= $length; $i++) {
            $string .= Random::getRandomNumber(0, 9);
        }

        return $string;
    }
}
