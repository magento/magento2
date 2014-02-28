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
 */
class CustomerAccountServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerAccountServiceInterface */
    private $_service;

    /** @var CustomerServiceInterface needed to setup tests */
    private $_customerService;

    /** @var \Magento\ObjectManager */
    private $_objectManager;

    /** @var \Magento\Customer\Service\V1\Dto\Address[] */
    private $_expectedAddresses;

    /** @var \Magento\Customer\Service\V1\Dto\AddressBuilder */
    private $_addressBuilder;

    /** @var \Magento\Customer\Service\V1\Dto\CustomerBuilder */
    private $_customerBuilder;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_service = $this->_objectManager->create('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $this->_customerService = $this->_objectManager->create('Magento\Customer\Service\V1\CustomerServiceInterface');

        $this->_addressBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Dto\AddressBuilder');
        $this->_customerBuilder = $this->_objectManager->create('Magento\Customer\Service\V1\Dto\CustomerBuilder');

        $this->_addressBuilder->setId(1)
            ->setCountryId('US')
            ->setCustomerId(1)
            ->setDefaultBilling(true)
            ->setDefaultShipping(true)
            ->setPostcode('75477')
            ->setRegion(new V1\Dto\Region([
                'region_code' => 'AL',
                'region' => 'Alabama',
                'region_id' => 1
            ]))
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
            ->setRegion(new V1\Dto\Region([
                'region_code' => 'AL',
                'region' => 'Alabama',
                'region_id' => 1
            ]))
            ->setStreet(['Black str, 48'])
            ->setCity('CityX')
            ->setTelephone('3234676')
            ->setFirstname('John')
            ->setLastname('Smith');
        $address2 = $this->_addressBuilder->create();

        $this->_expectedAddresses = [$address, $address2];
    }


    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testLogin()
    {
        // Customer e-mail and password are pulled from the fixture customer.php
        $customer = $this->_service->authenticate('customer@example.com', 'password', true);

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
        $this->_service->authenticate('customer@example.com', 'wrongPassword', true);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @expectedException \Magento\Exception\AuthenticationException
     * @expectedExceptionMessage Invalid login or password
     */
    public function testLoginWrongUsername()
    {
        // Customer e-mail and password are pulled from the fixture customer.php
        $this->_service->authenticate('non_existing_user', 'password', true);
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

        $this->_service->activateAccount($customerModel->getId(), $customerModel->getConfirmation());

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
    public function testActivateAccountWrongKey()
    {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customerModel->load(1);
        $key = $customerModel->getConfirmation();

        try {
            $this->_service->activateAccount($customerModel->getId(), $key . $key);
            $this->fail('Expected exception was not thrown');
        } catch (InputException $ie) {
            $expectedParams = [
                [
                    'code' => InputException::INVALID_FIELD_VALUE,
                    'fieldName' => 'confirmation',
                    'value' => $key . $key,
                ]
            ];
            $this->assertEquals($expectedParams, $ie->getParams());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     */
    public function testActivateAccountWrongAccount()
    {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customerModel->load(1);
        $key = $customerModel->getConfirmation();
        try {
            $this->_service->activateAccount('1234' . $customerModel->getId(), $key);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $expectedParams = [
                'customerId' => '12341',
            ];
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
    public function testActivateAccountAlreadyActive()
    {
        /** @var \Magento\Customer\Model\Customer $customerModel */
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customerModel->load(1);
        $key = $customerModel->getConfirmation();
        $this->_service->activateAccount($customerModel->getId(), $key);

        $this->_service->activateAccount($customerModel->getId(), $key);
    }


    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testValidateResetPasswordLinkToken()
    {
        $this->_customerBuilder->populateWithArray(array_merge($this->_customerService->getCustomer(1)->__toArray(), [
            'rp_token' => 'token',
            'rp_token_created_at' => date('Y-m-d')
        ]));
        $this->_customerService->saveCustomer($this->_customerBuilder->create());

        $this->_service->validateResetPasswordLinkToken(1, 'token');
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

        $this->_customerBuilder->populateWithArray(array_merge($this->_customerService->getCustomer(1)->__toArray(), [
            'rp_token' => $resetToken,
            'rp_token_created_at' => '1970-01-01',
        ]));
        $customerData = $this->_customerBuilder->create();
        $this->_customerService->saveCustomer($customerData);

        $this->_service->validateResetPasswordLinkToken(1, $resetToken);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testValidateResetPasswordLinkTokenInvalid()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $invalidToken = 0;

        $this->_customerBuilder->populateWithArray(array_merge($this->_customerService->getCustomer(1)->__toArray(), [
            'rp_token' => $resetToken,
            'rp_token_created_at' => date('Y-m-d')
        ]));
        $this->_customerService->saveCustomer($this->_customerBuilder->create());

        try {
            $this->_service->validateResetPasswordLinkToken(1, $invalidToken);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $ie) {
            $expectedParams = [
                [
                    'value' => $invalidToken,
                    'fieldName' => 'resetPasswordLinkToken',
                    'code' => InputException::INVALID_FIELD_VALUE,
                ]
            ];
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
            $this->_service->validateResetPasswordLinkToken(4200, $resetToken);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $expectedParams = [
                'customerId' => '4200',
            ];
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
            $this->_service->validateResetPasswordLinkToken(1, null);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $ie) {
            $expectedParams = [
                [
                    'value' => null,
                    'fieldName' => 'resetPasswordLinkToken',
                    'code' => InputException::INVALID_FIELD_VALUE,
                ]
            ];
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

        $this->_service->sendPasswordResetLink($email, 1);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testSendPasswordResetLinkBadEmailOrWebsite()
    {
        $email = 'foo@example.com';

        try {
            $this->_service->sendPasswordResetLink($email, 0);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $expectedParams = [
                'email' => $email,
                'websiteId' => 0,
            ];
            $this->assertEquals($expectedParams, $nsee->getParams());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPassword()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'password_secret';

        $this->_customerBuilder->populateWithArray(array_merge($this->_customerService->getCustomer(1)->__toArray(), [
            'rp_token' => $resetToken,
            'rp_token_created_at' => date('Y-m-d')
        ]));
        $this->_customerService->saveCustomer($this->_customerBuilder->create());

        $this->_service->resetPassword(1, $password, $resetToken);
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
        $password = 'password_secret';

        $this->_customerBuilder->populateWithArray(array_merge($this->_customerService->getCustomer(1)->__toArray(), [
            'rp_token' => $resetToken,
            'rp_token_created_at' => '1970-01-01',
        ]));
        $this->_customerService->saveCustomer($this->_customerBuilder->create());

        $this->_service->resetPassword(1, $password, $resetToken);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testResetPasswordTokenInvalid()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $invalidToken = 0;
        $password = 'password_secret';

        $this->_customerBuilder->populateWithArray(array_merge($this->_customerService->getCustomer(1)->__toArray(), [
            'rp_token' => $resetToken,
            'rp_token_created_at' => date('Y-m-d')
        ]));
        $this->_customerService->saveCustomer($this->_customerBuilder->create());

        try {
            $this->_service->resetPassword(1, $password, $invalidToken);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $ie) {
            $expectedParams = [
                [
                    'value' => $invalidToken,
                    'fieldName' => 'resetPasswordLinkToken',
                    'code' => InputException::INVALID_FIELD_VALUE,
                ]
            ];
            $this->assertEquals($expectedParams, $ie->getParams());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordTokenWrongUser()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'password_secret';

        $this->_customerBuilder->populateWithArray(array_merge($this->_customerService->getCustomer(1)->__toArray(), [
            'rp_token' => $resetToken,
            'rp_token_created_at' => date('Y-m-d')
        ]));
        $this->_customerService->saveCustomer($this->_customerBuilder->create());
        try {
            $this->_service->resetPassword(4200, $password, $resetToken);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $expectedParams = [
                'customerId' => '4200',
            ];
            $this->assertEquals($expectedParams, $nsee->getParams());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPasswordTokenInvalidUserId()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'password_secret';

        $this->_customerBuilder->populateWithArray(array_merge($this->_customerService->getCustomer(1)->__toArray(), [
            'rp_token' => $resetToken,
            'rp_token_created_at' => date('Y-m-d')
        ]));
        $this->_customerService->saveCustomer($this->_customerBuilder->create());
        try {
            $this->_service->resetPassword(0, $password, $resetToken);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $ie) {
            $expectedParams = [
                [
                    'value' => 0,
                    'fieldName' => 'customerId',
                    'code' => InputException::INVALID_FIELD_VALUE,
                ]
            ];
            $this->assertEquals($expectedParams, $ie->getParams());
        }

    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Customer/_files/inactive_customer.php
     */
    public function testSendConfirmation()
    {
        $this->_service->sendConfirmation('customer@needAconfirmation.com');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testSendConfirmationNoEmail()
    {
        try {
            $this->_service->sendConfirmation('wrongemail@example.com');
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $expectedParams = [
                'email' => 'wrongemail@example.com',
                'websiteId' => '1',
            ];
            $this->assertEquals($expectedParams, $nsee->getParams());
        }
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::INVALID_STATE
     */
    public function testSendConfirmationNotNeeded()
    {
        $this->_service->sendConfirmation('customer@example.com');
    }
}
