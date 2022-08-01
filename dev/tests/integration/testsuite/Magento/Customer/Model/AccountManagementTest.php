<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Url as UrlBuilder;
use Magento\Store\Model\StoreManagerInterface;
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

    /** @var AddressRepositoryInterface needed to setup tests */
    private $addressRepository;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var AddressInterface[] */
    private $_expectedAddresses;

    /** @var \Magento\Customer\Api\Data\AddressInterfaceFactory */
    private $addressFactory;

    /** @var DataObjectProcessor */
    private $dataProcessor;

    /** @var  \Magento\Framework\Api\DataObjectHelper */
    protected $dataObjectHelper;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager
            ->create(\Magento\Customer\Api\AccountManagementInterface::class);
        $this->addressRepository =
            $this->objectManager->create(\Magento\Customer\Api\AddressRepositoryInterface::class);

        $this->addressFactory = $this->objectManager->create(\Magento\Customer\Api\Data\AddressInterfaceFactory::class);

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
    }

    /**
     * Clean up shared dependencies
     */
    protected function tearDown(): void
    {
        $customerRegistry = $this->objectManager->get(\Magento\Customer\Model\CustomerRegistry::class);
        $addressRegistry = $this->objectManager->get(\Magento\Customer\Model\AddressRegistry::class);
        //Cleanup customer from registry
        $customerRegistry->remove(1);
        $addressRegistry->remove(1);
        $addressRegistry->remove(2);
        $resourceModel = $this->objectManager->get(\Magento\Customer\Model\ResourceModel\Visitor::class);
        $resourceModel->getConnection()->delete($resourceModel->getMainTable());
        parent::tearDown();
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
     */
    public function testLoginWrongPassword()
    {
        $this->expectException(\Magento\Framework\Exception\InvalidEmailOrPasswordException::class);

        // Customer email and password are pulled from the fixture customer.php
        $this->accountManagement->authenticate('customer@example.com', 'wrongPassword');
    }

    /**
     * Test attempt to login with wrong user name
     *
     */
    public function testLoginWrongUsername()
    {
        $this->expectException(\Magento\Framework\Exception\InvalidEmailOrPasswordException::class);
        $this->expectExceptionMessage('Invalid login or password.');

        // Customer email and password are pulled from the fixture customer.php
        $this->accountManagement->authenticate('non_existing_user', '_Password123');
    }

    /**
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testChangePassword()
    {
        /** @var SessionManagerInterface $session */
        $session = $this->objectManager->get(SessionManagerInterface::class);
        $customerId = 1;
        $time = time();

        $session->start();

        // open new session
        $activeSessionId = uniqid("active-$time-");
        $this->startNewSession($activeSessionId);
        $activeVisitor = $this->createVisitorSession($activeSessionId, $customerId);
        $session->setVisitorData($activeVisitor->getData());

        // open new session
        $currentSessionId = uniqid("current-$time-");
        $this->startNewSession($currentSessionId);
        $currentVisitor = $this->createVisitorSession($currentSessionId, $customerId);
        $session->setVisitorData($currentVisitor->getData());

        $this->assertNull($this->getCustomerCutoff($customerId), 'Customer cutoff session should not be set.');
        // change password
        $this->accountManagement->changePassword('customer@example.com', 'password', 'new_Password123');
        $this->assertEquals(
            $currentSessionId,
            $session->getSessionId(),
            'Current session was renewed'
        );

        // open customer active session
        $this->startNewSession($activeSessionId);
        $this->assertNotNull($this->getCustomerCutoff($customerId), 'Customer cutoff session should be set.');
        // Make sure current visitor session is updated.
        $this->assertLessThanOrEqual(
            $this->getCustomerCutoff($customerId),
            $this->getVisitorCreatedAt($activeVisitor->getId())
        );
        $this->assertGreaterThan(
            $this->getCustomerCutoff($customerId),
            $this->getVisitorCreatedAt($currentVisitor->getId())
        );

        $this->accountManagement->authenticate('customer@example.com', 'new_Password123');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     */
    public function testChangePasswordWrongPassword()
    {
        $this->expectException(\Magento\Framework\Exception\InvalidEmailOrPasswordException::class);
        $this->expectExceptionMessage('The password doesn\'t match this account. Verify the password and try again.');

        $this->accountManagement->changePassword('customer@example.com', 'wrongPassword', 'new_Password123');
    }

    /**
     * Test change password on the wrong user
     *
     */
    public function testChangePasswordWrongUser()
    {
        $this->expectException(\Magento\Framework\Exception\InvalidEmailOrPasswordException::class);
        $this->expectExceptionMessage('Invalid login or password.');

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
     */
    public function testActivateCustomerConfirmationKeyWrongKey()
    {
        $this->expectException(\Magento\Framework\Exception\State\InputMismatchException::class);

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
     */
    public function testActivateCustomerAlreadyActive()
    {
        $this->expectException(\Magento\Framework\Exception\State\InvalidTransitionException::class);

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
     */
    public function testValidateResetPasswordLinkTokenExpired()
    {
        $this->expectException(\Magento\Framework\Exception\State\ExpiredException::class);

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
     */
    public function testResetPasswordTokenSecondTime()
    {
        $this->expectException(\Magento\Framework\Exception\State\InputMismatchException::class);

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
    public function testValidateResetPasswordLinkTokenEmpty()
    {
        try {
            $this->accountManagement->validateResetPasswordLinkToken(1, '');
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
    public function testValidateResetPasswordLinkTokenInvalidId()
    {
        $token = 'randomStr123';
        $this->setResetPasswordData($token, 'Y-m-d H:i:s');
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->accountManagement->validateResetPasswordLinkToken(0, $token);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testResetPassword()
    {
        /** @var SessionManagerInterface $session */
        $session = $this->objectManager->get(SessionManagerInterface::class);
        $time = time();
        $customerId = 1;

        $session->start();

        // open new session
        $activeSessionId = uniqid("active-$time-");
        $this->startNewSession($activeSessionId);
        $activeVisitor = $this->createVisitorSession($activeSessionId, $customerId);
        $session->setVisitorData($activeVisitor->getData());

        // open new session
        $currentSessionId = uniqid("current-$time-");
        $this->startNewSession($currentSessionId);
        $currentVisitor = $this->createVisitorSession($currentSessionId, $customerId);
        $session->setVisitorData($currentVisitor->getData());

        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'new_Password123';

        $this->assertNull($this->getCustomerCutoff($customerId), 'Customer cutoff session should not be set.');

        $this->setResetPasswordData($resetToken, 'Y-m-d H:i:s');
        $this->assertTrue($this->accountManagement->resetPassword('customer@example.com', $resetToken, $password));

        $this->assertEquals(
            $currentSessionId,
            $session->getSessionId(),
            'Current session was renewed'
        );

        // open customer active session
        $this->startNewSession($activeSessionId);
        $this->assertNotNull($this->getCustomerCutoff($customerId), 'Customer cutoff session should be set.');
        // Make sure current visitor session is updated.
        $this->assertLessThanOrEqual(
            $this->getCustomerCutoff($customerId),
            $this->getVisitorCreatedAt($activeVisitor->getId())
        );
        $this->assertGreaterThan(
            $this->getCustomerCutoff($customerId),
            $this->getVisitorCreatedAt($currentVisitor->getId())
        );
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
        $this->expectException(InputException::class);
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
     */
    public function testResendConfirmationNotNeeded()
    {
        $this->expectException(\Magento\Framework\Exception\State\InvalidTransitionException::class);

        $this->accountManagement->resendConfirmation('customer@example.com', 1);
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
     * Test reset password for customer on second website when shared account is enabled
     *
     * When customer from second website initiate reset password on first website
     * global scope should not be reinited to customer scope
     *
     * @magentoConfigFixture current_store customer/account_share/scope 0
     * @magentoDataFixture Magento/Customer/_files/customer_for_second_website.php
     */
    public function testInitiatePasswordResetForCustomerOnSecondWebsite()
    {
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $store = $storeManager->getStore();

        $this->accountManagement->initiatePasswordReset(
            'customer@example.com',
            AccountManagement::EMAIL_RESET,
            $storeManager->getWebsite()->getId()
        );

        $this->assertEquals($store->getId(), $storeManager->getStore()->getId());
        $urlBuilder = $this->objectManager->get(UrlBuilder::class);
        // to init scope if it has not inited yet
        $urlBuilder->setScope($urlBuilder->getData('scope'));
        $scope = $urlBuilder->getData('scope');
        $this->assertEquals($store->getId(), $scope->getId());
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

    /**
     * Returns the customers cutoff value
     *
     * @param int $customerId
     * @return mixed
     */
    private function getCustomerCutoff(
        int $customerId
    ) {
        $customerModel = $this->objectManager->get(\Magento\Customer\Model\ResourceModel\Customer::class);
        return $customerModel->findSessionCutOff($customerId);
    }

    /**
     * Returns the visitors created at value
     *
     * @param int $visitorId
     * @return mixed
     */
    private function getVisitorCreatedAt(
        int $visitorId
    ) {
        $visitorModel = $this->objectManager->get(\Magento\Customer\Model\ResourceModel\Visitor::class);
        return $visitorModel->fetchCreatedAt($visitorId);
    }

    /**
     * Starts a new session
     *
     * @param string $sessionId
     */
    private function startNewSession(string $sessionId): void
    {
        /** @var SessionManagerInterface $session */
        $session = $this->objectManager->get(SessionManagerInterface::class);
        // close session and cleanup session variable
        $session->writeClose();
        $session->clearStorage();
        // open new session
        $session->setSessionId($sessionId);
        $session->start();
    }

    /**
     * Creates a new visitor session
     *
     * @param string $sessionId
     * @param int|null $customerId
     * @return Visitor
     * @throws \Exception
     */
    private function createVisitorSession(string $sessionId, ?int $customerId = null): Visitor
    {
        /** @var Visitor $visitor */
        $visitor = Bootstrap::getObjectManager()->create(Visitor::class);
        $visitor->setCustomerId($customerId);
        $visitor->setSessionId($sessionId);
        $visitor->setLastVisitAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        $visitor->save();
        return $visitor;
    }
}
