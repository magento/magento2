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
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Service\V1\Data\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Service\V1\Data\CustomerBuilder;
use Magento\Framework\Service\Data\AttributeValueBuilder;
use Magento\Framework\Service\V1\Data\FilterBuilder;
use Magento\Framework\Mail\Exception as MailException;
use Magento\Framework\Service\ExtensibleDataObjectConverter;

/**
 * Test for \Magento\Customer\Service\V1\CustomerAccountService
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
    const ID = '1';
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
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Event\ManagerInterface
     */
    private $_eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\StoreManagerInterface
     */
    private $_storeManagerMock;

    /**
     * @var Converter
     */
    private $_converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\Store
     */
    private $_storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Store\Model\Website
     */
    private $_websiteMock;

    /**
     * @var \Magento\Customer\Service\V1\Data\CustomerBuilder
     */
    private $_customerBuilder;

    /**
     * @var \Magento\Customer\Service\V1\Data\CustomerDetailsBuilder
     */
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
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\AddressMetadataService
     */
    private $_addressMetadataService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | CustomerRegistry
     */
    private $_customerRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject  | \Magento\Framework\Logger
     */
    private $_loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Helper\Data
     */
    private $_customerHelperMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Model\Config\Share */
    private $_configShareMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Encryption\EncryptorInterface  */
    private $_encryptorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\UrlInterface
     */
    private $_urlMock;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchBuilder;

    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $filterGroupBuilder = $this->_objectManager
            ->getObject('Magento\Framework\Service\V1\Data\Search\FilterGroupBuilder');
        /** @var SearchCriteriaBuilder $searchBuilder */
        $this->_searchBuilder = $this->_objectManager->getObject(
            'Magento\Framework\Service\V1\Data\SearchCriteriaBuilder',
            ['filterGroupBuilder' => $filterGroupBuilder]
        );

        $this->_customerFactoryMock = $this->getMockBuilder(
            'Magento\Customer\Model\CustomerFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();

        $this->_customerModelMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->setMethods(
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
                    'setPasswordHash',
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
                    'sendPasswordResetNotificationEmail',
                    'sendPasswordReminderEmail',
                    'delete'
                )
            )->getMock();

        $this->_eventManagerMock = $this->getMockBuilder(
            '\Magento\Framework\Event\ManagerInterface'
        )->disableOriginalConstructor()->getMock();
        $this->_customerModelMock->expects($this->any())->method('validate')->will($this->returnValue(true));

        $this->_setupStoreMock();

        $this->_validator = $this->getMockBuilder(
            '\Magento\Customer\Model\Metadata\Validator'
        )->disableOriginalConstructor()->getMock();

        $this->_customerMetadataService = $this->getMockForAbstractClass(
            'Magento\Customer\Service\V1\CustomerMetadataServiceInterface',
            [],
            '',
            false
        );
        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getCustomAttributesMetadata'
        )->will(
            $this->returnValue(array())
        );

        $this->_addressMetadataService = $this->getMockForAbstractClass(
            'Magento\Customer\Service\V1\AddressMetadataServiceInterface',
            [],
            '',
            false
        );

        $this->_addressMetadataService
            ->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->will($this->returnValue(array()));

        $this->_customerBuilder = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\CustomerBuilder',
            ['metadataService' => $this->_customerMetadataService]
        );

        $addressBuilder = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\AddressBuilder',
            ['metadataService' => $this->_addressMetadataService]
        );

        $this->_customerDetailsBuilder = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\CustomerDetailsBuilder',
            [
                'customerBuilder' => $this->_customerBuilder,
                'addressBuilder' => $addressBuilder
            ]
        );

        $this->_converter = new Converter(
            $this->_customerBuilder,
            $this->_customerFactoryMock,
            $this->_storeManagerMock
        );

        $this->_customerRegistry = $this->getMockBuilder('\Magento\Customer\Model\CustomerRegistry')
            ->setMethods(['retrieve', 'retrieveByEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_customerRegistry
            ->expects($this->any())
            ->method('retrieve')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerRegistry
            ->expects($this->any())
            ->method('retrieveByEmail')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerAddressServiceMock =
            $this->getMockBuilder('Magento\Customer\Service\V1\CustomerAddressService')
                ->disableOriginalConstructor()
                ->getMock();

        $this->_customerHelperMock =
            $this->getMockBuilder('Magento\Customer\Helper\Data')
                ->disableOriginalConstructor()
                ->setMethods(['isCustomerInStore'])
                ->getMock();
        $this->_customerHelperMock->expects($this->any())
            ->method('isCustomerInStore')
            ->will($this->returnValue(false));

        $this->_urlMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_loggerMock = $this->getMockBuilder('Magento\Framework\Logger')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_encryptorMock = $this->getMockBuilder('Magento\Framework\Encryption\EncryptorInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_configShareMock = $this->getMockBuilder('Magento\Customer\Model\Config\Share')
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
     * @expectedException  \Magento\Framework\Exception\State\InvalidTransitionException
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
        $this->_customerRegistry
            ->expects($this->any())
            ->method('retrieve')
            ->will($this->throwException(
                new NoSuchEntityException(
                    NoSuchEntityException::MESSAGE_SINGLE_FIELD,
                    [
                        'fieldName' => 'customerId',
                        'fieldValue' => 1,
                    ]
                )
            ));

        // Assertions
        $this->_customerModelMock->expects($this->never())->method('save');
        $this->_customerModelMock->expects($this->never())->method('setConfirmation');

        $customerService = $this->_createService();

        try {
            $customerService->activateCustomer(self::ID, self::EMAIL_CONFIRMATION_KEY);
            $this->fail('Expected exception not thrown.');
        } catch (NoSuchEntityException $nsee) {
            $this->assertSame('No such entity with customerId = 1', $nsee->getMessage());
        }
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InputMismatchException
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
     * @dataProvider authenticateException
     */
    public function testLoginAccountWithException($eName, $eCode)
    {
        $eMessage = 'exception message';
        $this->setExpectedException($eName, $eMessage);
        $this->_mockReturnValue(
            $this->_customerModelMock,
            array('getId' => self::ID, 'load' => $this->_customerModelMock)
        );

        $this->_customerModelMock->expects(
            $this->any()
        )->method(
            'authenticate'
        )->will(
            $this->throwException(new \Magento\Framework\Model\Exception($eMessage, $eCode))
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

    public function authenticateException()
    {
        return array(
            array(
                '\Magento\Framework\Exception\EmailNotConfirmedException',
                \Magento\Customer\Model\Customer::EXCEPTION_EMAIL_NOT_CONFIRMED
            ),
            array(
                '\Magento\Framework\Exception\InvalidEmailOrPasswordException',
                \Magento\Customer\Model\Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
            ),
            array('\Magento\Framework\Exception\AuthenticationException', 0),
        );
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
     * @expectedException \Magento\Framework\Exception\State\ExpiredException
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
     * @expectedException \Magento\Framework\Exception\State\InputMismatchException
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

        $this->_customerRegistry
            ->expects($this->any())
            ->method('retrieve')
            ->will($this->throwException(
                new NoSuchEntityException(
                    NoSuchEntityException::MESSAGE_SINGLE_FIELD,
                    [
                        'fieldName' => 'customerId',
                        'fieldValue' => 1,
                    ]
                )
            ));

        $customerService = $this->_createService();

        try {
            $customerService->validateResetPasswordLinkToken('1', $resetToken);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $nsee) {
            $this->assertSame('No such entity with customerId = 1', $nsee->getMessage());
        }
    }

    public function testValidateResetPasswordLinkTokenNull()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';

        $this->_mockReturnValue(
            $this->_customerModelMock,
            array(
                'getId' => '0',
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
            $customerService->validateResetPasswordLinkToken('14', null);
            $this->fail('Expected exception not thrown.');
        } catch (InputException $e) {
            $this->assertEquals(InputException::REQUIRED_FIELD, $e->getRawMessage());
            $this->assertEquals('resetPasswordLinkToken is a required field.', $e->getMessage());
            $this->assertEquals('resetPasswordLinkToken is a required field.', $e->getLogMessage());
        }
    }

    protected function prepareInitiatePasswordReset($method)
    {
        $storeId = 42;

        $this->_customerModelMock->expects($this->once())
            ->method($method);
        $this->_customerModelMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));
        $this->_urlMock->expects($this->once())
            ->method('setScope')->with($storeId)
            ->will($this->returnSelf());
    }

    public function testSendPasswordResetLink()
    {
        $email = 'foo@example.com';
        $this->prepareInitiatePasswordReset('sendPasswordResetConfirmationEmail');

        $customerService = $this->_createService();

        $customerService->initiatePasswordReset(
            $email,
            CustomerAccountServiceInterface::EMAIL_RESET,
            self::WEBSITE_ID
        );
    }

    public function testSendPasswordReminderLinkWithoutWebsite()
    {
        $email = 'foo@example.com';

        $this->prepareInitiatePasswordReset('sendPasswordReminderEmail');

        $customerService = $this->_createService();

        $customerService->initiatePasswordReset(
            $email,
            CustomerAccountServiceInterface::EMAIL_REMINDER
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Invalid value of "" provided for the email type field.
     */
    public function testInitiatePasswordResetWithException()
    {
        $email = 'foo@example.com';
        $storeId = 42;

        $this->_customerModelMock->expects($this->never())
            ->method('sendPasswordReminderEmail');
        $this->_customerModelMock->expects($this->never())
            ->method('sendPasswordResetConfirmationEmail');
        $this->_customerModelMock->expects($this->atLeastOnce())
            ->method('getStoreId')
            ->will($this->returnValue($storeId));
        $this->_urlMock->expects($this->once())
            ->method('setScope')->with($storeId)
            ->will($this->returnSelf());

        $this->_storeMock->expects($this->never())
            ->method('getWebSiteId');

        $this->_storeMock->expects($this->never())
            ->method('getWebSiteId');

        $customerService = $this->_createService();

        $customerService->initiatePasswordReset(
            $email,
            '',
            self::WEBSITE_ID
        );
    }

    public function testSendPasswordResetLinkBadEmailOrWebsite()
    {
        $email = 'foo@example.com';

        $this->_customerRegistry
            ->expects($this->any())
            ->method('retrieveByEmail')
            ->will($this->throwException(NoSuchEntityException::doubleField('email', $email, 'websiteId', 0)));

        $this->_customerModelMock->expects($this->never())->method('sendPasswordResetConfirmationEmail');

        $customerService = $this->_createService();

        try {
            $customerService->initiatePasswordReset($email, CustomerAccountServiceInterface::EMAIL_RESET, 0);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $nsee) {
            $this->assertSame("No such entity with email = foo@example.com, websiteId = 0", $nsee->getMessage());
        }
    }

    /**
     * @expectedException \Magento\Framework\Model\Exception
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
        $this->_customerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_customerModelMock));

        $this->_customerModelMock->expects($this->once())
            ->method('sendPasswordResetConfirmationEmail')
            ->will($this->throwException(
                new \Magento\Framework\Model\Exception(__('Invalid transactional email code: %1', 0))
            ));

        $customerService = $this->_createService();

        $customerService->initiatePasswordReset(
            $email,
            CustomerAccountServiceInterface::EMAIL_RESET,
            self::WEBSITE_ID
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

        $exception = new MailException(__('The mail server is down'));

        $this->_customerModelMock->expects($this->once())
            ->method('sendPasswordResetConfirmationEmail')
            ->will($this->throwException($exception));

        $this->_loggerMock->expects($this->once())
            ->method('logException')
            ->with($exception);

        $customerService = $this->_createService();

        $customerService->initiatePasswordReset($email, CustomerAccountServiceInterface::EMAIL_RESET, self::WEBSITE_ID);
    }

    public function testResetPassword()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = 'password_secret';
        $encryptedHash = 'password_encrypted_hash';

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

        $this->_customerModelMock->expects($this->once())
            ->method('setRpToken')
            ->with(null)
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->with(null)
            ->will($this->returnSelf());
        $this->_encryptorMock->expects($this->once())
            ->method('getHash')
            ->with($password, true)
            ->will($this->returnValue($encryptedHash));
        $this->_customerModelMock->expects($this->once())
            ->method('setPasswordHash')
            ->with($encryptedHash)
            ->will($this->returnSelf());

        $customerService = $this->_createService();

        $customerService->resetPassword(self::ID, $resetToken, $password);
    }

    public function testResetPasswordShortPassword()
    {
        $resetToken = 'lsdj579slkj5987slkj595lkj';
        $password = '12345';

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

        $this->_customerModelMock->expects($this->once())
            ->method('setRpToken')
            ->with(null)
            ->will($this->returnSelf());
        $this->_customerModelMock->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->with(null)
            ->will($this->returnSelf());

        $customerService = $this->_createService();

        $this->setExpectedException(
            'Magento\Framework\Exception\InputException',
            'The password must have at least 6 characters.'
        );
        $customerService->resetPassword(self::ID, $resetToken, $password);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\ExpiredException
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
     * @expectedException \Magento\Framework\Exception\State\InputMismatchException
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
        $invalidCustomerId = '4200';

        $this->_customerRegistry
            ->expects($this->any())
            ->method('retrieve')
            ->will($this->throwException(
                new NoSuchEntityException(
                    NoSuchEntityException::MESSAGE_SINGLE_FIELD,
                    [
                        'fieldName' => 'customerId',
                        'fieldValue' => $invalidCustomerId,
                    ]
                )
            )
        );

        $this->_customerModelMock->expects($this->never())->method('setRpToken');
        $this->_customerModelMock->expects($this->never())->method('setRpTokenCreatedAt');
        $this->_customerModelMock->expects($this->never())->method('setPassword');

        $customerService = $this->_createService();

        try {
            $customerService->resetPassword($invalidCustomerId, $resetToken, $password);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $nsee) {
            $this->assertSame('No such entity with customerId = 4200', $nsee->getMessage());
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
            $this->assertEquals(InputException::INVALID_FIELD_VALUE, $e->getRawMessage());
            $this->assertEquals('Invalid value of "0" provided for the customerId field.', $e->getMessage());
            $this->assertEquals('Invalid value of "0" provided for the customerId field.', $e->getLogMessage());
        }
    }

    public function testResendConfirmation()
    {
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
        $email = 'no.customer@example.com';
        $websiteId = self::WEBSITE_ID;
        $exception = new NoSuchEntityException(
            NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
            [
                'fieldName' => 'email',
                'fieldValue' => 'email@no.customer',
                'field2Name' => 'websiteId',
                'field2Value' => 1,
            ]
        );
        $this->_customerRegistry
            ->expects($this->any())
            ->method('retrieveByEmail')
            ->will($this->throwException($exception));

        $customerService = $this->_createService();
        try {
            $customerService->resendConfirmation($email, $websiteId);
            $this->fail("Expected NoSuchEntityException not caught");
        } catch (NoSuchEntityException $e) {
            $this->assertSame("No such entity with email = email@no.customer, websiteId = 1", $e->getMessage());
        }
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     */
    public function testResendConfirmationNotNeeded()
    {
        $customerService = $this->_createService();
        $customerService->resendConfirmation('email@test.com', 2);
    }

    public function testResendConfirmationWithMailException()
    {
        $this->_customerModelMock->expects($this->any())
            ->method('isConfirmationRequired')
            ->will($this->returnValue(true));
        $this->_customerModelMock->expects($this->any())
            ->method('getConfirmation')
            ->will($this->returnValue('123abc'));

        $exception = new MailException(__('The mail server is down'));

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
        $this->_customerModelMock->expects($this->once())->method('isReadonly')->will($this->returnValue($isBoolean));

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

        $this->_customerModelMock->expects(
            $this->once()
        )->method(
            'isDeleteable'
        )->will(
            $this->returnValue($isBoolean)
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

    public function testCreateCustomer()
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
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($customerEntity)->create();

        $this->_customerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_customerModelMock)
        );

        $this->_converter = $this->getMock('Magento\Customer\Model\Converter', [], [], '', false);
        $this->_converter
            ->expects($this->once())
            ->method('createCustomerFromModel')
            ->will($this->returnValue($customerEntity));
        $this->_converter
            ->expects($this->any())
            ->method('getCustomerModel')
            ->will($this->returnValue($this->_customerModelMock));

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
            'getAttributeMetadata'
        )->will(
            $this->returnValue($mockAttribute)
        );

        // verify
        $this->_converter
            ->expects($this->once())
            ->method('createCustomerModel')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $this->assertSame($customerEntity, $customerService->createCustomer($customerDetails));
    }

    public function testCreateNewCustomer()
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
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($customerEntity)->create();

        $this->_converter = $this->getMock('Magento\Customer\Model\Converter', [], [], '', false);
        $this->_converter
            ->expects($this->once())
            ->method('createCustomerFromModel')
            ->will($this->returnValue($customerEntity));
        $this->_converter
            ->expects($this->any())
            ->method('getCustomerModel')
            ->will($this->returnValue($this->_customerModelMock));

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
            'getAttributeMetadata'
        )->will(
            $this->returnValue($mockAttribute)
        );

        // verify
        $this->_converter
            ->expects($this->once())
            ->method('createCustomerModel')
            ->will($this->returnValue($this->_customerModelMock));

        $customerService = $this->_createService();

        $this->assertSame($customerEntity, $customerService->createCustomer($customerDetails));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage exception message
     */
    public function testCreateCustomerWithException()
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
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($customerEntity)->create();

        $this->_converter = $this->getMock('Magento\Customer\Model\Converter', [], [], '', false);
        $this->_converter
            ->expects($this->once())
            ->method('createCustomerModel')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_converter
            ->expects($this->any())
            ->method('getCustomerModel')
            ->will($this->returnValue($this->_customerModelMock));

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
            'getAttributeMetadata'
        )->will(
            $this->returnValue($mockAttribute)
        );

        $this->_converter
            ->expects($this->once())
            ->method('createCustomerFromModel')
            ->will($this->throwException(new \Exception('exception message')));

        $customerService = $this->_createService();
        $customerService->createCustomer($customerDetails);
    }

    public function testCreateCustomerWithInputException()
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
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($customerEntity)->create();

        $this->_converter = $this->getMock('Magento\Customer\Model\Converter', [], [], '', false);
        $this->_converter
            ->expects($this->once())
            ->method('createCustomerModel')
            ->will($this->returnValue($this->_customerModelMock));

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
                'getAttributeMetadata'
            )->will(
                $this->returnValue($mockAttribute)
            );

        // verify
        $customerService = $this->_createService();

        try {
            $customerService->createCustomer($customerDetails);
        } catch (InputException $inputException) {
            $this->assertEquals(InputException::DEFAULT_MESSAGE, $inputException->getRawMessage());
            $this->assertEquals(InputException::DEFAULT_MESSAGE, $inputException->getMessage());
            $this->assertEquals(InputException::DEFAULT_MESSAGE, $inputException->getLogMessage());

            $errors = $inputException->getErrors();
            $this->assertCount(6, $errors);

            $this->assertEquals(InputException::REQUIRED_FIELD, $errors[0]->getRawMessage());
            $this->assertEquals('firstname is a required field.', $errors[0]->getMessage());
            $this->assertEquals('firstname is a required field.', $errors[0]->getLogMessage());

            $this->assertEquals(InputException::REQUIRED_FIELD, $errors[1]->getRawMessage());
            $this->assertEquals('lastname is a required field.', $errors[1]->getMessage());
            $this->assertEquals('lastname is a required field.', $errors[1]->getLogMessage());

            $this->assertEquals(InputException::INVALID_FIELD_VALUE, $errors[2]->getRawMessage());
            $this->assertEquals(
                'Invalid value of "missingAtSign" provided for the email field.',
                $errors[2]->getMessage()
            );
            $this->assertEquals(
                'Invalid value of "missingAtSign" provided for the email field.',
                $errors[2]->getLogMessage()
            );

            $this->assertEquals(InputException::REQUIRED_FIELD, $errors[3]->getRawMessage());
            $this->assertEquals('dob is a required field.', $errors[3]->getMessage());
            $this->assertEquals('dob is a required field.', $errors[3]->getLogMessage());

            $this->assertEquals(InputException::REQUIRED_FIELD, $errors[4]->getRawMessage());
            $this->assertEquals('taxvat is a required field.', $errors[4]->getMessage());
            $this->assertEquals('taxvat is a required field.', $errors[4]->getLogMessage());

            $this->assertEquals(InputException::REQUIRED_FIELD, $errors[5]->getRawMessage());
            $this->assertEquals('gender is a required field.', $errors[5]->getMessage());
            $this->assertEquals('gender is a required field.', $errors[5]->getLogMessage());
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
        $this->assertEquals(4, count(ExtensibleDataObjectConverter::toFlatArray($actualCustomer)));
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
            'getAllAttributesMetadata'
        )->will(
            $this->returnValue(array())
        );

        $customerService = $this->_createService();
        $filterBuilder = $this->_objectManager->getObject('\Magento\Framework\Service\V1\Data\FilterBuilder');
        $filter = $filterBuilder->setField('email')->setValue('customer@search.example.com')->create();
        $this->_searchBuilder->addFilter([$filter]);

        $searchResults = $customerService->searchCustomers($this->_searchBuilder->create());
        $this->assertEquals(0, $searchResults->getTotalCount());
    }

    public function testSearchCustomers()
    {
        $collectionMock = $this->getMockBuilder('\Magento\Customer\Model\Resource\Customer\Collection')
            ->disableOriginalConstructor()
            ->setMethods(
                ['addNameToSelect', 'addFieldToFilter', 'getSize', 'load', 'getItems', 'getIterator', 'joinAttribute']
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
            'getAllAttributesMetadata'
        )->will(
            $this->returnValue(array())
        );

        $customerService = $this->_createService();
        $filterBuilder = $this->_objectManager->getObject('\Magento\Framework\Service\V1\Data\FilterBuilder');
        $filter = $filterBuilder->setField('email')->setValue(self::EMAIL)->create();
        $this->_searchBuilder->addFilter([$filter]);

        $searchResults = $customerService->searchCustomers($this->_searchBuilder->create());
        $this->assertEquals(1, $searchResults->getTotalCount());
        $this->assertEquals(self::EMAIL, $searchResults->getItems()[0]->getCustomer()->getEmail());
    }

    public function testGetCustomerDetails()
    {
        $customerMock = $this->getMockBuilder('\Magento\Customer\Service\V1\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock = $this->getMockBuilder('\Magento\Customer\Service\V1\Data\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_converter = $this->getMockBuilder('\Magento\Customer\Model\Converter')
            ->disableOriginalConstructor()
            ->getMock();
        $service = $this->_createService();
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
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetCustomerDetailsWithException()
    {
        $customerMock = $this->getMockBuilder('\Magento\Customer\Service\V1\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock = $this->getMockBuilder('\Magento\Customer\Service\V1\Data\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_converter = $this->getMockBuilder('\Magento\Customer\Model\Converter')
            ->disableOriginalConstructor()
            ->getMock();
        $service = $this->_createService();
        $this->_customerRegistry->expects($this->once())
            ->method('retrieve')
            ->will(
                $this->throwException(
                    new NoSuchEntityException(
                        NoSuchEntityException::MESSAGE_SINGLE_FIELD,
                        [
                            'fieldName' => 'testField',
                            'fieldValue'     => 'value',
                        ]
                    )
                )
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
        $this->_converter = $this->getMockBuilder('\Magento\Customer\Model\Converter')
            ->disableOriginalConstructor()
            ->getMock();
        $service = $this->_createService();
        $this->_customerRegistry->expects($this->once())
            ->method('retrieveByEmail')
            ->will(
                $this->throwException(
                    new NoSuchEntityException(
                        NoSuchEntityException::MESSAGE_SINGLE_FIELD,
                        ['fieldName' => 'testField', 'fieldValue' => 'value']
                    )
                )
            );
        $this->assertTrue($service->isEmailAvailable('email', 1));
    }

    public function testIsEmailAvailableNegative()
    {
        $service = $this->_createService();
        $this->assertFalse($service->isEmailAvailable('email', 1));
    }

    public function testIsEmailAvailableDefaultWebsite()
    {
        $customerMock = $this->getMockBuilder(
            '\Magento\Customer\Service\V1\Data\Customer'
        )->disableOriginalConstructor()->getMock();
        $this->_converter = $this->getMockBuilder(
            '\Magento\Customer\Model\Converter'
        )->disableOriginalConstructor()->getMock();
        $service = $this->_createService();

        $defaultWebsiteId = 7;
        $this->_storeMock->expects($this->once())->method('getWebSiteId')->will($this->returnValue($defaultWebsiteId));
        $this->_customerRegistry->expects(
            $this->once()
        )->method('retrieveByEmail')->with('email', $defaultWebsiteId)->will($this->returnValue($customerMock));
        $this->assertFalse($service->isEmailAvailable('email'));
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



        $this->_customerModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(true));

        $exception = new MailException(__('The mail server is down'));

        $this->_customerModelMock->expects($this->once())
            ->method('sendNewAccountEmail')
            ->will($this->throwException($exception));

        $this->_loggerMock->expects($this->once())
            ->method('logException')
            ->with($exception);

        $mockCustomer = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCustomer->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(true));

        $mockCustomer->expects($this->once())
            ->method('__toArray')
            ->will($this->returnValue(['attributeSetId' => true]));

        $this->_customerModelMock->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue([]));

        /**
         * @var Data\CustomerDetails | \PHPUnit_Framework_MockObject_MockObject
         */
        $mockCustomerDetail = $this->getMockBuilder('Magento\Customer\Service\V1\Data\CustomerDetails')
            ->disableOriginalConstructor()
            ->getMock();

        $mockCustomerDetail->expects($this->once())
            ->method('getCustomer')
            ->will($this->returnValue($mockCustomer));

        $service = $this->_createService();
        $service->createCustomer($mockCustomerDetail, 'abc123');
        // If we get no mail exception, the test in considered a success
    }

    public function testGetCustomerByEmail()
    {

        $this->_converter = $this->getMockBuilder('Magento\Customer\Model\Converter')
            ->disableOriginalConstructor()->getMock();

        $this->_customerRegistry->expects($this->any())
            ->method('retrieveByEmail')
            ->will($this->returnValue($this->_customerModelMock));

        $customerDataMock = $this->getMockBuilder(
            'Magento\Customer\Service\V1\Data\Customer'
        )->setMethods(['getId', 'getFirstname', 'getLastname', 'getEmail'])
            ->disableOriginalConstructor()->getMock();

        $defaultWebsiteId = 7;

        $this->_mockReturnValue(
            $customerDataMock,
            array(
                'getId' => self::ID,
                'getFirstname' => self::FIRSTNAME,
                'getLastname' => self::LASTNAME,
                'getName' => self::NAME,
                'getEmail' => self::EMAIL
            )
        );

        $this->_storeMock->expects($this->any())->method('getWebSiteId')->will($this->returnValue($defaultWebsiteId));
        $this->_converter->expects($this->once())
            ->method('createCustomerFromModel')->with($this->_customerModelMock)
            ->will($this->returnValue($customerDataMock));

        $customerService = $this->_createService();
        $actualCustomer = $customerService->getCustomerByEmail(self::EMAIL);

        $this->assertEquals(self::ID, $actualCustomer->getId());
        $this->assertEquals(self::FIRSTNAME, $actualCustomer->getFirstName());
        $this->assertEquals(self::LASTNAME, $actualCustomer->getLastName());
        $this->assertEquals(self::EMAIL, $actualCustomer->getEmail());
    }

    public function testGetCustomerDetailsByEmail()
    {
        $this->_converter = $this->getMockBuilder('Magento\Customer\Model\Converter')
            ->disableOriginalConstructor()->getMock();

        $customerDataMock = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Customer')
            ->disableOriginalConstructor()->getMock();

        $addressMock = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Address')
            ->disableOriginalConstructor()->getMock();

        $this->_customerAddressServiceMock
            ->expects($this->once())
            ->method('getAddresses')
            ->will($this->returnValue(array($addressMock)));
        $defaultWebsiteId = 7;
        $this->_storeMock->expects($this->any())->method('getWebSiteId')->will($this->returnValue($defaultWebsiteId));
        $this->_customerRegistry->expects($this->once())
            ->method('retrieveByEmail')->with(self::EMAIL, $defaultWebsiteId)
            ->will($this->returnValue($this->_customerModelMock));
        $this->_converter->expects($this->once())
            ->method('createCustomerFromModel')->with($this->_customerModelMock)
            ->will($this->returnValue($customerDataMock));

        $customerService = $this->_createService();
        $actualCustomerDetails = $customerService->getCustomerDetailsByEmail(self::EMAIL, $defaultWebsiteId);

        $this->assertEquals($customerDataMock, $actualCustomerDetails->getCustomer());
        $this->assertEquals(array($addressMock), $actualCustomerDetails->getAddresses());

    }

    public function testDeleteCustomerByEmail()
    {
        $this->_converter = $this->getMockBuilder('\Magento\Customer\Model\Converter')
            ->setMethods(['getCustomerModelByEmail', 'createCustomerFromModel'])
            ->disableOriginalConstructor()
            ->getMock();

        $defaultWebsiteId = 7;

        $this->_storeMock->expects($this->any())
            ->method('getWebSiteId')
            ->will($this->returnValue($defaultWebsiteId));
        $this->_customerRegistry->expects($this->once())
            ->method('retrieveByEmail')
            ->with(self::EMAIL, $defaultWebsiteId)
            ->will($this->returnValue($this->_customerModelMock));
        $this->_customerModelMock->expects($this->once())
            ->method('delete');

        $customerService = $this->_createService();
        $this->assertTrue($customerService->deleteCustomerByEmail(self::EMAIL, $defaultWebsiteId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InputMismatchException
     * @expectedExceptionMessage Customer with the same email already exists in associated website.
     */
    public function testCreateCustomerWithPasswordHashEmailExists()
    {
        $storeId = 5;
        $customerData = array(
            'customer_id' => 0,
            'email' => self::EMAIL,
            'firstname' => self::FIRSTNAME,
            'lastname' => self::LASTNAME,
            'website_id' => self::WEBSITE_ID,
            'create_in' => 'Admin',
            'password' => 'password'
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($customerEntity)->create();

        $this->_storeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($storeId));

        $this->_websiteMock->expects($this->once())
            ->method('getDefaultStore')
            ->will($this->returnValue($this->_storeMock));

        $this->_storeManagerMock->expects($this->once())
            ->method('getWebsite')
            ->with($this->equalTo(self::WEBSITE_ID))
            ->will($this->returnValue($this->_websiteMock));

        $this->_converter = $this->getMock('Magento\Customer\Model\Converter', ['createCustomerModel'], [], '', false);
        $this->_converter->expects($this->once())
            ->method('createCustomerModel')
            ->will(
                $this->throwException(
                    new \Magento\Customer\Exception(
                        'exception message',
                        \Magento\Customer\Model\Customer::EXCEPTION_EMAIL_EXISTS
                    )
                )
            );

        $customerService = $this->_createService();
        $customerService->createCustomerWithPasswordHash($customerDetails, '', '');
    }

    /**
     * @expectedException \Magento\Customer\Exception
     * @expectedExceptionMessage exception message
     */
    public function testCreateCustomerWithPasswordHashException()
    {
        $storeId = 5;
        $customerData = array(
            'id' => self::ID,
            'email' => self::EMAIL,
            'firstname' => self::FIRSTNAME,
            'lastname' => self::LASTNAME,
            'store_id' => $storeId,
            'website_id' => self::WEBSITE_ID
        );
        $this->_customerBuilder->populateWithArray($customerData);
        $customerEntity = $this->_customerBuilder->create();
        $customerDetails = $this->_customerDetailsBuilder->setCustomer($customerEntity)->create();

        $this->_storeManagerMock->expects($this->once())
            ->method('getStores')
            ->will($this->returnValue(array()));

        $this->_converter = $this->getMock(
            'Magento\Customer\Model\Converter',
            array('getCustomerModel', 'createCustomerModel'),
            array(),
            '',
            false
        );
        $this->_converter->expects($this->once())
            ->method('getCustomerModel')
            ->will($this->returnValue($this->_customerModelMock));
        $this->_converter->expects($this->once())
            ->method('createCustomerModel')
            ->will(
                $this->throwException(
                    new \Magento\Customer\Exception(
                        'exception message',
                        0
                    )
                )
            );

        $customerService = $this->_createService();
        $customerService->createCustomerWithPasswordHash($customerDetails, '', '');
    }

    private function _setupStoreMock()
    {
        $this->_storeManagerMock = $this->getMockBuilder(
            '\Magento\Framework\StoreManagerInterface'
        )->disableOriginalConstructor()->getMock();

        $this->_storeMock = $this->getMockBuilder(
            '\Magento\Store\Model\Store'
        )->disableOriginalConstructor()->getMock();

        $this->_websiteMock = $this->getMock(
            'Magento\Store\Model\Website',
            array('__wakeup', 'getDefaultStore'),
            array(),
            '',
            false
        );

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
        $searchResultBuilder = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\SearchResultsBuilder'
        );
        $customerService = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\CustomerAccountService',
            [
                'customerFactory' => $this->_customerFactoryMock,
                'storeManager' => $this->_storeManagerMock,
                'converter' => $this->_converter,
                'searchResultsBuilder' => $searchResultBuilder,
                'customerBuilder' => $this->_customerBuilder,
                'customerDetailsBuilder' => $this->_customerDetailsBuilder,
                'customerAddressService' => $this->_customerAddressServiceMock,
                'customerMetadataService' => $this->_customerMetadataService,
                'customerRegistry' => $this->_customerRegistry,
                'encryptor' => $this->_encryptorMock,
                'logger' => $this->_loggerMock,
                'url' => $this->_urlMock,
                'stringHelper' => new \Magento\Framework\Stdlib\String(),
                'mathRandom' => new \Magento\Framework\Math\Random()
            ]
        );
        return $customerService;
    }
}
