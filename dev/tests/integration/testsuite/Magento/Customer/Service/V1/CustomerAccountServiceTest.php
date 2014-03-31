<?php
namespace Magento\Customer\Service\V1;

use Magento\Customer\Service\V1;
use Magento\Exception\InputException;
use Magento\Exception\NoSuchEntityException;
use Magento\Exception\StateException;

/**
 * Integration test for service layer \Magento\Customer\Service\V1\CustomerAccountService
 *
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

    /** @var \Magento\ObjectManager */
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
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_customerAccountService = $this->_objectManager->create(
            'Magento\Customer\Service\V1\CustomerAccountServiceInterface'
        );
        $this->_customerAddressService = $this->_objectManager->create(
            'Magento\Customer\Service\V1\CustomerAddressServiceInterface'
        );

        $this->_addressBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Data\AddressBuilder');
        $this->_customerBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Data\CustomerBuilder');
        $this->_customerDetailsBuilder = $this->_objectManager->create(
            'Magento\Customer\Service\V1\Data\CustomerDetailsBuilder'
        );

        $this->_addressBuilder->setId(
            1
        )->setCountryId(
            'US'
        )->setCustomerId(
            1
        )->setDefaultBilling(
            true
        )->setDefaultShipping(
            true
        )->setPostcode(
            '75477'
        )->setRegion(
            (new V1\Data\RegionBuilder())->setRegionCode('AL')->setRegion('Alabama')->setRegionId(1)->create()
        )->setStreet(
            array('Green str, 67')
        )->setTelephone(
            '3468676'
        )->setCity(
            'CityM'
        )->setFirstname(
            'John'
        )->setLastname(
            'Smith'
        );
        $address = $this->_addressBuilder->create();

        $this->_addressBuilder->setId(
            2
        )->setCountryId(
            'US'
        )->setCustomerId(
            1
        )->setDefaultBilling(
            false
        )->setDefaultShipping(
            false
        )->setPostcode(
            '47676'
        )->setRegion(
            (new V1\Data\RegionBuilder())->setRegionCode('AL')->setRegion('Alabama')->setRegionId(1)->create()
        )->setStreet(
            array('Black str, 48')
        )->setCity(
            'CityX'
        )->setTelephone(
            '3234676'
        )->setFirstname(
            'John'
        )->setLastname(
            'Smith'
        );
        $address2 = $this->_addressBuilder->create();

        $this->_expectedAddresses = array($address, $address2);
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
     * @expectedException \Magento\Exception\AuthenticationException
     * @expectedExceptionMessage Invalid login or password
     */
    public function testLoginWrongPassword()
    {
        // Customer e-mail and password are pulled from the fixture customer.php
        $this->_customerAccountService->authenticate('customer@example.com', 'wrongPassword', true);
    }

    /**
     * @expectedException \Magento\Exception\AuthenticationException
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
     * @expectedException \Magento\Exception\AuthenticationException
     * @expectedExceptionMessage Password doesn't match for this account
     */
    public function testChangePasswordWrongPassword()
    {
        $this->_customerAccountService->changePassword(1, 'wrongPassword', 'new_password');
    }

    /**
     * @expectedException \Magento\Exception\NoSuchEntityException
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
     *
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::INPUT_MISMATCH
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
            $expectedParams = array(
                array('code' => StateException::INPUT_MISMATCH, 'fieldName' => 'confirmation', 'value' => $key . $key)
            );
            $this->assertEquals($expectedParams, $ie->getParams());
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
            $expectedParams = array('customerId' => '12341');
            $this->assertEquals($expectedParams, $nsee->getParams());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     * @magentoAppArea frontend
     *
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::INVALID_STATE
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
     *
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::EXPIRED
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
            $expectedParams = array(
                array(
                    'value' => $invalidToken,
                    'fieldName' => 'resetPasswordLinkToken',
                    'code' => InputException::INVALID_FIELD_VALUE
                )
            );
            $this->assertEquals($expectedParams, $ie->getParams());
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
            $expectedParams = array('customerId' => '4200');
            $this->assertEquals($expectedParams, $nsee->getParams());
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
            $expectedParams = array(
                array(
                    'value' => null,
                    'fieldName' => 'resetPasswordLinkToken',
                    'code' => InputException::INVALID_FIELD_VALUE
                )
            );
            $this->assertEquals($expectedParams, $ie->getParams());
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSendPasswordResetLink()
    {
        $email = 'customer@example.com';

        $this->_customerAccountService->initiatePasswordReset($email, 1, CustomerAccountServiceInterface::EMAIL_RESET);
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
                0,
                CustomerAccountServiceInterface::EMAIL_RESET
            );
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $expectedParams = array('email' => $email, 'websiteId' => 0);
            $this->assertEquals($expectedParams, $nsee->getParams());
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
     *
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::EXPIRED
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
            $expectedParams = array(
                array(
                    'value' => $invalidToken,
                    'fieldName' => 'resetPasswordLinkToken',
                    'code' => InputException::INVALID_FIELD_VALUE
                )
            );
            $this->assertEquals($expectedParams, $ie->getParams());
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
            $expectedParams = array('customerId' => '4200');
            $this->assertEquals($expectedParams, $nsee->getParams());
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
            $expectedParams = array(
                array('value' => 0, 'fieldName' => 'customerId', 'code' => InputException::INVALID_FIELD_VALUE)
            );
            $this->assertEquals($expectedParams, $ie->getParams());
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
            $expectedParams = array('email' => 'customer@needAconfirmation.com', 'websiteId' => 'notAWebsiteId');
            $this->assertEquals($expectedParams, $nsee->getParams());
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
            $expectedParams = array('email' => 'wrongemail@example.com', 'websiteId' => '1');
            $this->assertEquals($expectedParams, $nsee->getParams());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::INVALID_STATE
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
            array('firstname' => $firstName, 'lastname' => $lastName)
        );
        $this->_customerBuilder->populateWithArray($newCustomer);
        $this->_customerDetailsBuilder->setCustomer($this->_customerBuilder->create());
        $this->_customerAccountService->updateCustomer($this->_customerDetailsBuilder->create());

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
        $newAddress = array_merge($addresses[0]->__toArray(), array('city' => $city));

        $this->_addressBuilder->populateWithArray($newAddress);
        $this->_customerDetailsBuilder->setCustomer(
            $customerDetails->getCustomer()
        )->setAddresses(
            array($this->_addressBuilder->create(), $addresses[1])
        );
        $this->_customerAccountService->updateCustomer($this->_customerDetailsBuilder->create());

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

        $this->_customerDetailsBuilder->setCustomer(
            $customerDetails->getCustomer()
        )->setAddresses(
            array($addresses[1])
        );

        $this->_customerAccountService->updateCustomer($this->_customerDetailsBuilder->create());

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
        $this->_customerDetailsBuilder->setCustomer($customerDetails->getCustomer())->setAddresses(array());
        $this->_customerAccountService->updateCustomer($this->_customerDetailsBuilder->create());

        $newCustomerDetails = $this->_customerAccountService->getCustomerDetails($customerId);
        $this->assertEquals(0, count($newCustomerDetails->getAddresses()));
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSaveCustomer()
    {
        $existingCustId = 1;

        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastname = 'Lastsave';

        $customerBefore = $this->_customerAccountService->getCustomer($existingCustId);

        $customerData = array_merge(
            $customerBefore->__toArray(),
            array(
                'id' => 1,
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastname,
                'created_in' => 'Admin',
                'password' => 'notsaved'
            )
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $modifiedCustomer = $this->_customerBuilder->create();

        $returnedCustomerId = $this->_customerAccountService->saveCustomer($modifiedCustomer, 'aPassword');
        $this->assertEquals($existingCustId, $returnedCustomerId);
        $customerAfter = $this->_customerAccountService->getCustomer($existingCustId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastname, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $this->_customerAccountService->authenticate($customerAfter->getEmail(), 'aPassword', true);
        $attributesBefore = \Magento\Service\DataObjectConverter::toFlatArray($customerBefore);
        $attributesAfter = \Magento\Service\DataObjectConverter::toFlatArray($customerAfter);
        // ignore 'updated_at'
        unset($attributesBefore['updated_at']);
        unset($attributesAfter['updated_at']);
        $inBeforeOnly = array_diff_assoc($attributesBefore, $attributesAfter);
        $inAfterOnly = array_diff_assoc($attributesAfter, $attributesBefore);
        $expectedInBefore = array('firstname', 'lastname', 'email');
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
    public function testSaveCustomerWithoutChangingPassword()
    {
        $existingCustId = 1;

        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';

        $customerBefore = $this->_customerAccountService->getCustomer($existingCustId);
        $customerData = array_merge(
            $customerBefore->__toArray(),
            array(
                'id' => 1,
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'created_in' => 'Admin'
            )
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $modifiedCustomer = $this->_customerBuilder->create();

        $returnedCustomerId = $this->_customerAccountService->saveCustomer($modifiedCustomer);
        $this->assertEquals($existingCustId, $returnedCustomerId);
        $customerAfter = $this->_customerAccountService->getCustomer($existingCustId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastName, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $this->_customerAccountService->authenticate($customerAfter->getEmail(), 'password', true);
        $attributesBefore = \Magento\Service\DataObjectConverter::toFlatArray($customerBefore);
        $attributesAfter = \Magento\Service\DataObjectConverter::toFlatArray($customerAfter);
        // ignore 'updated_at'
        unset($attributesBefore['updated_at']);
        unset($attributesAfter['updated_at']);
        $inBeforeOnly = array_diff_assoc($attributesBefore, $attributesAfter);
        $inAfterOnly = array_diff_assoc($attributesAfter, $attributesBefore);
        $expectedInBefore = array('firstname', 'lastname', 'email');
        sort($expectedInBefore);
        $actualInBeforeOnly = array_keys($inBeforeOnly);
        sort($actualInBeforeOnly);
        $this->assertEquals($expectedInBefore, $actualInBeforeOnly);
        $expectedInAfter = array('firstname', 'lastname', 'email', 'created_in');
        sort($expectedInAfter);
        $actualInAfterOnly = array_keys($inAfterOnly);
        sort($actualInAfterOnly);
        $this->assertEquals($expectedInAfter, $actualInAfterOnly);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSaveCustomerPasswordCannotSetThroughAttributeSetting()
    {
        $existingCustId = 1;

        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';

        $customerBefore = $this->_customerAccountService->getCustomer($existingCustId);
        $customerData = array_merge(
            $customerBefore->__toArray(),
            array(
                'id' => 1,
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'created_in' => 'Admin',
                'password' => 'aPassword'
            )
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $modifiedCustomer = $this->_customerBuilder->create();

        $returnedCustomerId = $this->_customerAccountService->saveCustomer($modifiedCustomer);
        $this->assertEquals($existingCustId, $returnedCustomerId);
        $customerAfter = $this->_customerAccountService->getCustomer($existingCustId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastName, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $this->_customerAccountService->authenticate($customerAfter->getEmail(), 'password', true);
        $attributesBefore = \Magento\Service\DataObjectConverter::toFlatArray($customerBefore);
        $attributesAfter = \Magento\Service\DataObjectConverter::toFlatArray($customerAfter);
        // ignore 'updated_at'
        unset($attributesBefore['updated_at']);
        unset($attributesAfter['updated_at']);
        $inBeforeOnly = array_diff_assoc($attributesBefore, $attributesAfter);
        $inAfterOnly = array_diff_assoc($attributesAfter, $attributesBefore);
        $expectedInBefore = array('firstname', 'lastname', 'email');
        sort($expectedInBefore);
        $actualInBeforeOnly = array_keys($inBeforeOnly);
        sort($actualInBeforeOnly);
        $this->assertEquals($expectedInBefore, $actualInBeforeOnly);
        $expectedInAfter = array('firstname', 'lastname', 'email', 'created_in');
        sort($expectedInAfter);
        $actualInAfterOnly = array_keys($inAfterOnly);
        sort($actualInAfterOnly);
        $this->assertEquals($expectedInAfter, $actualInAfterOnly);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveCustomerException()
    {
        $customerData = array('id' => 1, 'password' => 'aPassword');
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();

        try {
            $this->_customerAccountService->saveCustomer($customerEntity);
            $this->fail('Expected exception not thrown');
        } catch (InputException $ie) {
            $expectedParams = array(
                array('fieldName' => 'firstname', 'value' => '', 'code' => InputException::REQUIRED_FIELD),
                array('fieldName' => 'lastname', 'value' => '', 'code' => InputException::REQUIRED_FIELD),
                array('fieldName' => 'email', 'value' => '', 'code' => InputException::INVALID_FIELD_VALUE)
            );
            $this->assertEquals($expectedParams, $ie->getParams());
        }
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSaveNonexistingCustomer()
    {
        $existingCustId = 1;
        $existingCustomer = $this->_customerAccountService->getCustomer($existingCustId);

        $newCustId = 2;
        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastName = 'Lastsave';
        $customerData = array_merge(
            $existingCustomer->__toArray(),
            array(
                'id' => $newCustId,
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'created_in' => 'Admin'
            )
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();

        $customerId = $this->_customerAccountService->saveCustomer($customerEntity, 'aPassword');
        $this->assertEquals($newCustId, $customerId);
        $customerAfter = $this->_customerAccountService->getCustomer($customerId);
        $this->assertEquals($email, $customerAfter->getEmail());
        $this->assertEquals($firstName, $customerAfter->getFirstname());
        $this->assertEquals($lastName, $customerAfter->getLastname());
        $this->assertEquals('Admin', $customerAfter->getCreatedIn());
        $this->_customerAccountService->authenticate($customerAfter->getEmail(), 'aPassword', true);
        $attributesBefore = \Magento\Service\DataObjectConverter::toFlatArray($existingCustomer);
        $attributesAfter = \Magento\Service\DataObjectConverter::toFlatArray($customerAfter);
        // ignore 'updated_at'
        unset($attributesBefore['updated_at']);
        unset($attributesAfter['updated_at']);
        $inBeforeOnly = array_diff_assoc($attributesBefore, $attributesAfter);
        $inAfterOnly = array_diff_assoc($attributesAfter, $attributesBefore);
        $expectedInBefore = array('email', 'firstname', 'id', 'lastname');
        sort($expectedInBefore);
        $actualInBeforeOnly = array_keys($inBeforeOnly);
        sort($actualInBeforeOnly);
        $this->assertEquals($expectedInBefore, $actualInBeforeOnly);
        $expectedInAfter = array('created_in', 'email', 'firstname', 'id', 'lastname');
        sort($expectedInAfter);
        $actualInAfterOnly = array_keys($inAfterOnly);
        sort($actualInAfterOnly);
        $this->assertEquals($expectedInAfter, $actualInAfterOnly);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveCustomerInServiceVsInModel()
    {
        $email = 'email@example.com';
        $email2 = 'email2@example.com';
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;
        $password = 'aPassword';

        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\CustomerFactory')->create();
        $customerModel->setEmail(
            $email
        )->setFirstname(
            $firstname
        )->setLastname(
            $lastname
        )->setGroupId(
            $groupId
        )->setPassword(
            $password
        );
        $customerModel->save();
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $savedModel = $this->_objectManager->create(
            'Magento\Customer\Model\CustomerFactory'
        )->create()->load(
            $customerModel->getId()
        );
        $dataInModel = $savedModel->getData();

        $this->_customerBuilder->setEmail(
            $email2
        )->setFirstname(
            $firstname
        )->setLastname(
            $lastname
        )->setGroupId(
            $groupId
        );
        $newCustomerEntity = $this->_customerBuilder->create();
        $customerId = $this->_customerAccountService->saveCustomer($newCustomerEntity, $password);
        $this->assertNotNull($customerId);
        $savedCustomer = $this->_customerAccountService->getCustomer($customerId);
        $dataInService = \Magento\Service\DataObjectConverter::toFlatArray($savedCustomer);
        $expectedDifferences = array(
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
        );
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
    public function testSaveNewCustomer()
    {
        $email = 'email@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;

        $this->_customerBuilder->setStoreId(
            $storeId
        )->setEmail(
            $email
        )->setFirstname(
            $firstname
        )->setLastname(
            $lastname
        )->setGroupId(
            $groupId
        );
        $newCustomerEntity = $this->_customerBuilder->create();
        $customerId = $this->_customerAccountService->saveCustomer($newCustomerEntity, 'aPassword');
        $this->assertNotNull($customerId);
        $savedCustomer = $this->_customerAccountService->getCustomer($customerId);
        $this->assertEquals($email, $savedCustomer->getEmail());
        $this->assertEquals($storeId, $savedCustomer->getStoreId());
        $this->assertEquals($firstname, $savedCustomer->getFirstname());
        $this->assertEquals($lastname, $savedCustomer->getLastname());
        $this->assertEquals($groupId, $savedCustomer->getGroupId());
        $this->assertTrue(!$savedCustomer->getSuffix());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSaveNewCustomerFromClone()
    {
        $email = 'savecustomer@example.com';
        $firstName = 'Firstsave';
        $lastname = 'Lastsave';

        $existingCustId = 1;
        $existingCustomer = $this->_customerAccountService->getCustomer($existingCustId);
        $customerData = array_merge(
            $existingCustomer->__toArray(),
            array('email' => $email, 'firstname' => $firstName, 'lastname' => $lastname, 'created_in' => 'Admin')
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();

        $customerId = $this->_customerAccountService->saveCustomer($customerEntity, 'aPassword');
        $this->assertNotEmpty($customerId);
        $customer = $this->_customerAccountService->getCustomer($customerId);
        $this->assertEquals($email, $customer->getEmail());
        $this->assertEquals($firstName, $customer->getFirstname());
        $this->assertEquals($lastname, $customer->getLastname());
        $this->assertEquals('Admin', $customer->getCreatedIn());
        $this->_customerAccountService->authenticate($customer->getEmail(), 'aPassword', true);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveCustomerNewThenUpdateFirstName()
    {
        $email = 'first_last@example.com';
        $storeId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;

        $this->_customerBuilder->setStoreId(
            $storeId
        )->setEmail(
            $email
        )->setFirstname(
            $firstname
        )->setLastname(
            $lastname
        )->setGroupId(
            $groupId
        );
        $newCustomerEntity = $this->_customerBuilder->create();
        $customerId = $this->_customerAccountService->saveCustomer($newCustomerEntity, 'aPassword');

        $this->_customerBuilder->populate($this->_customerAccountService->getCustomer($customerId));
        $this->_customerBuilder->setFirstname('Tested');
        $this->_customerAccountService->saveCustomer($this->_customerBuilder->create());

        $customer = $this->_customerAccountService->getCustomer($customerId);

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
            $expectedParams = array('customerId' => '1');
            $this->assertEquals($expectedParams, $nsee->getParams());
            $this->assertEquals('No such entity with customerId = 1', $nsee->getMessage());
        }
    }

    /**
     * @param mixed $custId
     * @dataProvider invalidCustomerIdsDataProvider
     * @expectedException \Magento\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId =
     */
    public function testGetCustomerInvalidIds($custId)
    {
        $this->_customerAccountService->getCustomer($custId);
    }

    public function invalidCustomerIdsDataProvider()
    {
        return array(array('ab'), array(' '), array(-1), array(0), array(' 1234'), array('-1'), array('0'));
    }

    /**
     * @param Data\Filter[] $filters
     * @param Datao\Filter[] $orGroup
     * @param array $expectedResult array of expected results indexed by ID
     *
     * @dataProvider searchCustomersDataProvider
     *
     * @magentoDataFixture Magento/Customer/_files/three_customers.php
     * @magentoDbIsolation enabled
     */
    public function testSearchCustomers($filters, $orGroup, $expectedResult)
    {
        $searchBuilder = new Data\SearchCriteriaBuilder();
        foreach ($filters as $filter) {
            $searchBuilder->addFilter($filter);
        }
        if (!is_null($orGroup)) {
            $searchBuilder->addOrGroup($orGroup);
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
        return array(
            'Customer with specific email' => array(
                array(
                    (new Data\FilterBuilder())->setField('email')->setValue('customer@search.example.com')->create()
                ),
                null,
                array(1 => array('email' => 'customer@search.example.com', 'firstname' => 'Firstname'))
            ),
            'Customer with specific first name' => array(
                array((new Data\FilterBuilder())->setField('firstname')->setValue('Firstname2')->create()),
                null,
                array(2 => array('email' => 'customer2@search.example.com', 'firstname' => 'Firstname2'))
            ),
            'Customers with either email' => array(
                array(),
                array(
                    (new Data\FilterBuilder())->setField('firstname')->setValue('Firstname')->create(),
                    (new Data\FilterBuilder())->setField('firstname')->setValue('Firstname2')->create()
                ),
                array(
                    1 => array('email' => 'customer@search.example.com', 'firstname' => 'Firstname'),
                    2 => array('email' => 'customer2@search.example.com', 'firstname' => 'Firstname2')
                )
            ),
            'Customers created since' => array(
                array(
                    (new Data\FilterBuilder())->setField(
                        'created_at'
                    )->setValue(
                        '2011-02-28 15:52:26'
                    )->setConditionType(
                        'gt'
                    )->create()
                ),
                array(),
                array(
                    1 => array('email' => 'customer@search.example.com', 'firstname' => 'Firstname'),
                    3 => array('email' => 'customer3@search.example.com', 'firstname' => 'Firstname3')
                )
            )
        );
    }

    /**
     * Test ordering
     *
     * @magentoDataFixture Magento/Customer/_files/three_customers.php
     * @magentoDbIsolation enabled
     */
    public function testSearchCustomersOrder()
    {
        $searchBuilder = new Data\SearchCriteriaBuilder();

        // Filter for 'firstname' like 'First'
        $filterBuilder = new Data\FilterBuilder();
        $firstnameFilter = $filterBuilder->setField(
            'firstname'
        )->setConditionType(
            'like'
        )->setValue(
            'First%'
        )->create();
        $searchBuilder->addFilter($firstnameFilter);

        // Search ascending order
        $searchBuilder->addSortOrder('lastname', Data\SearchCriteria::SORT_ASC);
        $searchResults = $this->_customerAccountService->searchCustomers($searchBuilder->create());
        $this->assertEquals(3, $searchResults->getTotalCount());
        $this->assertEquals('Lastname', $searchResults->getItems()[0]->getCustomer()->getLastname());
        $this->assertEquals('Lastname2', $searchResults->getItems()[1]->getCustomer()->getLastname());
        $this->assertEquals('Lastname3', $searchResults->getItems()[2]->getCustomer()->getLastname());

        // Search descending order
        $searchBuilder->addSortOrder('lastname', Data\SearchCriteria::SORT_DESC);
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
        $this->assertEquals('example@domain.com', $customer->getEmail());
        $this->assertEquals('test firstname', $customer->getFirstname());
        $this->assertEquals('test lastname', $customer->getLastname());
        $this->assertEquals(3, count($customerDetails->getAddresses()));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     * @magentoAppIsolation enabled
     * @expectedException \Magento\Exception\NoSuchEntityException
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
     * @expectedException \Magento\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId = 1
     */
    public function testDeleteCustomer()
    {
        // _files/customer.php sets the customer id to 1
        $this->_customerAccountService->deleteCustomer(1);
        $this->_customerAccountService->getCustomer(1);
    }

    /**
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @expectedException \Magento\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with customerId = 1
     */
    public function testDeleteCustomerWithAddress()
    {
        $this->markTestSkipped('MAGETWO-22014');
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
        $addressFactory = $this->_objectManager->create('Magento\Customer\Model\AddressFactory');
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
     * @expectedException \Magento\Model\Exception
     * @expectedExceptionMessage Customer website ID must be specified when using the website scope
     */
    public function testIsEmailAvailableNoWebsiteSpecified()
    {
        $this->_customerAccountService->isEmailAvailable('customer@example.com', null);
    }

    public function testIsEmailAvailableNonExistentEmail()
    {
        $this->assertTrue($this->_customerAccountService->isEmailAvailable('nonexistent@example.com', 1));
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
