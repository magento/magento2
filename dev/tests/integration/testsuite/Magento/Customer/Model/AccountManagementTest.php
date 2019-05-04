<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Integration test for service layer \Magento\Customer\Model\AccountManagementTest
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea frontend
 */
class AccountManagementTest extends \PHPUnit\Framework\TestCase
{
    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var AddressRepositoryInterface needed to setup tests */
    private $addressRepository;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var AddressInterface[] */
    private $_expectedAddresses;

    /** @var \Magento\Customer\Api\Data\AddressInterfaceFactory */
    private $addressFactory;

    /** @var \Magento\Customer\Api\Data\CustomerInterfaceFactory */
    private $customerFactory;

    /** @var DataObjectProcessor */
    private $dataProcessor;

    /** @var \Magento\Framework\Api\ExtensibleDataObjectConverter */
    private $extensibleDataObjectConverter;

    /** @var  \Magento\Framework\Api\DataObjectHelper */
    protected $dataObjectHelper;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager
            ->create(\Magento\Customer\Api\AccountManagementInterface::class);
        $this->customerRepository = $this->objectManager
            ->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->addressRepository =
            $this->objectManager->create(\Magento\Customer\Api\AddressRepositoryInterface::class);

        $this->addressFactory = $this->objectManager->create(\Magento\Customer\Api\Data\AddressInterfaceFactory::class);
        $this->customerFactory = $this->objectManager->create(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class
        );
        $this->dataObjectHelper = $this->objectManager->create(\Magento\Framework\Api\DataObjectHelper::class);

        $regionFactory = $this->objectManager->create(\Magento\Customer\Api\Data\RegionInterfaceFactory::class);
        $address = $this->addressFactory->create();
        $address->setId('1')
            ->setCountryId('US')
            ->setCustomerId('1')
            ->setPostcode('75477')
            ->setRegion(
                $regionFactory->create()->setRegionCode('AL')->setRegion('Alabama')->setRegionId(1)
            )
            ->setRegionId(1)
            ->setCompany('CompanyName')
            ->setStreet(['Green str, 67'])
            ->setTelephone('3468676')
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith')
            ->setIsDefaultShipping(true)
            ->setIsDefaultBilling(true);

        $address2 = $this->addressFactory->create();
        $address2->setId('2')
            ->setCountryId('US')
            ->setCustomerId('1')
            ->setPostcode('47676')
            ->setRegion(
                $regionFactory->create()->setRegionCode('AL')->setRegion('Alabama')->setRegionId(1)
            )
            ->setRegionId(1)
            ->setCompany('Company')
            ->setStreet(['Black str, 48'])
            ->setCity('CityX')
            ->setTelephone('3234676')
            ->setFirstname('John')
            ->setLastname('Smith');

        $this->_expectedAddresses = [$address, $address2];

        $this->dataProcessor = $this->objectManager
            ->create(\Magento\Framework\Reflection\DataObjectProcessor::class);

        $this->extensibleDataObjectConverter = $this->objectManager
            ->create(\Magento\Framework\Api\ExtensibleDataObjectConverter::class);
    }

    /**
     * Clean up shared dependencies
     */
    protected function tearDown()
    {
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = $this->objectManager->get(\Magento\Customer\Model\CustomerRegistry::class);
        /** @var \Magento\Customer\Model\CustomerRegistry $addressRegistry */
        $addressRegistry = $this->objectManager->get(\Magento\Customer\Model\AddressRegistry::class);
        //Cleanup customer from registry
        $customerRegistry->remove(1);
        $addressRegistry->remove(1);
        $addressRegistry->remove(2);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testLogin()
    {
        // Customer email and password are pulled from the fixture customer.php
        $customer = $this->accountManagement->authenticate('customer@example.com', 'password');

        $this->assertSame('customer@example.com', $customer->getEmail());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @expectedException \Magento\Framework\Exception\InvalidEmailOrPasswordException
     */
    public function testLoginWrongPassword()
    {
        // Customer email and password are pulled from the fixture customer.php
        $this->accountManagement->authenticate('customer@example.com', 'wrongPassword');
    }

    /**
     * @expectedException \Magento\Framework\Exception\InvalidEmailOrPasswordException
     * @expectedExceptionMessage Invalid login or password.
     */
    public function testLoginWrongUsername()
    {
        // Customer email and password are pulled from the fixture customer.php
        $this->accountManagement->authenticate('non_existing_user', '_Password123');
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangePassword()
    {
        $this->accountManagement->changePassword('customer@example.com', 'password', 'new_Password123');

        $this->accountManagement->authenticate('customer@example.com', 'new_Password123');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @expectedException \Magento\Framework\Exception\InvalidEmailOrPasswordException
     * @expectedExceptionMessage The password doesn't match this account. Verify the password and try again.
     */
    public function testChangePasswordWrongPassword()
    {
        $this->accountManagement->changePassword('customer@example.com', 'wrongPassword', 'new_Password123');
    }

    /**
     * @expectedException \Magento\Framework\Exception\InvalidEmailOrPasswordException
     * @expectedExceptionMessage Invalid login or password.
     */
    public function testChangePasswordWrongUser()
    {
        $this->accountManagement->changePassword('wrong.email@example.com', '_Password123', 'new_Password123');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     * @magentoAppArea frontend
     */
    public function testActivateAccount()
    {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->objectManager->create(\Magento\Customer\Model\Customer::class);
        $customerModel->load(1);
        // Assert in just one test that the fixture is working
        $this->assertNotNull($customerModel->getConfirmation(), 'New customer needs to be confirmed');

        $this->accountManagement->activate($customerModel->getEmail(), $customerModel->getConfirmation());

        $customerModel = $this->objectManager->create(\Magento\Customer\Model\Customer::class);
        $customerModel->load(1);
        $this->assertNull($customerModel->getConfirmation(), 'Customer should be considered confirmed now');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     * @expectedException \Magento\Framework\Exception\State\InputMismatchException
     */
    public function testActivateCustomerConfirmationKeyWrongKey()
    {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->objectManager->create(\Magento\Customer\Model\Customer::class);
        $customerModel->load(1);
        $key = $customerModel->getConfirmation();

        try {
            $this->accountManagement->activate($customerModel->getEmail(), $key . $key);
            $this->fail('Expected exception was not thrown');
        } catch (InputException $ie) {
            $this->assertEquals('', $ie->getMessage());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     */
    public function testActivateCustomerWrongAccount()
    {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->objectManager->create(\Magento\Customer\Model\Customer::class);
        $customerModel->load(1);
        $key = $customerModel->getConfirmation();
        try {
            $this->accountManagement->activate('1234' . $customerModel->getEmail(), $key);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals(
                'No such entity with email = 1234customer@needAconfirmation.com, websiteId = 1',
                $nsee->getMessage()
            );
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     * @magentoAppArea frontend
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     */
    public function testActivateCustomerAlreadyActive()
    {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->objectManager->create(\Magento\Customer\Model\Customer::class);
        $customerModel->load(1);
        $key = $customerModel->getConfirmation();
        $this->accountManagement->activate($customerModel->getEmail(), $key);
        // activate it one more time to produce an exception
        $this->accountManagement->activate($customerModel->getEmail(), $key);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testValidateResetPasswordLinkToken()
    {
        $this->setResetPasswordData('token', 'Y-m-d H:i:s');
        $this->accountManagement->validateResetPasswordLinkToken(1, 'token');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\State\ExpiredException
     */
    public function testValidateResetPasswordLinkTokenExpired()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $this->setResetPasswordData($resetToken, '1970-01-01');
        $this->accountManagement->validateResetPasswordLinkToken(1, $resetToken);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testValidateResetPasswordLinkTokenInvalid()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $invalidToken = 0;
        $this->setResetPasswordData($resetToken, 'Y-m-d H:i:s');
        try {
            $this->accountManagement->validateResetPasswordLinkToken(1, $invalidToken);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $ie) {
            $this->assertEquals('"%fieldName" is required. Enter and try again.', $ie->getRawMessage());
            $this->assertEquals('"resetPasswordLinkToken" is required. Enter and try again.', $ie->getMessage());
            $this->assertEquals('"resetPasswordLinkToken" is required. Enter and try again.', $ie->getLogMessage());
            $this->assertEmpty($ie->getErrors());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testValidateResetPasswordLinkTokenWrongUser()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';

        try {
            $this->accountManagement->validateResetPasswordLinkToken(4200, $resetToken);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals('No such entity with customerId = 4200', $nsee->getMessage());
        }
    }

    /**
     * Test for resetPassword() method when reset for the second time
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\State\InputMismatchException
     */
    public function testResetPasswordTokenSecondTime()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'new_Password123';
        $email = 'customer@example.com';
        $this->setResetPasswordData($resetToken, 'Y-m-d H:i');
        $this->assertTrue($this->accountManagement->resetPassword($email, $resetToken, $password));
        $this->accountManagement->resetPassword($email, $resetToken, $password);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testValidateResetPasswordLinkTokenNull()
    {
        try {
            $this->accountManagement->validateResetPasswordLinkToken(1, null);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $ie) {
            $this->assertEquals('"%fieldName" is required. Enter and try again.', $ie->getRawMessage());
            $this->assertEquals('"resetPasswordLinkToken" is required. Enter and try again.', $ie->getMessage());
            $this->assertEquals('"resetPasswordLinkToken" is required. Enter and try again.', $ie->getLogMessage());
            $this->assertEmpty($ie->getErrors());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testValidateResetPasswordLinkTokenWithoutId()
    {
        $token = 'randomStr123';
        $this->setResetPasswordData($token, 'Y-m-d H:i:s');
        $this->assertTrue(
            $this->accountManagement->validateResetPasswordLinkToken(null, $token)
        );
    }
    /**
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     * @expectedException \Magento\Framework\Exception\State\ExpiredException
     */
    public function testValidateResetPasswordLinkTokenAmbiguous()
    {
        $token = 'randomStr123';
        $this->setResetPasswordData($token, 'Y-m-d H:i:s', 1);
        $this->setResetPasswordData($token, 'Y-m-d H:i:s', 2);
        $this->accountManagement->validateResetPasswordLinkToken(null, $token);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSendPasswordResetLink()
    {
        $email = 'customer@example.com';

        $this->accountManagement->initiatePasswordReset($email, AccountManagement::EMAIL_RESET, 1);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSendPasswordResetLinkDefaultWebsite()
    {
        $email = 'customer@example.com';

        $this->accountManagement->initiatePasswordReset($email, AccountManagement::EMAIL_RESET);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testSendPasswordResetLinkBadEmailOrWebsite()
    {
        $email = 'foo@example.com';

        try {
            $this->accountManagement->initiatePasswordReset(
                $email,
                AccountManagement::EMAIL_RESET,
                0
            );
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $expectedParams = [
                'fieldName' => 'email',
                'fieldValue' => $email,
                'field2Name' => 'websiteId',
                'field2Value' => 0,
            ];
            $this->assertEquals($expectedParams, $e->getParameters());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSendPasswordResetLinkBadEmailDefaultWebsite()
    {
        $email = 'foo@example.com';

        try {
            $this->accountManagement->initiatePasswordReset(
                $email,
                AccountManagement::EMAIL_RESET
            );
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            // App area is frontend, so we expect websiteId of 1.
            $this->assertEquals('No such entity with email = foo@example.com, websiteId = 1', $nsee->getMessage());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPassword()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'new_Password123';

        $this->setResetPasswordData($resetToken, 'Y-m-d H:i:s');
        $this->assertTrue($this->accountManagement->resetPassword('customer@example.com', $resetToken, $password));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordTokenExpired()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'new_Password123';

        $this->setResetPasswordData($resetToken, '1970-01-01');
        try {
            $this->accountManagement->resetPassword('customer@example.com', $resetToken, $password);
            $this->fail('Expected exception not thrown.');
        } catch (ExpiredException $e) {
            $this->assertEquals('The password token is expired. Reset and try again.', $e->getMessage());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testResetPasswordTokenInvalid()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $invalidToken = 0;
        $password = 'new_Password123';

        $this->setResetPasswordData($resetToken, 'Y-m-d H:i:s');
        try {
            $this->accountManagement->resetPassword('customer@example.com', $invalidToken, $password);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $ie) {
            $this->assertEquals('"%fieldName" is required. Enter and try again.', $ie->getRawMessage());
            $this->assertEquals('"resetPasswordLinkToken" is required. Enter and try again.', $ie->getMessage());
            $this->assertEquals('"resetPasswordLinkToken" is required. Enter and try again.', $ie->getLogMessage());
            $this->assertEmpty($ie->getErrors());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordTokenWrongUser()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'new_Password123';
        $this->setResetPasswordData($resetToken, 'Y-m-d H:i:s');
        try {
            $this->accountManagement->resetPassword('invalid-customer@example.com', $resetToken, $password);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals(
                'No such entity with email = invalid-customer@example.com, websiteId = 1',
                $nsee->getMessage()
            );
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordTokenInvalidUserEmail()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'new_Password123';

        $this->setResetPasswordData($resetToken, 'Y-m-d H:i:s');

        try {
            $this->accountManagement->resetPassword('invalid', $resetToken, $password);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $e) {
            $this->assertEquals('No such entity with email = invalid, websiteId = 1', $e->getMessage());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordWithoutEmail()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'new_Password123';
        $this->setResetPasswordData($resetToken, 'Y-m-d H:i:s');
        $this->assertTrue(
            $this->accountManagement->resetPassword(null, $resetToken, $password)
        );
    }
    /**
     * @magentoDataFixture Magento/Customer/_files/two_customers.php
     * @expectedException \Magento\Framework\Exception\State\ExpiredException
     */
    public function testResetPasswordAmbiguousToken()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'new_Password123';
        $this->setResetPasswordData($resetToken, 'Y-m-d H:i:s', 1);
        $this->setResetPasswordData($resetToken, 'Y-m-d H:i:s', 2);
        $this->accountManagement->resetPassword(null, $resetToken, $password);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     */
    public function testResendConfirmation()
    {
        $this->accountManagement->resendConfirmation('customer@needAconfirmation.com', 1);
        //TODO assert
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     */
    public function testResendConfirmationBadWebsiteId()
    {
        try {
            $this->accountManagement->resendConfirmation('customer@needAconfirmation.com', 'notAWebsiteId');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals(
                'No such entity with email = customer@needAconfirmation.com, websiteId = notAWebsiteId',
                $nsee->getMessage()
            );
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResendConfirmationNoEmail()
    {
        try {
            $this->accountManagement->resendConfirmation('wrongemail@example.com', 1);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals(
                'No such entity with email = wrongemail@example.com, websiteId = 1',
                $nsee->getMessage()
            );
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     */
    public function testResendConfirmationNotNeeded()
    {
        $this->accountManagement->resendConfirmation('customer@example.com', 1);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateCustomerException()
    {
        $customerEntity = $this->customerFactory->create();

        try {
            $this->accountManagement->createAccount($customerEntity);
            $this->fail('Expected exception not thrown');
        } catch (InputException $ie) {
            $this->assertEquals('The customer email is missing. Enter and try again.', $ie->getMessage());
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDbIsolation enabled
     */
    public function testCreateNonexistingCustomer()
    {
        $existingCustId = 1;
        $existingCustomer = $this->customerRepository->getById($existingCustId);

        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';
        $customerData = array_merge(
            $existingCustomer->__toArray(),
            [
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'id' => null
            ]
        );
        $customerEntity = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerEntity,
            $customerData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );

        $customerAfter = $this->accountManagement->createAccount($customerEntity, '_aPassword1');
        $this->assertGreaterThan(0, $customerAfter->getId());
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastName, $customerAfter->getLastname());
        $this->accountManagement->authenticate(
            $customerAfter->getEmail(),
            '_aPassword1'
        );
        $attributesBefore = $this->extensibleDataObjectConverter->toFlatArray(
            $existingCustomer,
            [],
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $attributesAfter = $this->extensibleDataObjectConverter->toFlatArray(
            $customerAfter,
            [],
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        // ignore 'updated_at'
        unset($attributesBefore['updated_at']);
        unset($attributesAfter['updated_at']);
        $inBeforeOnly = array_diff_assoc($attributesBefore, $attributesAfter);
        $inAfterOnly = array_diff_assoc($attributesAfter, $attributesBefore);
        $expectedInBefore = [
            'email',
            'firstname',
            'id',
            'lastname',
        ];
        sort($expectedInBefore);
        $actualInBeforeOnly = array_keys($inBeforeOnly);
        sort($actualInBeforeOnly);
        $this->assertEquals($expectedInBefore, $actualInBeforeOnly);
        $expectedInAfter = [
            'created_in',
            'email',
            'firstname',
            'id',
            'lastname',
        ];
        $actualInAfterOnly = array_keys($inAfterOnly);
        foreach ($expectedInAfter as $item) {
            $this->assertContains($item, $actualInAfterOnly);
        }
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateCustomerInServiceVsInModel()
    {
        $email = 'email@example.com';
        $email2 = 'email2@example.com';
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;
        $password = '_aPassword1';

        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->objectManager->create(\Magento\Customer\Model\CustomerFactory::class)->create();
        $customerModel->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId)
            ->setPassword($password);
        $customerModel->save();
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $savedModel = $this->objectManager
            ->create(\Magento\Customer\Model\CustomerFactory::class)
            ->create()
            ->load($customerModel->getId());
        $dataInModel = $savedModel->getData();

        $newCustomerEntity = $this->customerFactory->create()
            ->setEmail($email2)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $customerData = $this->accountManagement->createAccount($newCustomerEntity, $password);
        $this->assertNotNull($customerData->getId());
        $savedCustomer = $this->customerRepository->getById($customerData->getId());

        /** @var \Magento\Framework\Api\SimpleDataObjectConverter $simpleDataObjectConverter */
        $simpleDataObjectConverter = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Api\SimpleDataObjectConverter::class);

        $dataInService = $simpleDataObjectConverter->toFlatArray(
            $savedCustomer,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $expectedDifferences = [
            'created_at',
            'updated_at',
            'email',
            'is_active',
            'entity_id',
            'entity_type_id',
            'password_hash',
            'attribute_set_id',
            'disable_auto_group_change',
            'confirmation',
            'reward_update_notification',
            'reward_warning_notification',
        ];
        foreach ($dataInModel as $key => $value) {
            if (!in_array($key, $expectedDifferences)) {
                if ($value === null) {
                    $this->assertArrayNotHasKey($key, $dataInService);
                } else {
                    if (isset($dataInService[$key])) {
                        $this->assertEquals($value, $dataInService[$key], 'Failed asserting value for ' . $key);
                    }
                }
            }
        }
        $this->assertEquals($email2, $dataInService['email']);
        $this->assertArrayNotHasKey('is_active', $dataInService);
        $this->assertArrayNotHasKey('password_hash', $dataInService);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateNewCustomer()
    {
        $email = 'email@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;

        $newCustomerEntity = $this->customerFactory->create()
            ->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $savedCustomer = $this->accountManagement->createAccount($newCustomerEntity, '_aPassword1');
        $this->assertNotNull($savedCustomer->getId());
        $this->assertEquals($email, $savedCustomer->getEmail());
        $this->assertEquals($storeId, $savedCustomer->getStoreId());
        $this->assertEquals($firstname, $savedCustomer->getFirstname());
        $this->assertEquals($lastname, $savedCustomer->getLastname());
        $this->assertEquals($groupId, $savedCustomer->getGroupId());
        $this->assertTrue(!$savedCustomer->getSuffix());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateNewCustomerWithPasswordHash()
    {
        $email = 'email@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;

        $newCustomerEntity = $this->customerFactory->create()
            ->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        /** @var \Magento\Framework\Math\Random $mathRandom */
        $password = $this->objectManager->get(\Magento\Framework\Math\Random::class)->getRandomString(8);
        /** @var \Magento\Framework\Encryption\EncryptorInterface $encryptor */
        $encryptor = $this->objectManager->get(\Magento\Framework\Encryption\EncryptorInterface::class);
        $passwordHash = $encryptor->getHash($password, true);
        $savedCustomer = $this->accountManagement->createAccountWithPasswordHash(
            $newCustomerEntity,
            $passwordHash
        );
        $this->assertNotNull($savedCustomer->getId());
        $this->assertEquals($email, $savedCustomer->getEmail());
        $this->assertEquals($storeId, $savedCustomer->getStoreId());
        $this->assertEquals($firstname, $savedCustomer->getFirstname());
        $this->assertEquals($lastname, $savedCustomer->getLastname());
        $this->assertEquals($groupId, $savedCustomer->getGroupId());
        $this->assertTrue(!$savedCustomer->getSuffix());
        $this->assertEquals(
            $savedCustomer->getId(),
            $this->accountManagement->authenticate($email, $password)->getId()
        );
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testCreateNewCustomerFromClone()
    {
        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastname = 'Lastsave';

        $existingCustId = 1;
        $existingCustomer = $this->customerRepository->getById($existingCustId);
        $customerEntity = $this->customerFactory->create();
        $this->dataObjectHelper->mergeDataObjects(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            $customerEntity,
            $existingCustomer
        );
        $customerEntity->setEmail($email)
            ->setFirstname($firstName)
            ->setLastname($lastname)
            ->setId(null);

        $customer = $this->accountManagement->createAccount($customerEntity, '_aPassword1');
        $this->assertNotEmpty($customer->getId());
        $this->assertEquals($email, $customer->getEmail());
        $this->assertEquals($firstName, $customer->getFirstname());
        $this->assertEquals($lastname, $customer->getLastname());
        $this->accountManagement->authenticate(
            $customer->getEmail(),
            '_aPassword1',
            true
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testIsEmailAvailable()
    {
        $this->assertFalse($this->accountManagement->isEmailAvailable('customer@example.com', 1));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testIsEmailAvailableNoWebsiteSpecified()
    {
        $this->assertFalse($this->accountManagement->isEmailAvailable('customer@example.com'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testIsEmailAvailableNoWebsiteSpecifiedNonExistent()
    {
        $this->assertTrue($this->accountManagement->isEmailAvailable('nonexistent@example.com'));
    }

    public function testIsEmailAvailableNonExistentEmail()
    {
        $this->assertTrue($this->accountManagement->isEmailAvailable('nonexistent@example.com', 1));
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     * @magentoDataFixture  Magento/Customer/_files/customer_address.php
     * @magentoDataFixture  Magento/Customer/_files/customer_two_addresses.php
     */
    public function testGetDefaultBillingAddress()
    {
        $customerId = 1;
        $address = $this->accountManagement->getDefaultBillingAddress($customerId);

        $expected = $this->dataProcessor->buildOutputDataArray(
            $this->_expectedAddresses[0],
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $result = $this->dataProcessor->buildOutputDataArray(
            $address,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        /*
         * TODO : Data builder / populateWithArray currently does not detect
         * array type and returns street as string instead of array. Need to fix this.
         */
        unset($expected[AddressInterface::STREET]);
        unset($result[AddressInterface::STREET]);
        $this->assertEquals($expected, $result);
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     */
    public function testSaveNewAddressDefaults()
    {
        $customerId = 1;
        /** @var $addressShipping \Magento\Customer\Api\Data\AddressInterface */
        $addressShipping = $this->_expectedAddresses[0]->setId(null);
        $addressShipping->setIsDefaultShipping(true)->setIsDefaultBilling(false)->setCustomerId($customerId);
        //TODO : Will be fixed as part of fixing populate. For now Region is set as Data Object instead of array
        $addressShipping->setRegion($this->_expectedAddresses[0]->getRegion());

        /** @var $addressBilling \Magento\Customer\Api\Data\AddressInterface */
        $addressBilling = $this->_expectedAddresses[1]->setId(null);
        $addressBilling->setIsDefaultBilling(true)->setIsDefaultShipping(false)->setCustomerId($customerId);
        //TODO : Will be fixed as part of fixing populate
        $addressBilling->setRegion($this->_expectedAddresses[1]->getRegion());

        $addressShippingExpected = $this->addressRepository->save($addressShipping);
        $addressBillingExpected = $this->addressRepository->save($addressBilling);
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = $this->objectManager->get(\Magento\Customer\Model\CustomerRegistry::class);
        $customerRegistry->remove(1);
        // Call api under test
        $shippingResponse = $this->accountManagement->getDefaultShippingAddress($customerId);
        $billingResponse = $this->accountManagement->getDefaultBillingAddress($customerId);

        // Verify if the new Shipping address created is same as returned by the api under test :
        // \Magento\Customer\Api\AccountManagementInterface::getDefaultShippingAddress
        $addressShippingExpected = $this->dataProcessor->buildOutputDataArray(
            $addressShippingExpected,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $shippingResponse = $this->dataProcessor->buildOutputDataArray(
            $shippingResponse,
            \Magento\Customer\Api\Data\AddressInterface::class
        );

        // Response should have this set since we save as default shipping
        $addressShippingExpected[AddressInterface::DEFAULT_SHIPPING] = true;
        $this->assertEquals($addressShippingExpected, $shippingResponse);

        // Verify if the new Billing address created is same as returned by the api under test :
        // \Magento\Customer\Api\AccountManagementInterface::getDefaultShippingAddress
        $addressBillingExpected = $this->dataProcessor->buildOutputDataArray(
            $addressBillingExpected,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $billingResponse = $this->dataProcessor->buildOutputDataArray(
            $billingResponse,
            \Magento\Customer\Api\Data\AddressInterface::class
        );

        // Response should have this set since we save as default billing
        $addressBillingExpected[AddressInterface::DEFAULT_BILLING] = true;
        $this->assertEquals($addressBillingExpected, $billingResponse);
    }

    /**
     * @magentoDataFixture  Magento/Customer/_files/customer.php
     */
    public function testGetDefaultAddressesForNonExistentAddress()
    {
        $customerId = 1;
        $this->assertNull($this->accountManagement->getDefaultBillingAddress($customerId));
        $this->assertNull($this->accountManagement->getDefaultShippingAddress($customerId));
    }

    /**
     * Set Rp data to Customer in fixture
     *
     * @param $resetToken
     * @param $date
     * @param int $customerIdFromFixture Which customer to use.
     * @throws \Exception
     */
    protected function setResetPasswordData(
        $resetToken,
        $date,
        int $customerIdFromFixture = 1
    ) {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->objectManager->create(\Magento\Customer\Model\Customer::class);
        $customerModel->load($customerIdFromFixture);
        $customerModel->setRpToken($resetToken);
        $customerModel->setRpTokenCreatedAt(date($date));
        $customerModel->save();
    }
}
