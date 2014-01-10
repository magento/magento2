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

/**
 * \Magento\Customer\Service\V1\CustomerAccountService
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerAccountServiceTest extends \PHPUnit_Framework_TestCase
{

    const STREET = 'Parmer';
    const CITY = 'Albuquerque';
    const POSTCODE = '90014';
    const TELEPHONE = '7143556767';
    const REGION = 'Alabama';
    const REGION_ID = 1;
    const COUNTRY_ID = 'US';

    /** Sample values for testing */
    const ID = 1;
    const FIRSTNAME = 'Jane';
    const LASTNAME = 'Doe';
    const NAME = 'J';
    const EMAIL = 'janedoe@example.com';
    const EMAIL_CONFIRMATION_KEY = 'blj487lkjs4confirmation_key';
    const PASSWORD = 'password';
    const ATTRIBUTE_CODE = 'random_attr_code';
    const ATTRIBUTE_VALUE = 'random_attr_value';
    const WEBSITE_ID = 1;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\CustomerFactory
     */
    private $_customerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\AddressFactory
     */
    private $_addressFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Customer
     */
    private $_customerModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Attribute
     */
    private $_attributeModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\CustomerMetadataServiceInterface
     */
    private $_eavMetadataServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Event\ManagerInterface
     */
    private $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Model\StoreManagerInterface
     */
    private $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Math\Random
     */
    private $_mathRandomMock;

    /**
     * @var \Magento\Customer\Model\Converter
     */
    private $_converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Model\Store
     */
    private $_storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\Dto\AddressBuilder
     */
    private $_addressBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\Dto\CustomerBuilder
     */
    private $_customerBuilder;

    private $_validator;

    private $_customerServiceMock;

    private $_customerAddressServiceMock;

    public function setUp()
    {
        $this->_customerFactoryMock = $this->getMockBuilder('Magento\Customer\Model\CustomerFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();

        $this->_customerModelMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getId',
                    'getFirstname',
                    'getLastname',
                    'getName',
                    'getEmail',
                    'getAttributes',
                    'getConfirmation',
                    'setConfirmation',
                    'save',
                    'load',
                    '__wakeup',
                    'authenticate',
                    'getData',
                    'getDefaultBilling',
                    'getDefaultShipping',
                    'getDefaultShippingAddress',
                    'getDefaultBillingAddress',
                    'getStoreId',
                    'getAddressById',
                    'getAddresses',
                    'getAddressItemById',
                    'getParentId',
                    'isConfirmationRequired',
                    'addAddress',
                    'loadByEmail',
                    'sendNewAccountEmail',
                    'setFirstname',
                    'setLastname',
                    'setEmail',
                    'setPassword',
                    'setData',
                    'setWebsiteId',
                    'getAttributeSetId',
                    'setAttributeSetId',
                    'validate',
                    'getRpToken',
                    'setRpToken',
                    'setRpTokenCreatedAt',
                    'isResetPasswordLinkTokenExpired',
                    'changeResetPasswordLinkToken',
                    'sendPasswordResetConfirmationEmail',
                )
            )
            ->getMock();

        $this->_addressFactoryMock = $this->getMockBuilder('Magento\Customer\Model\AddressFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();

        $this->_eavMetadataServiceMock =
            $this->getMockBuilder('Magento\Customer\Service\Eav\AttributeMetadataServiceV1Interface')
                ->disableOriginalConstructor()
                ->getMock();

        $this->_eventManagerMock =
            $this->getMockBuilder('\Magento\Event\ManagerInterface')
                ->disableOriginalConstructor()
                ->getMock();

        $this->_attributeModelMock =
            $this->getMockBuilder('\Magento\Customer\Model\Attribute')
                ->disableOriginalConstructor()
                ->getMock();

        $this->_attributeModelMock
            ->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue(self::ATTRIBUTE_CODE));

        $this->_customerModelMock
            ->expects($this->any())
            ->method('getData')
            ->with($this->equalTo(self::ATTRIBUTE_CODE))
            ->will($this->returnValue(self::ATTRIBUTE_VALUE));

        $this->_customerModelMock
            ->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(TRUE));

        $this->_setupStoreMock();

        $this->_mathRandomMock = $this->getMockBuilder('\Magento\Math\Random')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_validator = $this->getMockBuilder('\Magento\Customer\Model\Metadata\Validator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_addressBuilder = new Dto\AddressBuilder(
            new Dto\RegionBuilder());

        $this->_customerBuilder = new Dto\CustomerBuilder();

        $customerBuilder = new Dto\CustomerBuilder();

        $this->_converter = new \Magento\Customer\Model\Converter($customerBuilder, $this->_customerFactoryMock);

        $this->_customerServiceMock = $this->getMockBuilder('\Magento\Customer\Service\V1\CustomerService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_customerAddressServiceMock =
            $this->getMockBuilder('\Magento\Customer\Service\V1\CustomerAddressService')
            ->disableOriginalConstructor()
            ->getMock();
    }


    public function testActivateAccount()
    {
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'getConfirmation' => self::EMAIL_CONFIRMATION_KEY,
                'getAttributes' => array(),
            )
        );

        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        // Assertions
        $this->_customerModelMock->expects($this->once())
            ->method('save');
        $this->_customerModelMock->expects($this->once())
            ->method('setConfirmation')
            ->with($this->isNull());

        $customerService = $this->_createService();

        $customer = $customerService->activateAccount(self::ID, self::EMAIL_CONFIRMATION_KEY);

        $this->assertEquals(self::ID, $customer->getCustomerId());
    }

    /**
     * @expectedException  \Magento\Customer\Service\Entity\V1\Exception
     */
    public function testActivateAccountAlreadyActive()
    {
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'getConfirmation' => null,
                'getAttributes' => array()
            )
        );

        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        // Assertions
        $this->_customerModelMock->expects($this->never())
            ->method('save');
        $this->_customerModelMock->expects($this->never())
            ->method('setConfirmation');

        $customerService = $this->_createService();

        $customerService->activateAccount(self::ID, self::EMAIL_CONFIRMATION_KEY);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionMessage No customer with customerId 1 exists.
     */
    public function testActivateAccountDoesntExist()
    {
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => 0,
            )
        );

        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        // Assertions
        $this->_customerModelMock->expects($this->never())
            ->method('save');
        $this->_customerModelMock->expects($this->never())
            ->method('setConfirmation');

        $customerService = $this->_createService();

        $customerService->activateAccount(self::ID, self::EMAIL_CONFIRMATION_KEY);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionMessage DB was down
     */
    public function testActivateAccountLoadError()
    {
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->throwException(new \Exception('DB was down')));

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => 0,
            )
        );

        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        // Assertions
        $this->_customerModelMock->expects($this->never())
            ->method('save');
        $this->_customerModelMock->expects($this->never())
            ->method('setConfirmation');

        $customerService = $this->_createService();

        $customerService->activateAccount(self::ID, self::EMAIL_CONFIRMATION_KEY);
    }

    /**
     * @expectedException \Magento\Core\Exception
     * @expectedExceptionMessage Wrong confirmation key
     */
    public function testActivateAccountBadKey()
    {
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'getConfirmation' => self::EMAIL_CONFIRMATION_KEY . 'BAD',
            )
        );

        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        // Assertions
        $this->_customerModelMock->expects($this->never())
            ->method('save');
        $this->_customerModelMock->expects($this->never())
            ->method('setConfirmation');

        $customerService = $this->_createService();

        $customerService->activateAccount(self::ID, self::EMAIL_CONFIRMATION_KEY);
    }

    /**
     * @expectedException \Magento\Core\Exception
     * @expectedExceptionMessage Failed to confirm customer account
     */
    public function testActivateAccountSaveFailed()
    {
        $this->_customerModelMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'getConfirmation' => self::EMAIL_CONFIRMATION_KEY,
            )
        );

        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        // Assertions/Mocking
        $this->_customerModelMock->expects($this->once())
            ->method('save')
            ->will($this->throwException(new \Exception('DB is down')));
        $this->_customerModelMock->expects($this->once())
            ->method('setConfirmation');

        $customerService = $this->_createService();

        $customerService->activateAccount(self::ID, self::EMAIL_CONFIRMATION_KEY);
    }

    public function testLoginAccount()
    {
        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'authenticate' => true,
                'load' => $this->_customerModelMock,
                'getAttributes' => array()
            )
        );

        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $customer = $customerService->authenticate(self::EMAIL, self::PASSWORD, self::WEBSITE_ID);

        $this->assertEquals(self::ID, $customer->getCustomerId());
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionMessage exception message
     */
    public function testLoginAccountWithException()
    {
        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'load' => $this->_customerModelMock,
            )
        );

        $this->_customerModelMock->expects($this->any())
            ->method('authenticate')
            ->will($this->throwException(new \Magento\Core\Exception('exception message') ));

        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $customerService->authenticate(self::EMAIL, self::PASSWORD, self::WEBSITE_ID);
    }

    public function testValidateResetPasswordLinkToken()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => false,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $customerService->validateResetPasswordLinkToken(self::ID, $resetToken);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_RESET_TOKEN_EXPIRED
     * @expectedExceptionMessage Your password reset link has expired.
     */
    public function testValidateResetPasswordLinkTokenExpired()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => true,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $customerService->validateResetPasswordLinkToken(self::ID, $resetToken);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_RESET_TOKEN_EXPIRED
     * @expectedExceptionMessage Your password reset link has expired.
     */
    public function testValidateResetPasswordLinkTokenInvalid()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $invalidToken = $resetToken . 'extra_stuff';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => false,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $customerService->validateResetPasswordLinkToken(self::ID, $invalidToken);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_INVALID_CUSTOMER_ID
     * @expectedExceptionMessage No customer with customerId 1 exists
     */
    public function testValidateResetPasswordLinkTokenWrongUser()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => 0,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => false,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $customerService->validateResetPasswordLinkToken(1, $resetToken);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_INVALID_RESET_TOKEN
     * @expectedExceptionMessage Invalid password reset token
     */
    public function testValidateResetPasswordLinkTokenNull()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => 0,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => false,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $customerService->validateResetPasswordLinkToken(null, null);
    }

    public function testSendPasswordResetLink()
    {
        $email = 'foo@example.com';
        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'setWebsiteId' => $this->_customerModelMock,
                'loadByEmail' => $this->_customerModelMock,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerModelMock->expects($this->once())
            ->method('sendPasswordResetConfirmationEmail');

        $customerService = $this->_createService();

        $customerService->sendPasswordResetLink($email, self::WEBSITE_ID);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_EMAIL_NOT_FOUND
     * @expectedExceptionMessage No customer found for the provided email and website ID
     */
    public function testSendPasswordResetLinkBadEmailOrWebsite()
    {
        $email = 'foo@example.com';
        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => 0,
                'setWebsiteId' => $this->_customerModelMock,
                'loadByEmail' => $this->_customerModelMock,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerModelMock->expects($this->never())
            ->method('sendPasswordResetConfirmationEmail');

        $customerService = $this->_createService();

        $customerService->sendPasswordResetLink($email, 0);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_UNKNOWN
     * @expectedExceptionMessage Invalid transactional email code: 0
     */
    public function testSendPasswordResetLinkSendException()
    {
        $email = 'foo@example.com';
        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'setWebsiteId' => $this->_customerModelMock,
                'loadByEmail' => $this->_customerModelMock,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerModelMock->expects($this->once())
            ->method('sendPasswordResetConfirmationEmail')
            ->will($this->throwException(new \Magento\Core\Exception(__('Invalid transactional email code: %1', 0))));

        $customerService = $this->_createService();

        $customerService->sendPasswordResetLink($email, self::WEBSITE_ID);
    }

    public function testResetPassword()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'password_secret';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => false,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerModelMock->expects($this->once())
            ->method('setRpToken')
            ->with(null)
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->with(null)
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->once())
            ->method('setPassword')
            ->with($password)
            ->will($this->returnSelf());

        $customerService = $this->_createService();

        $customerService->resetPassword(self::ID, $password, $resetToken);
    }

    public function testResetPasswordShortPassword()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = '';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => false,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerModelMock->expects($this->once())
            ->method('setRpToken')
            ->with(null)
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->with(null)
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->once())
            ->method('setPassword')
            ->with($password)
            ->will($this->returnSelf());

        $customerService = $this->_createService();

        $customerService->resetPassword(self::ID, $password, $resetToken);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_RESET_TOKEN_EXPIRED
     * @expectedExceptionMessage Your password reset link has expired.
     */
    public function testResetPasswordTokenExpired()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'password_secret';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => true,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerModelMock->expects($this->never())
            ->method('setRpToken');
        $this->_customerModelMock->expects($this->never())
            ->method('setRpTokenCreatedAt');
        $this->_customerModelMock->expects($this->never())
            ->method('setPassword');

        $customerService = $this->_createService();

        $customerService->resetPassword(self::ID, $password, $resetToken);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_RESET_TOKEN_EXPIRED
     * @expectedExceptionMessage Your password reset link has expired.
     */
    public function testResetPasswordTokenInvalid()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $invalidToken = $resetToken . 'invalid';
        $password = 'password_secret';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => false,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerModelMock->expects($this->never())
            ->method('setRpToken');
        $this->_customerModelMock->expects($this->never())
            ->method('setRpTokenCreatedAt');
        $this->_customerModelMock->expects($this->never())
            ->method('setPassword');

        $customerService = $this->_createService();

        $customerService->resetPassword(self::ID, $password, $invalidToken);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_INVALID_CUSTOMER_ID
     * @expectedExceptionMessage No customer with customerId 4200 exists
     */
    public function testResetPasswordTokenWrongUser()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'password_secret';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => 0,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => false,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerModelMock->expects($this->never())
            ->method('setRpToken');
        $this->_customerModelMock->expects($this->never())
            ->method('setRpTokenCreatedAt');
        $this->_customerModelMock->expects($this->never())
            ->method('setPassword');

        $customerService = $this->_createService();

        $customerService->resetPassword(4200, $password, $resetToken);
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_INVALID_RESET_TOKEN
     * @expectedExceptionMessage Invalid password reset token
     */
    public function testResetPasswordTokenInvalidUserId()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'password_secret';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => 0,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => false,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerModelMock->expects($this->never())
            ->method('setRpToken');
        $this->_customerModelMock->expects($this->never())
            ->method('setRpTokenCreatedAt');
        $this->_customerModelMock->expects($this->never())
            ->method('setPassword');

        $customerService = $this->_createService();

        $customerService->resetPassword(0, $password, $resetToken);
    }


    public function testSendConfirmation()
    {
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(55));
        $this->_customerModelMock->expects($this->once())
            ->method('setWebsiteId')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('isConfirmationRequired')
            ->will($this->returnValue(true));
        $this->_customerModelMock->expects($this->any())
            ->method('getConfirmation')
            ->will($this->returnValue('123abc'));

        $customerService = $this->_createService();
        $customerService->sendConfirmation('email');
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_EMAIL_NOT_FOUND
     */
    public function testSendConfirmationNoEmail()
    {
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(0));
        $this->_customerModelMock->expects($this->once())
            ->method('setWebsiteId')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();
        $customerService->sendConfirmation('email');
    }

    /**
     * @expectedException \Magento\Customer\Service\Entity\V1\Exception
     * @expectedExceptionCode \Magento\Customer\Service\Entity\V1\Exception::CODE_CONFIRMATION_NOT_NEEDED
     */
    public function testSendConfirmationNotNeeded()
    {
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(55));
        $this->_customerModelMock->expects($this->once())
            ->method('setWebsiteId')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();
        $customerService->sendConfirmation('email');
    }


    private function _setupStoreMock()
    {
        $this->_storeManagerMock =
            $this->getMockBuilder('\Magento\Core\Model\StoreManagerInterface')
                ->disableOriginalConstructor()
                ->getMock();

        $this->_storeMock = $this->getMockBuilder('\Magento\Core\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->_storeMock));
    }


    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function _mockReturnValue($mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())
                ->method($method)
                ->will($this->returnValue($value));
        }
    }

    /**
     * @return CustomerAccountService
     */
    private function _createService()
    {
        $customerService = new CustomerAccountService(
            $this->_customerFactoryMock,
            $this->_eventManagerMock,
            $this->_storeManagerMock,
            $this->_mathRandomMock,
            $this->_converter,
            $this->_validator,
            new Dto\Response\CreateCustomerAccountResponseBuilder(),
            $this->_customerServiceMock,
            $this->_customerAddressServiceMock
        );
        return $customerService;
    }
}
