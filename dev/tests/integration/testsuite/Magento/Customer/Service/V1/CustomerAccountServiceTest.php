<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Service\V1;

namespace Magento\Customer\Service\V1;

use Magento\Customer\Service\V1;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Service\V1\Data\SearchCriteria;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Integration test for service layer \Magento\Customer\Service\V1\CustomerAccountService
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea frontend
 */
class CustomerAccountServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerAccountServiceInterface */
    private $_customerAccountService;

    /** @var CustomerAddressServiceInterface needed to setup tests */
    private $_customerAddressService;

    /** @var \Magento\Framework\ObjectManager */
    private $_objectManager;

    /** @var \Magento\Customer\Service\V1\Data\Address[] */
    private $_expectedAddresses;

    /** @var \Magento\Customer\Service\V1\Data\AddressBuilder */
    private $_addressBuilder;

    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder */
    private $_customerBuilder;

    /** @var \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder */
    private $_customerDetailsBuilder;

    protected function setUp()
    {
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->_customerAccountService = $this->_objectManager
            ->create('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $this->_customerAddressService =
            $this->_objectManager->create('Magento\Customer\Service\V1\CustomerAddressServiceInterface');

        $this->_addressBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Data\AddressBuilder');
        $this->_customerBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Data\CustomerBuilder');
        $this->_customerDetailsBuilder =
            $this->_objectManager->create('Magento\Customer\Service\V1\Data\CustomerDetailsBuilder');

        $regionBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Data\RegionBuilder');
        $this->_addressBuilder->setId(1)
            ->setCountryId('US')
            ->setCustomerId(1)
            ->setDefaultBilling(true)
            ->setDefaultShipping(true)
            ->setPostcode('75477')
            ->setRegion(
                $regionBuilder->setRegionCode('AL')->setRegion('Alabama')->setRegionId(1)->create()
            )
            ->setStreet(['Green str, 67'])
            ->setTelephone('3468676')
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address = $this->_addressBuilder->create();

        $this->_addressBuilder->setId(2)
            ->setCountryId('US')
            ->setCustomerId(1)
            ->setDefaultBilling(false)
            ->setDefaultShipping(false)
            ->setPostcode('47676')
            ->setRegion(
                $regionBuilder->setRegionCode('AL')->setRegion('Alabama')->setRegionId(1)->create()
            )
            ->setStreet(['Black str, 48'])
            ->setCity('CityX')
            ->setTelephone('3234676')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address2 = $this->_addressBuilder->create();

        $this->_expectedAddresses = [$address, $address2];
    }

    /**
     * Clean up shared dependencies
     */
    protected function tearDown()
    {
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = $this->_objectManager->get('Magento\Customer\Model\CustomerRegistry');
        //Cleanup customer from registry
        $customerRegistry->remove(1);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testLogin()
    {
        // Customer e-mail and password are pulled from the fixture customer.php
        $customer = $this->_customerAccountService->authenticate('customer@example.com', 'password', true);

        $this->assertSame('customer@example.com', $customer->getEmail());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @expectedException \Magento\Framework\Exception\AuthenticationException
     * @expectedExceptionMessage Invalid login or password
     */
    public function testLoginWrongPassword()
    {
        // Customer e-mail and password are pulled from the fixture customer.php
        $this->_customerAccountService->authenticate('customer@example.com', 'wrongPassword', true);
    }

    /**
     * @expectedException \Magento\Framework\Exception\AuthenticationException
     * @expectedExceptionMessage Invalid login or password
     */
    public function testLoginWrongUsername()
    {
        // Customer e-mail and password are pulled from the fixture customer.php
        $this->_customerAccountService->authenticate('non_existing_user', 'password', true);
    }


    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangePassword()
    {
        $this->_customerAccountService->changePassword(1, 'password', 'new_password');

        $this->_customerAccountService->authenticate('customer@example.com', 'new_password');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @expectedException \Magento\Framework\Exception\AuthenticationException
     * @expectedExceptionMessage Password doesn't match for this account
     */
    public function testChangePasswordWrongPassword()
    {
        $this->_customerAccountService->changePassword(1, 'wrongPassword', 'new_password');
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testChangePasswordWrongUser()
    {
        $this->_customerAccountService->changePassword(4200, 'password', 'new_password');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     * @magentoAppArea frontend
     */
    public function testActivateAccount()
    {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customerModel->load(1);
        // Assert in just one test that the fixture is working
        $this->assertNotNull($customerModel->getConfirmation(), 'New customer needs to be confirmed');

        $this->_customerAccountService->activateCustomer($customerModel->getId(), $customerModel->getConfirmation());

        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
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
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customerModel->load(1);
        $key = $customerModel->getConfirmation();

        try {
            $this->_customerAccountService->activateCustomer($customerModel->getId(), $key . $key);
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
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customerModel->load(1);
        $key = $customerModel->getConfirmation();
        try {
            $this->_customerAccountService->activateCustomer('1234' . $customerModel->getId(), $key);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals('No such entity with customerId = 12341', $nsee->getMessage());
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
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customerModel->load(1);
        $key = $customerModel->getConfirmation();
        $this->_customerAccountService->activateCustomer($customerModel->getId(), $key);
        // activate it one more time to produce an exception
        $this->_customerAccountService->activateCustomer($customerModel->getId(), $key);
    }


    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testValidateResetPasswordLinkToken()
    {
        $this->setResetPasswordData('token', 'Y-m-d');
        $this->_customerAccountService->validateResetPasswordLinkToken(1, 'token');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\State\ExpiredException
     */
    public function testValidateResetPasswordLinkTokenExpired()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $this->setResetPasswordData($resetToken, '1970-01-01');
        $this->_customerAccountService->validateResetPasswordLinkToken(1, $resetToken);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testValidateResetPasswordLinkTokenInvalid()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $invalidToken = 0;
        $this->setResetPasswordData($resetToken, 'Y-m-d');
        try {
            $this->_customerAccountService->validateResetPasswordLinkToken(1, $invalidToken);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $ie) {
            $this->assertEquals(InputException::REQUIRED_FIELD, $ie->getRawMessage());
            $this->assertEquals('resetPasswordLinkToken is a required field.', $ie->getMessage());
            $this->assertEquals('resetPasswordLinkToken is a required field.', $ie->getLogMessage());
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
            $this->_customerAccountService->validateResetPasswordLinkToken(4200, $resetToken);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals('No such entity with customerId = 4200', $nsee->getMessage());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testValidateResetPasswordLinkTokenNull()
    {
        try {
            $this->_customerAccountService->validateResetPasswordLinkToken(1, null);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $ie) {
            $this->assertEquals(InputException::REQUIRED_FIELD, $ie->getRawMessage());
            $this->assertEquals('resetPasswordLinkToken is a required field.', $ie->getMessage());
            $this->assertEquals('resetPasswordLinkToken is a required field.', $ie->getLogMessage());
            $this->assertEmpty($ie->getErrors());
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSendPasswordResetLink()
    {
        $email = 'customer@example.com';

        $this->_customerAccountService->initiatePasswordReset($email, CustomerAccountServiceInterface::EMAIL_RESET, 1);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSendPasswordResetLinkDefaultWebsite()
    {
        $email = 'customer@example.com';

        $this->_customerAccountService->initiatePasswordReset($email, CustomerAccountServiceInterface::EMAIL_RESET);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testSendPasswordResetLinkBadEmailOrWebsite()
    {
        $email = 'foo@example.com';

        try {
            $this->_customerAccountService->initiatePasswordReset(
                $email,
                CustomerAccountServiceInterface::EMAIL_RESET,
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
            $this->_customerAccountService->initiatePasswordReset(
                $email,
                CustomerAccountServiceInterface::EMAIL_RESET
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
        $password = 'new_password';

        $this->setResetPasswordData($resetToken, 'Y-m-d');
        $this->_customerAccountService->resetPassword(1, $resetToken, $password);
        //TODO assert
    }



    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\State\ExpiredException
     */
    public function testResetPasswordTokenExpired()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'new_password';

        $this->setResetPasswordData($resetToken, '1970-01-01');
        $this->_customerAccountService->resetPassword(1, $resetToken, $password);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testResetPasswordTokenInvalid()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $invalidToken = 0;
        $password = 'new_password';

        $this->setResetPasswordData($resetToken, 'Y-m-d');
        try {
            $this->_customerAccountService->resetPassword(1, $invalidToken, $password);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $ie) {
            $this->assertEquals(InputException::REQUIRED_FIELD, $ie->getRawMessage());
            $this->assertEquals('resetPasswordLinkToken is a required field.', $ie->getMessage());
            $this->assertEquals('resetPasswordLinkToken is a required field.', $ie->getLogMessage());
            $this->assertEmpty($ie->getErrors());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordTokenWrongUser()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'new_password';
        $this->setResetPasswordData($resetToken, 'Y-m-d');
        try {
            $this->_customerAccountService->resetPassword(4200, $resetToken, $password);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals('No such entity with customerId = 4200', $nsee->getMessage());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordTokenInvalidUserId()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'new_password';

        $this->setResetPasswordData($resetToken, 'Y-m-d');

        try {
            $this->_customerAccountService->resetPassword(0, $resetToken, $password);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $ie) {
            $this->assertEquals('Invalid value of "0" provided for the customerId field.', $ie->getMessage());
            $this->assertEmpty($ie->getErrors());
        }

    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     */
    public function testResendConfirmation()
    {
        $this->_customerAccountService->resendConfirmation('customer@needAconfirmation.com', 1);
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
            $this->_customerAccountService->resendConfirmation('customer@needAconfirmation.com', 'notAWebsiteId');
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
            $this->_customerAccountService->resendConfirmation('wrongemail@example.com', 1);
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
        $this->_customerAccountService->resendConfirmation('customer@example.com', 1);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerName()
    {
        $customerId = 1;
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';

        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $newCustomer = array_merge(
            $customerDetails->getCustomer()->__toArray(),
            [
                'firstname' => $firstName,
                'lastname' => $lastName,
            ]
        );
        $this->_customerBuilder->populateWithArray($newCustomer);
        $this->_customerDetailsBuilder->setCustomer($this->_customerBuilder->create());
        $this->_customerAccountService->updateCustomer($customerId, $this->_customerDetailsBuilder->create());

        $newCustomerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $this->assertEquals($firstName, $newCustomerDetails->getCustomer()->getFirstname());
        $this->assertEquals($lastName, $newCustomerDetails->getCustomer()->getLastname());
        $this->assertEquals(2, count($newCustomerDetails->getAddresses()));

    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerAddress()
    {
        $customerId = 1;
        $city = 'San Jose';

        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $addresses = $customerDetails->getAddresses();
        $addressId = $addresses[0]->getId();
        $newAddress = array_merge($addresses[0]->__toArray(), ['city' => $city]);

        $this->_addressBuilder->populateWithArray($newAddress);
        $this->_customerDetailsBuilder
            ->setCustomer($customerDetails->getCustomer())
            ->setAddresses([$this->_addressBuilder->create(), $addresses[1]]);
        $this->_customerAccountService->updateCustomer($customerId, $this->_customerDetailsBuilder->create());

        $newCustomerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $this->assertEquals(2, count($newCustomerDetails->getAddresses()));

        foreach ($newCustomerDetails->getAddresses() as $newAddress) {
            if ($newAddress->getId() == $addressId) {
                $this->assertEquals($city, $newAddress->getCity());
            }
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerDeleteOneAddress()
    {
        $customerId = 1;
        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $addresses = $customerDetails->getAddresses();
        $addressIdToRetain = $addresses[1]->getId();

        $this->_customerDetailsBuilder
            ->setCustomer($customerDetails->getCustomer())->setAddresses([$addresses[1]]);

        $this->_customerAccountService->updateCustomer($customerId, $this->_customerDetailsBuilder->create());

        $newCustomerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $this->assertEquals(1, count($newCustomerDetails->getAddresses()));
        $this->assertEquals($addressIdToRetain, $newCustomerDetails->getAddresses()[0]->getId());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testUpdateCustomerDeleteAllAddresses()
    {
        $customerId = 1;
        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $this->_customerDetailsBuilder->setCustomer($customerDetails->getCustomer())
            ->setAddresses([]);
        $this->_customerAccountService->updateCustomer($customerId, $this->_customerDetailsBuilder->create());

        $newCustomerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $this->assertEquals(0, count($newCustomerDetails->getAddresses()));
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomer()
    {
        $existingCustId = 1;

        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastname = 'Lastsave';

        $customerBefore = $this->_customerAccountService->getCustomer($existingCustId);

        $customerData = array_merge($customerBefore->__toArray(), [
            'id' => 1,
            'email' => $email,
            'firstname' => $firstName,
            'lastname' => $lastname,
            'created_in' => 'Admin',
            'password' => 'notsaved'
        ]);
        $this->_customerBuilder->populateWithArray($customerData);
        $modifiedCustomer = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($modifiedCustomer)->create();
        $this->_customerAccountService->updateCustomer($existingCustId, $customerDetails);
        $customerAfter = $this->_customerAccountService->getCustomer($existingCustId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastname, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $passwordFromFixture = 'password';
        $this->_customerAccountService->authenticate($customerAfter->getEmail(), $passwordFromFixture);
        $attributesBefore = \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray($customerBefore);
        $attributesAfter = \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray($customerAfter);
        // ignore 'updated_at'
        unset($attributesBefore['updated_at']);
        unset($attributesAfter['updated_at']);
        $inBeforeOnly = array_diff_assoc($attributesBefore, $attributesAfter);
        $inAfterOnly = array_diff_assoc($attributesAfter, $attributesBefore);
        $expectedInBefore = [
            'firstname',
            'lastname',
            'email',
        ];
        foreach ($expectedInBefore as $key) {
            $this->assertContains($key, array_keys($inBeforeOnly));
        }
        $this->assertContains('created_in', array_keys($inAfterOnly));
        $this->assertContains('firstname', array_keys($inAfterOnly));
        $this->assertContains('lastname', array_keys($inAfterOnly));
        $this->assertContains('email', array_keys($inAfterOnly));
        $this->assertNotContains('password_hash', array_keys($inAfterOnly));
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomerWithoutChangingPassword()
    {
        $existingCustId = 1;

        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';

        $customerBefore = $this->_customerAccountService->getCustomer($existingCustId);
        $customerData = array_merge(
            $customerBefore->__toArray(),
            [
                'id' => 1,
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'created_in' => 'Admin'
            ]
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $modifiedCustomer = $this->_customerBuilder->create();

        $customerDetails = $this->_customerDetailsBuilder->setCustomer($modifiedCustomer)->create();
        $this->_customerAccountService->updateCustomer($existingCustId, $customerDetails);
        $customerAfter = $this->_customerAccountService->getCustomer($existingCustId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastName, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $this->_customerAccountService->authenticate(
            $customerAfter->getEmail(),
            'password',
            true
        );
        $attributesBefore = \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray($customerBefore);
        $attributesAfter = \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray($customerAfter);
        // ignore 'updated_at'
        unset($attributesBefore['updated_at']);
        unset($attributesAfter['updated_at']);
        $inBeforeOnly = array_diff_assoc($attributesBefore, $attributesAfter);
        $inAfterOnly = array_diff_assoc($attributesAfter, $attributesBefore);
        $expectedInBefore = [
            'firstname',
            'lastname',
            'email',
        ];
        sort($expectedInBefore);
        $actualInBeforeOnly = array_keys($inBeforeOnly);
        sort($actualInBeforeOnly);
        $this->assertEquals($expectedInBefore, $actualInBeforeOnly);
        $expectedInAfter = [
            'firstname',
            'lastname',
            'email',
            'created_in',
        ];
        sort($expectedInAfter);
        $actualInAfterOnly = array_keys($inAfterOnly);
        sort($actualInAfterOnly);
        $this->assertEquals($expectedInAfter, $actualInAfterOnly);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testUpdateCustomerPasswordCannotSetThroughAttributeSetting()
    {
        $existingCustId = 1;

        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';

        $customerBefore = $this->_customerAccountService->getCustomer($existingCustId);
        $customerData = array_merge(
            $customerBefore->__toArray(),
            [
                'id' => 1,
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'created_in' => 'Admin',
                'password' => 'aPassword'
            ]
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $modifiedCustomer = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($modifiedCustomer)->create();
        $this->_customerAccountService->updateCustomer($existingCustId, $customerDetails);
        $customerAfter = $this->_customerAccountService->getCustomer($existingCustId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastName, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $this->_customerAccountService->authenticate(
            $customerAfter->getEmail(),
            'password',
            true
        );
        $attributesBefore = \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray($customerBefore);
        $attributesAfter = \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray($customerAfter);
        // ignore 'updated_at'
        unset($attributesBefore['updated_at']);
        unset($attributesAfter['updated_at']);
        $inBeforeOnly = array_diff_assoc($attributesBefore, $attributesAfter);
        $inAfterOnly = array_diff_assoc($attributesAfter, $attributesBefore);
        $expectedInBefore = [
            'firstname',
            'lastname',
            'email',
        ];
        sort($expectedInBefore);
        $actualInBeforeOnly = array_keys($inBeforeOnly);
        sort($actualInBeforeOnly);
        $this->assertEquals($expectedInBefore, $actualInBeforeOnly);
        $expectedInAfter = [
            'firstname',
            'lastname',
            'email',
            'created_in',
        ];
        sort($expectedInAfter);
        $actualInAfterOnly = array_keys($inAfterOnly);
        sort($actualInAfterOnly);
        $this->assertEquals($expectedInAfter, $actualInAfterOnly);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateCustomerException()
    {
        $customerEntity = $this->_customerBuilder->create();

        try {
            $customerDetails = $this->_customerDetailsBuilder->setCustomer($customerEntity)->create();
            $this->_customerAccountService->createCustomer($customerDetails);
            $this->fail('Expected exception not thrown');
        } catch (InputException $ie) {
            $this->assertEquals(InputException::DEFAULT_MESSAGE, $ie->getMessage());
            $errors = $ie->getErrors();
            $this->assertCount(3, $errors);
            $this->assertEquals('firstname is a required field.', $errors[0]->getLogMessage());
            $this->assertEquals('lastname is a required field.', $errors[1]->getLogMessage());
            $this->assertEquals('Invalid value of "" provided for the email field.', $errors[2]->getLogMessage());
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
        $existingCustomer = $this->_customerAccountService->getCustomer($existingCustId);

        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';
        $customerData = array_merge(
            $existingCustomer->__toArray(),
            [
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'created_in' => 'Admin',
                'id' => null
            ]
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();

        $customerDetails = $this->_customerDetailsBuilder->setCustomer($customerEntity)->create();
        $customerAfter = $this->_customerAccountService->createCustomer($customerDetails, 'aPassword');
        $this->assertGreaterThan(0, $customerAfter->getId());
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastName, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $this->_customerAccountService->authenticate(
            $customerAfter->getEmail(),
            'aPassword',
            true
        );
        $attributesBefore = \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray($existingCustomer);
        $attributesAfter = \Magento\Framework\Service\ExtensibleDataObjectConverter::toFlatArray($customerAfter);
        // ignore 'updated_at'
        unset($attributesBefore['updated_at']);
        unset($attributesAfter['updated_at']);
        $inBeforeOnly = array_diff_assoc($attributesBefore, $attributesAfter);
        $inAfterOnly = array_diff_assoc($attributesAfter, $attributesBefore);
        $expectedInBefore = [
            'email',
            'firstname',
            'id',
            'lastname'
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
        sort($expectedInAfter);
        $actualInAfterOnly = array_keys($inAfterOnly);
        sort($actualInAfterOnly);
        $this->assertEquals($expectedInAfter, $actualInAfterOnly);
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
        $password = 'aPassword';

        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\CustomerFactory')->create();
        $customerModel->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId)
            ->setPassword($password);
        $customerModel->save();
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $savedModel = $this->_objectManager
            ->create('Magento\Customer\Model\CustomerFactory')
            ->create()
            ->load($customerModel->getId());
        $dataInModel = $savedModel->getData();

        $this->_customerBuilder
            ->setEmail($email2)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $newCustomerEntity = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($newCustomerEntity)->create();
        $customerData = $this->_customerAccountService->createCustomer($customerDetails, $password);
        $this->assertNotNull($customerData->getId());
        $savedCustomer = $this->_customerAccountService->getCustomer($customerData->getId());
        $dataInService = \Magento\Framework\Service\SimpleDataObjectConverter::toFlatArray($savedCustomer);
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
            'reward_warning_notification'
        ];
        foreach ($dataInModel as $key => $value) {
            if (!in_array($key, $expectedDifferences)) {
                if (is_null($value)) {
                    $this->assertArrayNotHasKey($key, $dataInService);
                } else {
                    $this->assertEquals($value, $dataInService[$key], 'Failed asserting value for ' . $key);
                }
            }
        }
        $this->assertEquals($email2, $dataInService['email']);
        $this->assertArrayNotHasKey('is_active', $dataInService);
        $this->assertArrayNotHasKey('updated_at', $dataInService);
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

        $this->_customerBuilder
            ->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $newCustomerEntity = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($newCustomerEntity)->create();
        $savedCustomer = $this->_customerAccountService->createCustomer($customerDetails, 'aPassword');
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

        $this->_customerBuilder->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $newCustomerEntity = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($newCustomerEntity)->create();
        /** @var \Magento\Framework\Math\Random $mathRandom */
        $password = $this->_objectManager->get('Magento\Framework\Math\Random')->getRandomString(
            CustomerAccountServiceInterface::MIN_PASSWORD_LENGTH
        );
        /** @var \Magento\Framework\Encryption\EncryptorInterface $encryptor */
        $encryptor = $this->_objectManager->get('Magento\Framework\Encryption\EncryptorInterface');
        $passwordHash = $encryptor->getHash($password);
        $savedCustomer = $this->_customerAccountService->createCustomerWithPasswordHash(
            $customerDetails,
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
            $this->_customerAccountService->authenticate($email, $password)->getId()
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
        $existingCustomer = $this->_customerAccountService->getCustomer($existingCustId);
        $customerData = array_merge(
            $existingCustomer->__toArray(),
            [
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastname,
                'created_in' => 'Admin',
                'id' => null
            ]
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($customerEntity)->create();

        $customer = $this->_customerAccountService->createCustomer($customerDetails, 'aPassword');
        $this->assertNotEmpty($customer->getId());
        $this->assertEquals($email, $customer->getEmail());
        $this->assertEquals($firstName, $customer->getFirstname());
        $this->assertEquals($lastname, $customer->getLastname());
        $this->assertEquals('Admin', $customer->getCreatedIn());
        $this->_customerAccountService->authenticate(
            $customer->getEmail(),
            'aPassword',
            true
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateCustomerNewThenUpdateFirstName()
    {
        $email = 'first_last@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;

        $this->_customerBuilder
            ->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $newCustomerEntity = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($newCustomerEntity)->create();

        $customer = $this->_customerAccountService->createCustomer($customerDetails, 'aPassword');

        $this->_customerBuilder->populate($customer);
        $this->_customerBuilder->setFirstname('Tested');
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($this->_customerBuilder->create())->create();
        $this->_customerAccountService->updateCustomer($customer->getId(), $customerDetails);

        $customer = $this->_customerAccountService->getCustomer($customer->getId());

        $this->assertEquals('Tested', $customer->getFirstname());
        $this->assertEquals($lastname, $customer->getLastname());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testGetCustomer()
    {
        // _files/customer.php sets the customer id to 1
        $customer = $this->_customerAccountService->getCustomer(1);

        // All these expected values come from _files/customer.php fixture
        $this->assertEquals(1, $customer->getId());
        $this->assertEquals('customer@example.com', $customer->getEmail());
        $this->assertEquals('Firstname', $customer->getFirstname());
        $this->assertEquals('Lastname', $customer->getLastname());
    }

    public function testGetCustomerNotExist()
    {
        try {
            // No fixture, so customer with id 1 shouldn't exist, exception should be thrown
            $this->_customerAccountService->getCustomer(1);
            $this->fail('Did not throw expected exception.');
        } catch (NoSuchEntityException $nsee) {
            $this->assertEquals('No such entity with customerId = 1', $nsee->getMessage());
        }
    }

    /**
     * @param mixed $custId
     * @dataProvider invalidCustomerIdsDataProvider
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId =
     */
    public function testGetCustomerInvalidIds($custId)
    {
        $this->_customerAccountService->getCustomer($custId);
    }

    public function invalidCustomerIdsDataProvider()
    {
        return [
            ['ab'],
            [' '],
            [-1],
            [0],
            [' 1234'],
            ['-1'],
            ['0'],
        ];
    }

    /**
     * @param \Magento\Framework\Service\V1\Data\Filter[] $filters
     * @param \Magento\Framework\Service\V1\Data\Filter[] $filterGroup
     * @param array $expectedResult array of expected results indexed by ID
     *
     * @dataProvider searchCustomersDataProvider
     *
     * @magentoDataFixture Magento/Customer/_files/three_customers.php
     * @magentoDbIsolation enabled
     */
    public function testSearchCustomers($filters, $filterGroup, $expectedResult)
    {
        /** @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()->create(
            'Magento\Framework\Service\V1\Data\SearchCriteriaBuilder'
        );
        foreach ($filters as $filter) {
            $searchBuilder->addFilter([$filter]);
        }
        if (!is_null($filterGroup)) {
            $searchBuilder->addFilter($filterGroup);
        }

        $searchResults = $this->_customerAccountService->searchCustomers($searchBuilder->create());

        $this->assertEquals(count($expectedResult), $searchResults->getTotalCount());

        /** @var $item Data\CustomerDetails */
        foreach ($searchResults->getItems() as $item) {
            $this->assertEquals(
                $expectedResult[$item->getCustomer()->getId()]['email'],
                $item->getCustomer()->getEmail()
            );
            $this->assertEquals(
                $expectedResult[$item->getCustomer()->getId()]['firstname'],
                $item->getCustomer()->getFirstname()
            );
            unset($expectedResult[$item->getCustomer()->getId()]);
        }
    }

    public function searchCustomersDataProvider()
    {
        $builder = Bootstrap::getObjectManager()->create('\Magento\Framework\Service\V1\Data\FilterBuilder');
        return [
            'Customer with specific email' => [
                [$builder->setField('email')->setValue('customer@search.example.com')->create()],
                null,
                [1 => ['email' => 'customer@search.example.com', 'firstname' => 'Firstname']]
            ],
            'Customer with specific first name' => [
                [$builder->setField('firstname')->setValue('Firstname2')->create()],
                null,
                [2 => ['email' => 'customer2@search.example.com', 'firstname' => 'Firstname2']]
            ],
            'Customers with either email' => [
                [],
                [
                    $builder->setField('firstname')->setValue('Firstname')->create(),
                    $builder->setField('firstname')->setValue('Firstname2')->create()
                ],
                [
                    1 => ['email' => 'customer@search.example.com', 'firstname' => 'Firstname'],
                    2 => ['email' => 'customer2@search.example.com', 'firstname' => 'Firstname2']
                ]
            ],
            'Customers created since' => [
                [
                    $builder->setField('created_at')->setValue('2011-02-28 15:52:26')
                        ->setConditionType('gt')->create()
                ],
                [],
                [
                    1 => ['email' => 'customer@search.example.com', 'firstname' => 'Firstname'],
                    3 => ['email' => 'customer3@search.example.com', 'firstname' => 'Firstname3']
                ]
            ]
        ];
    }

    /**
     * Test ordering
     *
     * @magentoDataFixture Magento/Customer/_files/three_customers.php
     * @magentoDbIsolation enabled
     */
    public function testSearchCustomersOrder()
    {
        /** @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = Bootstrap::getObjectManager()
            ->create('Magento\Framework\Service\V1\Data\SearchCriteriaBuilder');

        // Filter for 'firstname' like 'First'
        $filterBuilder = $this->_objectManager->create('\Magento\Framework\Service\V1\Data\FilterBuilder');
        $firstnameFilter = $filterBuilder->setField('firstname')
            ->setConditionType('like')
            ->setValue('First%')
            ->create();
        $searchBuilder->addFilter([$firstnameFilter]);
        // Search ascending order
        $sortOrderBuilder = $this->_objectManager->create('\Magento\Framework\Service\V1\Data\SortOrderBuilder');
        $sortOrder = $sortOrderBuilder
            ->setField('lastname')
            ->setDirection(SearchCriteria::SORT_ASC)
            ->create();
        $searchBuilder->addSortOrder($sortOrder);
        $searchResults = $this->_customerAccountService->searchCustomers($searchBuilder->create());
        $this->assertEquals(3, $searchResults->getTotalCount());
        $this->assertEquals('Lastname', $searchResults->getItems()[0]->getCustomer()->getLastname());
        $this->assertEquals('Lastname2', $searchResults->getItems()[1]->getCustomer()->getLastname());
        $this->assertEquals('Lastname3', $searchResults->getItems()[2]->getCustomer()->getLastname());

        // Search descending order
        $sortOrder = $sortOrderBuilder
            ->setField('lastname')
            ->setDirection(SearchCriteria::SORT_DESC)
            ->create();
        $searchBuilder->addSortOrder($sortOrder);
        $searchResults = $this->_customerAccountService->searchCustomers($searchBuilder->create());
        $this->assertEquals('Lastname3', $searchResults->getItems()[0]->getCustomer()->getLastname());
        $this->assertEquals('Lastname2', $searchResults->getItems()[1]->getCustomer()->getLastname());
        $this->assertEquals('Lastname', $searchResults->getItems()[2]->getCustomer()->getLastname());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     * @magentoAppIsolation enabled
     */
    public function testGetCustomerDetails()
    {
        $customerDetails = $this->_customerAccountService->getCustomerDetails(1);

        $customer = $customerDetails->getCustomer();
        // All these expected values come from _files/customer.php fixture
        $this->assertEquals(1, $customer->getId());
        $this->assertEquals('customer@example.com', $customer->getEmail());
        $this->assertEquals('test firstname', $customer->getFirstname());
        $this->assertEquals('test lastname', $customer->getLastname());
        $this->assertEquals(3, count($customerDetails->getAddresses()));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     * @magentoAppIsolation enabled
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetCustomerDetailsWithException()
    {
        $customerDetails = $this->_customerAccountService->getCustomerDetails(20);

        $customerDetails->getCustomer();
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testDeleteCustomer()
    {
        // _files/customer.php sets the customer id to 1
        $this->_customerAccountService->deleteCustomer(1);
        //Verify if the customer is deleted
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            'No such entity with customerId = 1'
        );
        $this->_customerAccountService->getCustomer(1);
    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId = 1
     */
    public function testDeleteCustomerWithAddress()
    {
        //Verify address is created for the customer;
        $result = $this->_customerAddressService->getAddresses(1);
        $this->assertEquals(2, count($result));
        // _files/customer.php sets the customer id to 1
        $this->_customerAccountService->deleteCustomer(1);

        // Verify by directly loading the address by id
        $this->verifyDeletedAddress(1);
        $this->verifyDeletedAddress(2);

        //Verify by calling the Address Service. This will throw the expected exception since customerId doesn't exist
        $result = $this->_customerAddressService->getAddresses(1);
    }

    /**
     * Check if the Address with the give addressid is deleted
     *
     * @param int $addressId
     */
    protected function verifyDeletedAddress($addressId)
    {
        /** @var $addressFactory \Magento\Customer\Model\AddressFactory */
        $addressFactory = $this->_objectManager
            ->create('Magento\Customer\Model\AddressFactory');
        $addressModel = $addressFactory->create()->load($addressId);
        $addressData = $addressModel->getData();
        $this->assertTrue(empty($addressData));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testIsEmailAvailable()
    {
        $this->assertFalse($this->_customerAccountService->isEmailAvailable('customer@example.com', 1));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testIsEmailAvailableNoWebsiteSpecified()
    {
        $this->assertFalse($this->_customerAccountService->isEmailAvailable('customer@example.com'));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testIsEmailAvailableNoWebsiteSpecifiedNonExistent()
    {
        $this->assertTrue($this->_customerAccountService->isEmailAvailable('nonexistent@example.com'));
    }

    public function testIsEmailAvailableNonExistentEmail()
    {
        $this->assertTrue($this->_customerAccountService->isEmailAvailable('nonexistent@example.com', 1));
    }

    /**
     * @param $email
     * @param $websiteId
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @dataProvider getValidEmailDataProvider
     */
    public function testGetCustomerByEmail($email, $websiteId)
    {
        /** @var \Magento\Customer\Service\V1\Data\Customer $customer */
        $customer = $this->_customerAccountService->getCustomerByEmail($email, $websiteId);

        // All these expected values come from _files/customer.php fixture
        $this->assertEquals(1, $customer->getId());
        $this->assertEquals('customer@example.com', $customer->getEmail());
        $this->assertEquals('Firstname', $customer->getFirstname());
        $this->assertEquals('Lastname', $customer->getLastname());
    }

    /**
     * @param $email
     * @param $websiteId
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider getInvalidEmailDataProvider
     */
    public function testGetCustomerByEmailWithException($email, $websiteId)
    {
        $this->_customerAccountService->getCustomerByEmail($email, $websiteId);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_non_default_website_id.php
     */
    public function testGetCustomerByEmailWithNonDefaultWebsiteId()
    {
        $email = 'customer2@example.com';
        /** @var \Magento\Store\Model\Website $website */
        $website = Bootstrap::getObjectManager()->get('Magento\Store\Model\Website');
        $websiteId = $website->load('newwebsite')->getId();
        /** @var \Magento\Customer\Service\V1\Data\Customer $customer */
        $customer = $this->_customerAccountService->getCustomerByEmail($email, $websiteId);

        // All these expected values come from _files/customer.php fixture
        $this->assertEquals(1, $customer->getId());
        $this->assertEquals($email, $customer->getEmail());
        $this->assertEquals('Firstname', $customer->getFirstname());
        $this->assertEquals('Lastname', $customer->getLastname());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     * @magentoAppIsolation enabled
     * @dataProvider getValidEmailDataProvider
     */
    public function testGetCustomerDetailsByEmail($email, $websiteId)
    {
        $customerDetails = $this->_customerAccountService->getCustomerDetailsByEmail($email, $websiteId);
        $customer = $customerDetails->getCustomer();

        $this->assertEquals(1, $customer->getId());
        $this->assertEquals('customer@example.com', $customer->getEmail());
        $this->assertEquals('test firstname', $customer->getFirstname());
        $this->assertEquals('test lastname', $customer->getLastname());
        $this->assertEquals(3, count($customerDetails->getAddresses()));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_non_default_website_id.php
     */
    public function testGetCustomerDetailsByEmailWithNonDefaultWebsiteId()
    {
        /** @var \Magento\Customer\Service\V1\Data\Customer $customer */
        $email = 'customer2@example.com';
        /** @var \Magento\Store\Model\Website $website */
        $website = Bootstrap::getObjectManager()->get('Magento\Store\Model\Website');
        $websiteId = $website->load('newwebsite')->getId();
        $customerDetails = $this->_customerAccountService->getCustomerDetailsByEmail($email, $websiteId);
        $customer = $customerDetails->getCustomer();

        $this->assertEquals(1, $customer->getId());
        $this->assertEquals($email, $customer->getEmail());
        $this->assertEquals('Firstname', $customer->getFirstname());
        $this->assertEquals('Lastname', $customer->getLastname());
        $this->assertEquals(3, count($customerDetails->getAddresses()));
    }

    /**
     * @return array
     *
     */
    public function getValidEmailDataProvider()
    {
        /** @var \Magento\Framework\StoreManagerInterface  $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get('Magento\Framework\StoreManagerInterface');
        $defaultWebsiteId = $storeManager->getStore()->getWebsiteId();
        return [
            'valid email' => ['customer@example.com', null],
            'default websiteId' => ['customer@example.com', $defaultWebsiteId],
        ];
    }

    /**
     * @return array
     *
     */
    public function getInvalidEmailDataProvider()
    {
        return [
            'invalid email' => ['nonexistent@example.com', null],
            'invalid websiteId' => ['customer@example.com', 123456],
        ];
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer_non_default_website_id.php
     */
    public function testUpdateCustomerDetailsByEmail()
    {
        $customerId = 1;
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';
        $newEmail = 'newcustomeremail@example.com';
        $city = 'San Jose';

        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $website = Bootstrap::getObjectManager()->get('Magento\Store\Model\Website');
        $websiteId = $website->load('newwebsite')->getId();
        $email = $customerDetails->getCustomer()->getEmail();
        $customerData = array_merge(
            $customerDetails->getCustomer()->__toArray(),
            [
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $newEmail,
                'id' => null
            ]
        );
        $addresses = $customerDetails->getAddresses();
        $addressId = $addresses[0]->getId();
        $newAddress = array_merge($addresses[0]->__toArray(), ['city' => $city]);
        $this->_customerBuilder->populateWithArray($customerData);
        $this->_addressBuilder->populateWithArray($newAddress);
        $this->_customerDetailsBuilder->setCustomer(($this->_customerBuilder->create()))
            ->setAddresses([$this->_addressBuilder->create(), $addresses[1]]);

        $this->_customerAccountService->updateCustomerByEmail(
            $email,
            $this->_customerDetailsBuilder->create(),
            $websiteId
        );

        $updatedCustomerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $updateCustomerData = $updatedCustomerDetails->getCustomer();
        $this->assertEquals($firstName, $updateCustomerData->getFirstname());
        $this->assertEquals($lastName, $updateCustomerData->getLastname());
        $this->assertEquals($newEmail, $updateCustomerData->getEmail());
        $this->assertEquals(2, count($updatedCustomerDetails->getAddresses()));

        foreach ($updatedCustomerDetails->getAddresses() as $newAddress) {
            if ($newAddress->getId() == $addressId) {
                $this->assertEquals($city, $newAddress->getCity());
            }
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @expectedException \Magento\Framework\Exception\StateException
     */
    public function testUpdateCustomerDetailsByEmailWithException()
    {
        $customerId = 1;
        $customerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $email = $customerDetails->getCustomer()->getEmail();
        $customerData = array_merge(
            $customerDetails->getCustomer()->__toArray(),
            [
                'firstname' => 'fname',
                'id' => 1234567
            ]
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $this->_customerDetailsBuilder->setCustomer(($this->_customerBuilder->create()))->setAddresses([]);
        $this->_customerAccountService->updateCustomerByEmail($email, $this->_customerDetailsBuilder->create());

    }

    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoAppIsolation enabled
     */
    public function testDeleteCustomerByEmail()
    {
        // _files/customer.php sets the customer email to customer@example.com
        $this->_customerAccountService->deleteCustomerByEmail('customer@example.com');
        //Verify if the customer is deleted
        $this->setExpectedException(
            'Magento\Framework\Exception\NoSuchEntityException',
            'No such entity with email = customer@example.com'
        );
        $this->_customerAccountService->getCustomerByEmail('customer@example.com');
    }

    /**
     * Set Rp data to Customer in fixture
     *
     * @param $resetToken
     * @param $date
     */
    protected function setResetPasswordData($resetToken, $date)
    {
        $customerIdFromFixture = 1;
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customerModel->load($customerIdFromFixture);
        $customerModel->setRpToken($resetToken);
        $customerModel->setRpTokenCreatedAt(date($date));
        $customerModel->save();
    }
}
