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

use Magento\Customer\Model\Converter;
use Magento\Exception\InputException;
use Magento\Exception\NoSuchEntityException;
use Magento\Exception\StateException;
use Magento\Customer\Service\V1\Data\CustomerBuilder;

/**
 * \Magento\Customer\Service\V1\CustomerAccountService
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CustomerAccountServiceTest extends \PHPUnit_Framework_TestCase
{
    /** Sample values for testing */
    const ID = 1;

    const FIRSTNAME = 'Jane';

    const LASTNAME = 'Doe';

    const NAME = 'J';

    const EMAIL = 'janedoe@example.com';

    const EMAIL_CONFIRMATION_KEY = 'blj487lkjs4confirmation_key';

    const PASSWORD = 'password';

    const WEBSITE_ID = 1;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\CustomerFactory
     */
    private $_customerFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Customer
     */
    private $_customerModelMock;

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
     * @var Converter
     */
    private $_converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Model\Store
     */
    private $_storeMock;

    /**
     * @var \Magento\Customer\Model\Metadata\Validator
     */
    private $_validator;

    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder */
    private $_customerBuilder;

    /** @var \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder */
    private $_customerDetailsBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\CustomerAddressService
     */
    private $_customerAddressServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\CustomerMetadataService
     */
    private $_customerMetadataService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\UrlInterface
     */
    private $_urlMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject  | \Magento\Logger
     */
    private $_loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Helper\Data
     */
    private $_customerHelperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\ObjectManager */
    protected $_objectManagerMock;

    public function setUp()
    {
        $this->_customerFactoryMock = $this->getMockBuilder(
            'Magento\Customer\Model\CustomerFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();

        $this->_customerModelMock = $this->getMockBuilder(
            'Magento\Customer\Model\Customer'
        )->disableOriginalConstructor()->setMethods(
            array(
                'getCollection',
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
                'isDeleteable',
                'isReadonly',
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
                'sendPasswordResetNotificationEmail'
            )
        )->getMock();

        $this->_eventManagerMock = $this->getMockBuilder(
            '\Magento\Event\ManagerInterface'
        )->disableOriginalConstructor()->getMock();

        $this->_customerModelMock->expects($this->any())->method('validate')->will($this->returnValue(true));

        $this->_setupStoreMock();

        $this->_mathRandomMock = $this->getMockBuilder(
            '\Magento\Math\Random'
        )->disableOriginalConstructor()->getMock();

        $this->_validator = $this->getMockBuilder(
            '\Magento\Customer\Model\Metadata\Validator'
        )->disableOriginalConstructor()->getMock();

        $this->_customerMetadataService = $this->getMockForAbstractClass(
            'Magento\Customer\Service\V1\CustomerMetadataServiceInterface',
            array(),
            '',
            false
        );
        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getCustomCustomerAttributeMetadata'
        )->will(
            $this->returnValue(array())
        );

        $this->_customerBuilder = new Data\CustomerBuilder($this->_customerMetadataService);

        $customerBuilder = new CustomerBuilder($this->_customerMetadataService);
        $this->_customerDetailsBuilder = new Data\CustomerDetailsBuilder(
            $this->_customerBuilder,
            new Data\AddressBuilder(new Data\RegionBuilder(), $this->_customerMetadataService)
        );

        $this->_converter = new Converter($customerBuilder, $this->_customerFactoryMock);

        $this->_customerAddressServiceMock = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\CustomerAddressService'
        )->disableOriginalConstructor()->getMock();

        $this->_customerHelperMock = $this->getMockBuilder(
            'Magento\Customer\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            array('isCustomerInStore')
        )->getMock();
        $this->_customerHelperMock->expects(
            $this->any()
        )->method(
            'isCustomerInStore'
        )->will(
            $this->returnValue(false)
        );

        $this->_objectManagerMock = $this->getMock('Magento\ObjectManager', array(), array(), '', false);
        $this->_objectManagerMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            'Magento\Customer\Helper\Data'
        )->will(
            $this->returnValue($this->_customerHelperMock)
        );

        $this->_urlMock = $this->getMockBuilder('\Magento\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_loggerMock = $this->getMockBuilder('\Magento\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testActivateAccount()
    {
        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array('getId' => self::ID, 'getConfirmation' => self::EMAIL_CONFIRMATION_KEY, 'getAttributes' => array())
        );

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        // Assertions
        $this->_customerModelMock->expects($this->once())->method('save');
        $this->_customerModelMock->expects($this->once())->method('setConfirmation')->with($this->isNull());

        $customerService = $this->_createService();

        $customer = $customerService->activateCustomer(self::ID, self::EMAIL_CONFIRMATION_KEY);

        $this->assertEquals(self::ID, $customer->getId());
    }

    /**
     * @expectedException  \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::INVALID_STATE
     */
    public function testActivateAccountAlreadyActive()
    {
        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array('getId' => self::ID, 'getConfirmation' => null, 'getAttributes' => array())
        );

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        // Assertions
        $this->_customerModelMock->expects($this->never())->method('save');
        $this->_customerModelMock->expects($this->never())->method('setConfirmation');

        $customerService = $this->_createService();

        $customerService->activateCustomer(self::ID, self::EMAIL_CONFIRMATION_KEY);
    }

    public function testActivateAccountDoesntExist()
    {
        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());

        $this->_mockReturnValue($this->_customerModelMock, array('getId' => 0));

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        // Assertions
        $this->_customerModelMock->expects($this->never())->method('save');
        $this->_customerModelMock->expects($this->never())->method('setConfirmation');

        $customerService = $this->_createService();

        try {
            $customerService->activateCustomer(self::ID, self::EMAIL_CONFIRMATION_KEY);
            $this->fail('Expected exception not thrown.');
        } catch (\Magento\Exception\NoSuchEntityException $nsee) {
            $this->assertSame($nsee->getCode(), \Magento\Exception\NoSuchEntityException::NO_SUCH_ENTITY);
            $this->assertSame($nsee->getParams(), array('customerId' => self::ID));
        }
    }

    /**
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::INPUT_MISMATCH
     */
    public function testActivateAccountBadKey()
    {
        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array('getId' => self::ID, 'getConfirmation' => self::EMAIL_CONFIRMATION_KEY)
        );

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        // Assertions
        $this->_customerModelMock->expects($this->never())->method('save');
        $this->_customerModelMock->expects($this->never())->method('setConfirmation');

        $customerService = $this->_createService();

        $customerService->activateCustomer(self::ID, self::EMAIL_CONFIRMATION_KEY . 'BAD');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage DB is down
     */
    public function testActivateAccountSaveFailed()
    {
        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array('getId' => self::ID, 'getConfirmation' => self::EMAIL_CONFIRMATION_KEY)
        );

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        // Assertions/Mocking
        $this->_customerModelMock->expects(
            $this->once()
        )->method(
            'save'
        )->will(
            $this->throwException(new \Exception('DB is down'))
        );
        $this->_customerModelMock->expects($this->once())->method('setConfirmation');

        $customerService = $this->_createService();

        $customerService->activateCustomer(self::ID, self::EMAIL_CONFIRMATION_KEY);
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

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $customerService = $this->_createService();

        $customer = $customerService->authenticate(self::EMAIL, self::PASSWORD, self::WEBSITE_ID);

        $this->assertEquals(self::ID, $customer->getId());
    }

    /**
     * @expectedException \Magento\Exception\AuthenticationException
     * @expectedExceptionMessage exception message
     */
    public function testLoginAccountWithException()
    {
        $this->_mockReturnValue(
            $this->_customerModelMock,
            array('getId' => self::ID, 'load' => $this->_customerModelMock)
        );

        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'authenticate'
        )->will(
            $this->throwException(new \Magento\Model\Exception('exception message'))
        );

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

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
                'isResetPasswordLinkTokenExpired' => false
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $customerService = $this->_createService();

        $customerService->validateResetPasswordLinkToken(self::ID, $resetToken);
    }

    /**
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::EXPIRED
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
                'isResetPasswordLinkTokenExpired' => true
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $customerService = $this->_createService();

        $customerService->validateResetPasswordLinkToken(self::ID, $resetToken);
    }

    /**
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::INPUT_MISMATCH
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
                'isResetPasswordLinkTokenExpired' => false
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $customerService = $this->_createService();

        $customerService->validateResetPasswordLinkToken(self::ID, $invalidToken);
    }

    public function testValidateResetPasswordLinkTokenWrongUser()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => 0,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => false
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $customerService = $this->_createService();

        try {
            $customerService->validateResetPasswordLinkToken(1, $resetToken);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (\Magento\Exception\NoSuchEntityException $nsee) {
            $this->assertSame($nsee->getCode(), \Magento\Exception\NoSuchEntityException::NO_SUCH_ENTITY);
            $this->assertSame($nsee->getParams(), array('customerId' => self::ID));
        }
    }

    public function testValidateResetPasswordLinkTokenNull()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => 0,
                'load' => $this->_customerModelMock,
                'getRpToken' => $resetToken,
                'isResetPasswordLinkTokenExpired' => false
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $customerService = $this->_createService();

        try {
            $customerService->validateResetPasswordLinkToken(14, null);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $e) {
            $expectedParams = array(
                array(
                    'code' => InputException::INVALID_FIELD_VALUE,
                    'fieldName' => 'resetPasswordLinkToken',
                    'value' => null
                )
            );
            $this->assertEquals($expectedParams, $e->getParams());
        }
    }

    public function testSendPasswordResetLink()
    {
        $email = 'foo@example.com';
        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'setWebsiteId' => $this->_customerModelMock,
                'loadByEmail' => $this->_customerModelMock
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_customerModelMock->expects($this->once())->method('sendPasswordResetConfirmationEmail');

        $customerService = $this->_createService();

        $customerService->initiatePasswordReset(
            $email,
            self::WEBSITE_ID,
            CustomerAccountServiceInterface::EMAIL_RESET
        );
    }

    public function testSendPasswordResetLinkBadEmailOrWebsite()
    {
        $email = 'foo@example.com';
        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => 0,
                'setWebsiteId' => $this->_customerModelMock,
                'loadByEmail' => $this->_customerModelMock
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_customerModelMock->expects($this->never())->method('sendPasswordResetConfirmationEmail');

        $customerService = $this->_createService();

        try {
            $customerService->initiatePasswordReset($email, 0, CustomerAccountServiceInterface::EMAIL_RESET);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (\Magento\Exception\NoSuchEntityException $nsee) {
            $this->assertSame($nsee->getCode(), \Magento\Exception\NoSuchEntityException::NO_SUCH_ENTITY);
            $this->assertSame($nsee->getParams(), array('email' => $email, 'websiteId' => 0));
        }
    }

    /**
     * @expectedException \Magento\Model\Exception
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
                'loadByEmail' => $this->_customerModelMock
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_customerModelMock->expects(
            $this->once()
        )->method(
            'sendPasswordResetConfirmationEmail'
        )->will(
            $this->throwException(new \Magento\Model\Exception(__('Invalid transactional email code: %1', 0)))
        );

        $customerService = $this->_createService();

        $customerService->initiatePasswordReset(
            $email,
            self::WEBSITE_ID,
            CustomerAccountServiceInterface::EMAIL_RESET
        );
    }

    public function testSendPasswordResetLinkSendMailException()
    {
        $email = 'foo@example.com';
        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId'        => self::ID,
                'setWebsiteId' => $this->_customerModelMock,
                'loadByEmail'  => $this->_customerModelMock,
            )
        );
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $exception = new \Magento\Mail\Exception(__('The mail server is down'));

        $this->_customerModelMock->expects($this->once())
            ->method('sendPasswordResetConfirmationEmail')
            ->will($this->throwException($exception));

        $this->_loggerMock->expects($this->once())
            ->method('logException')
            ->with($exception);

        $customerService = $this->_createService();

        $customerService->initiatePasswordReset($email, self::WEBSITE_ID, CustomerAccountServiceInterface::EMAIL_RESET);
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
                'isResetPasswordLinkTokenExpired' => false
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_customerModelMock->expects($this->once())->method('setRpToken')->with(null)->will($this->returnSelf());
        $this->_customerModelMock->expects(
            $this->once()
        )->method(
            'setRpTokenCreatedAt'
        )->with(
            null
        )->will(
            $this->returnSelf()
        );
        $this->_customerModelMock->expects(
            $this->once()
        )->method(
            'setPassword'
        )->with(
            $password
        )->will(
            $this->returnSelf()
        );

        $customerService = $this->_createService();

        $customerService->resetPassword(self::ID, $resetToken, $password);
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
                'isResetPasswordLinkTokenExpired' => false
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_customerModelMock->expects($this->once())->method('setRpToken')->with(null)->will($this->returnSelf());
        $this->_customerModelMock->expects(
            $this->once()
        )->method(
            'setRpTokenCreatedAt'
        )->with(
            null
        )->will(
            $this->returnSelf()
        );
        $this->_customerModelMock->expects(
            $this->once()
        )->method(
            'setPassword'
        )->with(
            $password
        )->will(
            $this->returnSelf()
        );

        $customerService = $this->_createService();

        $customerService->resetPassword(self::ID, $resetToken, $password);
    }

    /**
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::EXPIRED
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
                'isResetPasswordLinkTokenExpired' => true
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_customerModelMock->expects($this->never())->method('setRpToken');
        $this->_customerModelMock->expects($this->never())->method('setRpTokenCreatedAt');
        $this->_customerModelMock->expects($this->never())->method('setPassword');

        $customerService = $this->_createService();

        $customerService->resetPassword(self::ID, $resetToken, $password);
    }

    /**
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::INPUT_MISMATCH
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
                'isResetPasswordLinkTokenExpired' => false
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_customerModelMock->expects($this->never())->method('setRpToken');
        $this->_customerModelMock->expects($this->never())->method('setRpTokenCreatedAt');
        $this->_customerModelMock->expects($this->never())->method('setPassword');

        $customerService = $this->_createService();

        $customerService->resetPassword(self::ID, $invalidToken, $password);
    }

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
                'isResetPasswordLinkTokenExpired' => false
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_customerModelMock->expects($this->never())->method('setRpToken');
        $this->_customerModelMock->expects($this->never())->method('setRpTokenCreatedAt');
        $this->_customerModelMock->expects($this->never())->method('setPassword');

        $customerService = $this->_createService();

        try {
            $customerService->resetPassword(4200, $resetToken, $password);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (\Magento\Exception\NoSuchEntityException $nsee) {
            $this->assertSame($nsee->getCode(), \Magento\Exception\NoSuchEntityException::NO_SUCH_ENTITY);
            $this->assertSame($nsee->getParams(), array('customerId' => 4200));
        }
    }

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
                'isResetPasswordLinkTokenExpired' => false
            )
        );
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_customerModelMock->expects($this->never())->method('setRpToken');
        $this->_customerModelMock->expects($this->never())->method('setRpTokenCreatedAt');
        $this->_customerModelMock->expects($this->never())->method('setPassword');

        $customerService = $this->_createService();

        try {
            $customerService->resetPassword(0, $resetToken, $password);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $e) {
            $expectedParams = array(
                array('code' => InputException::INVALID_FIELD_VALUE, 'fieldName' => 'customerId', 'value' => 0)
            );
            $this->assertEquals($expectedParams, $e->getParams());
        }
    }

    public function testResendConfirmation()
    {
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );
        $this->_customerModelMock->expects($this->any())->method('getId')->will($this->returnValue(55));
        $this->_customerModelMock->expects($this->once())->method('setWebsiteId')->will($this->returnSelf());
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'isConfirmationRequired'
        )->will(
            $this->returnValue(true)
        );
        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'getConfirmation'
        )->will(
            $this->returnValue('123abc')
        );

        $customerService = $this->_createService();
        $customerService->resendConfirmation('email', 1);
    }

    public function testResendConfirmationNoEmail()
    {
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );
        $this->_customerModelMock->expects($this->once())->method('getId')->will($this->returnValue(0));
        $this->_customerModelMock->expects($this->once())->method('setWebsiteId')->will($this->returnSelf());

        $customerService = $this->_createService();
        try {
            $customerService->resendConfirmation('email@no.customer', 1);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $nsee) {
            $expectedParams = array('email' => 'email@no.customer', 'websiteId' => 1);
            $this->assertEquals($expectedParams, $nsee->getParams());
        }
    }

    /**
     * @expectedException \Magento\Exception\StateException
     * @expectedExceptionCode \Magento\Exception\StateException::INVALID_STATE
     */
    public function testResendConfirmationNotNeeded()
    {
        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );
        $this->_customerModelMock->expects($this->once())->method('getId')->will($this->returnValue(55));
        $this->_customerModelMock->expects($this->once())->method('setWebsiteId')->with(2)->will($this->returnSelf());

        $customerService = $this->_createService();
        $customerService->resendConfirmation('email@test.com', 2);
    }

    public function testResendConfirmationWithMailException()
    {
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(55));
        $this->_customerModelMock->expects($this->once())
            ->method('setWebsiteId')
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->any())
            ->method('isConfirmationRequired')
            ->will($this->returnValue(true));
        $this->_customerModelMock->expects($this->any())
            ->method('getConfirmation')
            ->will($this->returnValue('123abc'));

        $exception = new \Magento\Mail\Exception(__('The mail server is down'));

        $this->_customerModelMock->expects($this->once())
            ->method('sendNewAccountEmail')
            ->withAnyParameters()
            ->will($this->throwException($exception));

        $this->_loggerMock->expects($this->once())
            ->method('logException')
            ->with($exception);

        $customerService = $this->_createService();
        $customerService->resendConfirmation('email', 1);
        // If we call sendNewAccountEmail and no exception is returned, the test succeeds
    }

    /**
     * @dataProvider testGetConfirmationStatusDataProvider
     * @param string $expected The expected confirmation status.
     */
    public function testGetConfirmationStatus($expected)
    {
        $customerId = 1234;
        $this->_customerFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );
        $this->_customerModelMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $customerId
        )->will(
            $this->returnSelf()
        );
        $this->_customerModelMock->expects($this->once())->method('getId')->will($this->returnValue($customerId));
        if (CustomerAccountServiceInterface::ACCOUNT_CONFIRMED == $expected) {
            $this->_customerModelMock->expects(
                $this->once()
            )->method(
                'getConfirmation'
            )->will(
                $this->returnValue(false)
            );
        } else {
            $this->_customerModelMock->expects(
                $this->once()
            )->method(
                'getConfirmation'
            )->will(
                $this->returnValue(true)
            );
        }
        if (CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_REQUIRED == $expected) {
            $this->_customerModelMock->expects(
                $this->once()
            )->method(
                'isConfirmationRequired'
            )->will(
                $this->returnValue(true)
            );
        } elseif (CustomerAccountServiceInterface::ACCOUNT_CONFIRMED != $expected) {
            $this->_customerModelMock->expects(
                $this->once()
            )->method(
                'getConfirmation'
            )->will(
                $this->returnValue(false)
            );
        }

        $customerService = $this->_createService();
        $this->assertEquals($expected, $customerService->getConfirmationStatus($customerId));
    }

    public function testGetConfirmationStatusDataProvider()
    {
        return array(
            array(CustomerAccountServiceInterface::ACCOUNT_CONFIRMED),
            array(CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_REQUIRED),
            array(CustomerAccountServiceInterface::ACCOUNT_CONFIRMATION_NOT_REQUIRED)
        );
    }

    /**
     * @param bool $isBoolean If the customer is or is not readonly/deleteable
     *
     * @dataProvider isBooleanDataProvider
     */
    public function testCanModify($isBoolean)
    {
        $this->_mockReturnValue($this->_customerModelMock, array('getId' => self::ID));

        $this->_customerModelMock->expects($this->once())->method('load')->with(self::ID)->will($this->returnSelf());
        $this->_customerModelMock->expects($this->once())->method('isReadonly')->will($this->returnValue($isBoolean));

        $this->_customerFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $customerService = $this->_createService();
        $this->assertEquals(!$isBoolean, $customerService->canModify(self::ID));
    }

    /**
     * @param bool $isBoolean If the customer is or is not readonly/deleteable
     *
     * @dataProvider isBooleanDataProvider
     */
    public function testCanDelete($isBoolean)
    {
        $this->_mockReturnValue($this->_customerModelMock, array('getId' => self::ID));

        $this->_customerModelMock->expects($this->once())->method('load')->with(self::ID)->will($this->returnSelf());
        $this->_customerModelMock->expects(
            $this->once()
        )->method(
            'isDeleteable'
        )->will(
            $this->returnValue($isBoolean)
        );

        $this->_customerFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $customerService = $this->_createService();
        $this->assertEquals($isBoolean, $customerService->canDelete(self::ID));
    }

    /**
     * Data provider for checking isReadonly() and isDeleteable()
     *
     * @return array
     */
    public function isBooleanDataProvider()
    {
        return array('true' => array(true), 'false' => array(false));
    }

    public function testSaveCustomer()
    {
        $customerData = array(
            'customer_id' => self::ID,
            'email' => self::EMAIL,
            'firstname' => self::FIRSTNAME,
            'lastname' => self::LASTNAME,
            'create_in' => 'Admin',
            'password' => 'password'
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'load' => $this->_customerModelMock,
                'getEmail' => self::EMAIL,
                'getFirstname' => self::FIRSTNAME,
                'getLastname' => self::LASTNAME
            )
        );

        $mockAttribute = $this->getMockBuilder(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata'
        )->disableOriginalConstructor()->getMock();
        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getCustomerAttributeMetadata'
        )->will(
            $this->returnValue($mockAttribute)
        );

        // verify
        $this->_customerModelMock->expects($this->atLeastOnce())->method('setData');

        $customerService = $this->_createService();

        $this->assertEquals(self::ID, $customerService->saveCustomer($customerEntity));
    }

    public function testSaveNonexistingCustomer()
    {
        $customerData = array(
            'customer_id' => self::ID,
            'email' => self::EMAIL,
            'firstname' => self::FIRSTNAME,
            'lastname' => self::LASTNAME,
            'create_in' => 'Admin',
            'password' => 'password'
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();

        $this->_customerFactoryMock->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => '2',
                'getEmail' => self::EMAIL,
                'getFirstname' => self::FIRSTNAME,
                'getLastname' => self::LASTNAME
            )
        );

        $mockAttribute = $this->getMockBuilder(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata'
        )->disableOriginalConstructor()->getMock();
        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getCustomerAttributeMetadata'
        )->will(
            $this->returnValue($mockAttribute)
        );

        // verify
        $this->_customerModelMock->expects($this->atLeastOnce())->method('setData');

        $customerService = $this->_createService();

        $this->assertEquals(2, $customerService->saveCustomer($customerEntity));
    }

    public function testSaveNewCustomer()
    {
        $customerData = array(
            'email' => self::EMAIL,
            'firstname' => self::FIRSTNAME,
            'lastname' => self::LASTNAME,
            'create_in' => 'Admin',
            'password' => 'password'
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'getEmail' => self::EMAIL,
                'getFirstname' => self::FIRSTNAME,
                'getLastname' => self::LASTNAME
            )
        );

        $mockAttribute = $this->getMockBuilder(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata'
        )->disableOriginalConstructor()->getMock();
        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getCustomerAttributeMetadata'
        )->will(
            $this->returnValue($mockAttribute)
        );

        // verify
        $this->_customerModelMock->expects($this->atLeastOnce())->method('setData');

        $customerService = $this->_createService();

        $this->assertEquals(self::ID, $customerService->saveCustomer($customerEntity));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage exception message
     */
    public function testSaveCustomerWithException()
    {
        $customerData = array(
            'email' => self::EMAIL,
            'firstname' => self::FIRSTNAME,
            'lastname' => self::LASTNAME,
            'create_in' => 'Admin',
            'password' => 'password'
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'getEmail' => self::EMAIL,
                'getFirstname' => self::FIRSTNAME,
                'getLastname' => self::LASTNAME
            )
        );

        $mockAttribute = $this->getMockBuilder(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata'
        )->disableOriginalConstructor()->getMock();
        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getCustomerAttributeMetadata'
        )->will(
            $this->returnValue($mockAttribute)
        );

        $this->_customerModelMock->expects(
            $this->once()
        )->method(
            'save'
        )->will(
            $this->throwException(new \Exception('exception message'))
        );

        // verify
        $customerService = $this->_createService();

        $customerService->saveCustomer($customerEntity);
    }

    public function testSaveCustomerWithInputException()
    {
        $customerData = array(
            'email' => self::EMAIL,
            'firstname' => self::FIRSTNAME,
            'lastname' => self::LASTNAME,
            'create_in' => 'Admin',
            'password' => 'password'
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_mockReturnValue($this->_customerModelMock, array('getId' => self::ID, 'getEmail' => 'missingAtSign'));

        $mockAttribute = $this->getMockBuilder(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata'
        )->disableOriginalConstructor()->getMock();
        $mockAttribute->expects($this->atLeastOnce())->method('isRequired')->will($this->returnValue(true));
        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getCustomerAttributeMetadata'
        )->will(
            $this->returnValue($mockAttribute)
        );

        // verify
        $customerService = $this->_createService();

        try {
            $customerService->saveCustomer($customerEntity);
        } catch (InputException $inputException) {
            $this->assertContains(
                array('fieldName' => 'firstname', 'code' => InputException::REQUIRED_FIELD, 'value' => null),
                $inputException->getParams()
            );
            $this->assertContains(
                array('fieldName' => 'lastname', 'code' => InputException::REQUIRED_FIELD, 'value' => null),
                $inputException->getParams()
            );
            $this->assertContains(
                array(
                    'fieldName' => 'email',
                    'code' => InputException::INVALID_FIELD_VALUE,
                    'value' => 'missingAtSign'
                ),
                $inputException->getParams()
            );
            $this->assertContains(
                array('fieldName' => 'dob', 'code' => InputException::REQUIRED_FIELD, 'value' => null),
                $inputException->getParams()
            );
            $this->assertContains(
                array('fieldName' => 'taxvat', 'code' => InputException::REQUIRED_FIELD, 'value' => null),
                $inputException->getParams()
            );
            $this->assertContains(
                array('fieldName' => 'gender', 'code' => InputException::REQUIRED_FIELD, 'value' => null),
                $inputException->getParams()
            );
        }
    }

    public function testGetCustomer()
    {
        $attributeModelMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Attribute'
        )->disableOriginalConstructor()->getMock();

        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'load'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => self::ID,
                'getFirstname' => self::FIRSTNAME,
                'getLastname' => self::LASTNAME,
                'getName' => self::NAME,
                'getEmail' => self::EMAIL,
                'getAttributes' => array($attributeModelMock)
            )
        );

        $attributeModelMock->expects(
            $this->any()
        )->method(
            'getAttributeCode'
        )->will(
            $this->returnValue('attribute_code')
        );

        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'getData'
        )->with(
            $this->equalTo('attribute_code')
        )->will(
            $this->returnValue('ATTRIBUTE_VALUE')
        );

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $customerService = $this->_createService();

        $actualCustomer = $customerService->getCustomer(self::ID);
        $this->assertEquals(self::ID, $actualCustomer->getId(), 'customer id does not match');
        $this->assertEquals(self::FIRSTNAME, $actualCustomer->getFirstName());
        $this->assertEquals(self::LASTNAME, $actualCustomer->getLastName());
        $this->assertEquals(self::EMAIL, $actualCustomer->getEmail());
        $this->assertEquals(4, count(\Magento\Service\DataObjectConverter::toFlatArray($actualCustomer)));
    }

    public function testSearchCustomersEmpty()
    {
        $collectionMock = $this->getMockBuilder(
            'Magento\Customer\Model\Resource\Customer\Collection'
        )->disableOriginalConstructor()->setMethods(
            array('addNameToSelect', 'addFieldToFilter', 'getSize', 'load', 'joinAttribute')
        )->getMock();
        $collectionMock->expects($this->any())->method('joinAttribute')->will($this->returnSelf());

        $this->_mockReturnValue($collectionMock, array('getSize' => 0));
        $this->_customerFactoryMock->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_customerModelMock->expects($this->any())->method('load')->will($this->returnSelf());

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array('getId' => self::ID, 'getCollection' => $collectionMock)
        );

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );
        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getAllCustomerAttributeMetadata'
        )->will(
            $this->returnValue(array())
        );

        $customerService = $this->_createService();
        $searchBuilder = new Data\SearchCriteriaBuilder();
        $filterBuilder = new Data\FilterBuilder();
        $filter = $filterBuilder->setField('email')->setValue('customer@search.example.com')->create();
        $searchBuilder->addFilter($filter);

        $searchResults = $customerService->searchCustomers($searchBuilder->create());
        $this->assertEquals(0, $searchResults->getTotalCount());
    }

    public function testSearchCustomers()
    {
        $collectionMock = $this->getMockBuilder(
            '\Magento\Customer\Model\Resource\Customer\Collection'
        )->disableOriginalConstructor()->setMethods(
            array('addNameToSelect', 'addFieldToFilter', 'getSize', 'load', 'getItems', 'getIterator', 'joinAttribute')
        )->getMock();
        $collectionMock->expects($this->any())->method('joinAttribute')->will($this->returnSelf());

        $this->_mockReturnValue(
            $collectionMock,
            array(
                'getSize' => 1,
                '_getItems' => array($this->_customerModelMock),
                'getIterator' => new \ArrayIterator(array($this->_customerModelMock))
            )
        );

        $this->_customerFactoryMock->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'load' => $this->returnSelf(),
                'getId' => self::ID,
                'getEmail' => self::EMAIL,
                'getCollection' => $collectionMock,
                'getAttributes' => array()
            )
        );

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_customerAddressServiceMock->expects(
            $this->once()
        )->method(
            'getAddresses'
        )->will(
            $this->returnValue(array())
        );

        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getAllCustomerAttributeMetadata'
        )->will(
            $this->returnValue(array())
        );

        $customerService = $this->_createService();
        $searchBuilder = new Data\SearchCriteriaBuilder();
        $filterBuilder = new Data\FilterBuilder();
        $filter = $filterBuilder->setField('email')->setValue(self::EMAIL)->create();
        $searchBuilder->addFilter($filter);

        $searchResults = $customerService->searchCustomers($searchBuilder->create());
        $this->assertEquals(1, $searchResults->getTotalCount());
        $this->assertEquals(self::EMAIL, $searchResults->getItems()[0]->getCustomer()->getEmail());
    }

    public function testGetCustomerDetails()
    {
        $customerMock = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Customer'
        )->disableOriginalConstructor()->getMock();
        $addressMock = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Address'
        )->disableOriginalConstructor()->getMock();
        $this->_converter = $this->getMockBuilder(
            '\Magento\Customer\Model\Converter'
        )->disableOriginalConstructor()->getMock();
        $service = $this->_createService();
        $this->_converter->expects(
            $this->once()
        )->method(
            'getCustomerModel'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );
        $this->_converter->expects(
            $this->once()
        )->method(
            'createCustomerFromModel'
        )->will(
            $this->returnValue($customerMock)
        );
        $this->_customerAddressServiceMock->expects(
            $this->once()
        )->method(
            'getAddresses'
        )->will(
            $this->returnValue(array($addressMock))
        );
        $customerDetails = $service->getCustomerDetails(1);
        $this->assertEquals($customerMock, $customerDetails->getCustomer());
        $this->assertEquals(array($addressMock), $customerDetails->getAddresses());
    }

    /**
     * @expectedException \Magento\Exception\NoSuchEntityException
     */
    public function testGetCustomerDetailsWithException()
    {
        $customerMock = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Customer'
        )->disableOriginalConstructor()->getMock();
        $addressMock = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Address'
        )->disableOriginalConstructor()->getMock();
        $this->_converter = $this->getMockBuilder(
            '\Magento\Customer\Model\Converter'
        )->disableOriginalConstructor()->getMock();
        $service = $this->_createService();
        $this->_converter->expects(
            $this->once()
        )->method(
            'getCustomerModel'
        )->will(
            $this->throwException(new \Magento\Exception\NoSuchEntityException('testField', 'value'))
        );
        $this->_converter->expects(
            $this->any()
        )->method(
            'createCustomerFromModel'
        )->will(
            $this->returnValue($customerMock)
        );
        $this->_customerAddressServiceMock->expects(
            $this->any()
        )->method(
            'getAddresses'
        )->will(
            $this->returnValue(array($addressMock))
        );
        $service->getCustomerDetails(1);
    }

    public function testIsEmailAvailable()
    {
        $this->_converter = $this->getMockBuilder(
            '\Magento\Customer\Model\Converter'
        )->disableOriginalConstructor()->getMock();
        $service = $this->_createService();
        $this->_converter->expects(
            $this->once()
        )->method(
            'getCustomerModelByEmail'
        )->will(
            $this->throwException(new \Magento\Exception\NoSuchEntityException('testField', 'value'))
        );
        $this->assertTrue($service->isEmailAvailable('email', 1));
    }

    public function testIsEmailAvailableNegative()
    {
        $customerMock = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Customer'
        )->disableOriginalConstructor()->getMock();
        $this->_converter = $this->getMockBuilder(
            '\Magento\Customer\Model\Converter'
        )->disableOriginalConstructor()->getMock();
        $service = $this->_createService();
        $this->_converter->expects(
            $this->once()
        )->method(
            'getCustomerModelByEmail'
        )->will(
            $this->returnValue($customerMock)
        );
        $this->assertFalse($service->isEmailAvailable('email', 1));
    }

    public function testCreateAccountMailException()
    {
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        // This is to get the customer model through validation
        $this->_customerModelMock->expects($this->any())
            ->method('getFirstname')
            ->will($this->returnValue('John'));

        $this->_customerModelMock->expects($this->any())
            ->method('getLastname')
            ->will($this->returnValue('Doe'));

        $this->_customerModelMock->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue('somebody@example.com'));

        // This is to get the customer model through Converter::getCustomerModel
        $this->_customerModelMock->expects($this->once())
            ->method('load')
            ->will($this->returnSelf());

        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(true));

        $exception = new \Magento\Mail\Exception(__('The mail server is down'));

        $this->_customerModelMock->expects($this->once())
            ->method('sendNewAccountEmail')
            ->will($this->throwException($exception));

        $this->_loggerMock->expects($this->once())
            ->method('logException')
            ->with($exception);

        $this->_customerModelMock->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue([]));

        $mockCustomer = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCustomer->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(true));

        $mockCustomer->expects($this->once())
            ->method('__toArray')
            ->will($this->returnValue(['attributeSetId' => true]));

        /**
         * @var \Magento\Customer\Service\V1\Data\CustomerDetails | \PHPUnit_Framework_MockObject_MockObject
         */
        $mockCustomerDetail = $this->getMockBuilder('Magento\Customer\Service\V1\Data\CustomerDetails')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCustomerDetail->expects($this->once())
            ->method('getCustomer')
            ->will($this->returnValue($mockCustomer));

        $service = $this->_createService();
        $service->createAccount($mockCustomerDetail, 'abc123');
        // If we get no mail exception, the test in considered a success
    }

    private function _setupStoreMock()
    {
        $this->_storeManagerMock = $this->getMockBuilder(
            '\Magento\Core\Model\StoreManagerInterface'
        )->disableOriginalConstructor()->getMock();

        $this->_storeMock = $this->getMockBuilder(
            '\Magento\Core\Model\Store'
        )->disableOriginalConstructor()->getMock();

        $this->_storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_storeMock)
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function _mockReturnValue($mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())->method($method)->will($this->returnValue($value));
        }
    }

    /**
     * @return CustomerAccountService
     */
    private function _createService()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $customerService = new CustomerAccountService(
            $this->_customerFactoryMock,
            $this->_eventManagerMock,
            $this->_storeManagerMock,
            $this->_mathRandomMock,
            $this->_converter,
            $this->_validator,
            $objectManager->getObject('\Magento\Customer\Service\V1\Data\CustomerBuilder'),
            $this->_customerDetailsBuilder,
            new Data\SearchResultsBuilder(),
            $this->_customerAddressServiceMock,
            $this->_customerMetadataService,
            $this->_urlMock,
            $this->_loggerMock,
            $this->_objectManagerMock
        );
        return $customerService;
    }


}
