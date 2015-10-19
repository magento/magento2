<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Model;

use Magento\User\Model\UserValidationRules;

/**
 * Test class for \Magento\User\Model\User testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\User\Model\User */
    protected $model;

    /** @var \Magento\User\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $userDataMock;

    /** @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $transportBuilderMock;

    /** @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\User\Model\ResourceModel\User|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;

    /** @var \Magento\Framework\Data\Collection\AbstractDb|\PHPUnit_Framework_MockObject_MockObject */
    protected $collectionMock;

    /** @var \Magento\Framework\Mail\TransportInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $transportMock;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    /** @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject */
    protected $storetMock;

    /** @var \Magento\Backend\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /** @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $encryptorMock;

    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManagerMock;

    /** @var \Magento\Framework\Validator\DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $validatorObjectFactoryMock;

    /** @var \Magento\User\Model\UserValidationRules|\PHPUnit_Framework_MockObject_MockObject */
    protected $validationRulesMock;

    /** @var \Magento\Authorization\Model\RoleFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $roleFactoryMock;

    /**
     * Set required values
     */
    protected function setUp()
    {
        $this->userDataMock = $this->getMockBuilder(
            'Magento\User\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->contextMock = $this->getMockBuilder(
            'Magento\Framework\Model\Context'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->resourceMock = $this->getMockBuilder(
            'Magento\User\Model\ResourceModel\User'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->collectionMock = $this->getMockBuilder(
            'Magento\Framework\Data\Collection\AbstractDb'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMockForAbstractClass();
        $coreRegistry = $this->getMockBuilder(
            'Magento\Framework\Registry'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->eventManagerMock = $this->getMockBuilder(
            'Magento\Framework\Event\ManagerInterface'
        )->disableOriginalConstructor()->setMethods(
            ['dispatch']
        )->getMockForAbstractClass();
        $this->validatorObjectFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Validator\DataObjectFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $this->roleFactoryMock = $this->getMockBuilder(
            'Magento\Authorization\Model\RoleFactory'
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();
        $this->transportMock = $this->getMockBuilder(
            'Magento\Framework\Mail\TransportInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->transportBuilderMock = $this->getMockBuilder(
            'Magento\Framework\Mail\Template\TransportBuilder'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->storetMock = $this->getMockBuilder(
            'Magento\Store\Model\Store'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $this->storeManagerMock = $this->getMockBuilder(
            'Magento\Store\Model\StoreManagerInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $this->configMock = $this->getMockBuilder(
            'Magento\Backend\App\ConfigInterface'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $this->validationRulesMock = $this->getMockBuilder(
            'Magento\User\Model\UserValidationRules'
        )->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $this->encryptorMock = $this->getMockBuilder('Magento\Framework\Encryption\EncryptorInterface')
            ->setMethods(['validateHash'])
            ->getMockForAbstractClass();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            'Magento\User\Model\User',
            [
                'eventManager' => $this->eventManagerMock,
                'userData' => $this->userDataMock,
                'registry' => $coreRegistry,
                'resource' => $this->resourceMock,
                'resourceCollection' => $this->collectionMock,
                'validatorObjectFactory' => $this->validatorObjectFactoryMock,
                'roleFactory' => $this->roleFactoryMock,
                'transportBuilder' => $this->transportBuilderMock,
                'storeManager' => $this->storeManagerMock,
                'validationRules' => $this->validationRulesMock,
                'config' => $this->configMock,
                'encryptor' => $this->encryptorMock
            ]
        );
    }

    public function testSendPasswordResetNotificationEmail()
    {
        $storeId = 0;
        $email = 'test@example.com';
        $firstName = 'Foo';
        $lastName = 'Bar';

        $this->model->setEmail($email);
        $this->model->setFirstname($firstName);
        $this->model->setLastname($lastName);

        $this->configMock->expects(
            $this->at(0)
        )->method(
            'getValue'
        )->with(
            \Magento\User\Model\User::XML_PATH_RESET_PASSWORD_TEMPLATE
        )->will(
            $this->returnValue('templateId')
        );
        $this->configMock->expects(
            $this->at(1)
        )->method(
            'getValue'
        )->with(
            \Magento\User\Model\User::XML_PATH_FORGOT_EMAIL_IDENTITY
        )->will(
            $this->returnValue('sender')
        );
        $this->transportBuilderMock->expects($this->once())->method('setTemplateOptions')->will($this->returnSelf());
        $this->transportBuilderMock->expects(
            $this->once()
        )->method(
            'setTemplateVars'
        )->with(
            ['user' => $this->model, 'store' => $this->storetMock]
        )->will(
            $this->returnSelf()
        );
        $this->transportBuilderMock->expects(
            $this->once()
        )->method(
            'addTo'
        )->with(
            $this->equalTo($email),
            $this->equalTo($firstName . ' ' . $lastName)
        )->will(
            $this->returnSelf()
        );
        $this->transportBuilderMock->expects(
            $this->once()
        )->method(
            'setFrom'
        )->with(
            'sender'
        )->will(
            $this->returnSelf()
        );
        $this->transportBuilderMock->expects(
            $this->once()
        )->method(
            'setTemplateIdentifier'
        )->with(
            'templateId'
        )->will(
            $this->returnSelf()
        );
        $this->transportBuilderMock->expects(
            $this->once()
        )->method(
            'getTransport'
        )->will(
            $this->returnValue($this->transportMock)
        );
        $this->transportMock->expects($this->once())->method('sendMessage');

        $this->storeManagerMock->expects(
            $this->once()
        )->method(
            'getStore'
        )->with(
            $storeId
        )->will(
            $this->returnValue($this->storetMock)
        );

        $this->assertInstanceOf('\Magento\User\Model\User', $this->model->sendPasswordResetNotificationEmail());
    }

    public function testSendPasswordResetConfirmationEmail()
    {
        $storeId = 0;
        $email = 'test@example.com';
        $firstName = 'Foo';
        $lastName = 'Bar';

        $this->model->setEmail($email);
        $this->model->setFirstname($firstName);
        $this->model->setLastname($lastName);

        $this->configMock->expects(
            $this->at(0)
        )->method(
            'getValue'
        )->with(
            \Magento\User\Model\User::XML_PATH_FORGOT_EMAIL_TEMPLATE
        )->will(
            $this->returnValue('templateId')
        );
        $this->configMock->expects(
            $this->at(1)
        )->method(
            'getValue'
        )->with(
            \Magento\User\Model\User::XML_PATH_FORGOT_EMAIL_IDENTITY
        )->will(
            $this->returnValue('sender')
        );
        $this->transportBuilderMock->expects($this->once())->method('setTemplateOptions')->will($this->returnSelf());
        $this->transportBuilderMock->expects(
            $this->once()
        )->method(
            'setTemplateVars'
        )->with(
            ['user' => $this->model, 'store' => $this->storetMock]
        )->will(
            $this->returnSelf()
        );
        $this->transportBuilderMock->expects(
            $this->once()
        )->method(
            'addTo'
        )->with(
            $this->equalTo($email),
            $this->equalTo($firstName . ' ' . $lastName)
        )->will(
            $this->returnSelf()
        );
        $this->transportBuilderMock->expects(
            $this->once()
        )->method(
            'setFrom'
        )->with(
            'sender'
        )->will(
            $this->returnSelf()
        );
        $this->transportBuilderMock->expects(
            $this->once()
        )->method(
            'setTemplateIdentifier'
        )->with(
            'templateId'
        )->will(
            $this->returnSelf()
        );
        $this->transportBuilderMock->expects(
            $this->once()
        )->method(
            'getTransport'
        )->will(
            $this->returnValue($this->transportMock)
        );
        $this->transportMock->expects($this->once())->method('sendMessage');

        $this->storeManagerMock->expects(
            $this->once()
        )->method(
            'getStore'
        )->with(
            $storeId
        )->will(
            $this->returnValue($this->storetMock)
        );

        $this->assertInstanceOf('\Magento\User\Model\User', $this->model->sendPasswordResetConfirmationEmail());
    }

    public function testVerifyIdentity()
    {
        $password = 'password';
        $this->encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->model->getPassword())
            ->will($this->returnValue(true));
        $this->model->setIsActive(true);
        $this->resourceMock->expects($this->once())->method('hasAssigned2Role')->will($this->returnValue(true));
        $this->assertTrue(
            $this->model->verifyIdentity($password),
            'Identity verification failed while should have passed.'
        );
    }

    public function testVerifyIdentityFailure()
    {
        $password = 'password';
        $this->encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->model->getPassword())
            ->will($this->returnValue(false));
        $this->assertFalse(
            $this->model->verifyIdentity($password),
            'Identity verification passed while should have failed.'
        );
    }

    public function testVerifyIdentityInactiveRecord()
    {
        $password = 'password';
        $this->encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->model->getPassword())
            ->will($this->returnValue(true));
        $this->model->setIsActive(false);
        $this->setExpectedException(
            'Magento\\Framework\\Exception\\AuthenticationException',
            'You did not sign in correctly or your account is temporarily disabled.'
        );
        $this->model->verifyIdentity($password);
    }

    public function testVerifyIdentityNoAssignedRoles()
    {
        $password = 'password';
        $this->encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->model->getPassword())
            ->will($this->returnValue(true));
        $this->model->setIsActive(true);
        $this->resourceMock->expects($this->once())->method('hasAssigned2Role')->will($this->returnValue(false));
        $this->setExpectedException(
            'Magento\\Framework\\Exception\\AuthenticationException',
            'You need more permissions to access this.'
        );
        $this->model->verifyIdentity($password);
    }

    public function testSleep()
    {
        $excludedProperties = [
            '_eventManager',
            '_cacheManager',
            '_registry',
            '_appState',
            '_userData',
            '_config',
            '_validatorObject',
            '_roleFactory',
            '_encryptor',
            '_transportBuilder',
            '_storeManager',
            '_validatorBeforeSave'
        ];
        $actualResult = $this->model->__sleep();
        $this->assertNotEmpty($actualResult);
        $expectedResult = array_intersect($actualResult, $excludedProperties);
        $this->assertEmpty($expectedResult);
    }

    public function testBeforeSave()
    {
        $this->eventManagerMock->expects($this->any())->method('dispatch');
        $this->model->setIsActive(1);
        $actualData = $this->model->beforeSave()->getData();
        $this->assertArrayHasKey('modified', $actualData);
        $this->assertArrayHasKey('extra', $actualData);
        $this->assertArrayHasKey('password', $actualData);
        $this->assertArrayHasKey('is_active', $actualData);
    }

    public function testValidateOk()
    {
        /** @var $validatorMock \Magento\Framework\Validator\DataObject|\PHPUnit_Framework_MockObject_MockObject */
        $validatorMock = $this->getMockBuilder('Magento\Framework\Validator\DataObject')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->validatorObjectFactoryMock->expects($this->once())->method('create')->willReturn($validatorMock);
        $this->validationRulesMock->expects($this->once())
            ->method('addUserInfoRules')
            ->with($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->willReturn(true);
        $this->assertTrue($this->model->validate());
    }

    public function testValidateInvalid()
    {
        $messages = ['Invalid username'];
        /** @var $validatorMock \Magento\Framework\Validator\DataObject|\PHPUnit_Framework_MockObject_MockObject */
        $validatorMock = $this->getMockBuilder('Magento\Framework\Validator\DataObject')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->validatorObjectFactoryMock->expects($this->once())->method('create')->willReturn($validatorMock);
        $this->validationRulesMock->expects($this->once())
            ->method('addUserInfoRules')
            ->with($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->willReturn(false);
        $validatorMock->expects($this->once())->method('getMessages')->willReturn($messages);
        $this->assertEquals($messages, $this->model->validate());
    }

    public function testSaveExtra()
    {
        $data = [1, 2, 3];
        $this->resourceMock->expects($this->once())->method('saveExtra')->with($this->model, serialize($data));
        $this->assertInstanceOf('Magento\User\Model\User', $this->model->saveExtra($data));
    }

    public function testGetRoles()
    {
        $this->resourceMock->expects($this->once())->method('getRoles')->with($this->model)->willReturn([]);
        $this->assertInternalType('array', $this->model->getRoles());
    }

    public function testGetRole()
    {
        $roles = ['role'];
        $roleMock = $this->getMockBuilder('Magento\Authorization\Model\Role')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($roleMock);
        $this->resourceMock->expects($this->once())->method('getRoles')->with($this->model)->willReturn($roles);
        $roleMock->expects($this->once())->method('load')->with($roles[0]);
        $this->assertInstanceOf('Magento\Authorization\Model\Role', $this->model->getRole());
    }

    public function testDeleteFromRole()
    {
        $this->resourceMock->expects($this->once())->method('deleteFromRole')->with($this->model);
        $this->assertInstanceOf('Magento\User\Model\User', $this->model->deleteFromRole());
    }

    public function testRoleUserExistsTrue()
    {
        $result = ['role'];
        $this->resourceMock->expects($this->once())->method('roleUserExists')->with($this->model)->willReturn($result);
        $this->assertTrue($this->model->roleUserExists());
    }

    public function testRoleUserExistsFalse()
    {
        $result = [];
        $this->resourceMock->expects($this->once())->method('roleUserExists')->with($this->model)->willReturn($result);
        $this->assertFalse($this->model->roleUserExists());
    }

    public function testGetAclRole()
    {
        $roles = ['role'];
        $result = 1;
        $roleMock = $this->getMockBuilder('Magento\Authorization\Model\Role')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($roleMock);
        $this->resourceMock->expects($this->once())->method('getRoles')->with($this->model)->willReturn($roles);
        $roleMock->expects($this->once())->method('load')->with($roles[0]);
        $roleMock->expects($this->once())->method('getId')->willReturn($result);
        $this->assertEquals($result, $this->model->getAclRole());
    }

    /**
     * @dataProvider authenticateDataProvider
     */
    public function testAuthenticate($usernameIn, $usernameOut, $expectedResult)
    {
        $password = 'password';
        $config = 'config';

        $data = ['id' => 1, 'is_active' => 1, 'username' => $usernameOut];

        $this->configMock->expects($this->once())
            ->method('isSetFlag')
            ->with('admin/security/use_case_sensitive_login')
            ->willReturn($config);
        $this->eventManagerMock->expects($this->any())->method('dispatch');

        $this->resourceMock->expects($this->any())->method('loadByUsername')->willReturn($data);
        $this->model->setIdFieldName('id');

        $this->encryptorMock->expects($this->any())->method('validateHash')->willReturn(true);
        $this->resourceMock->expects($this->any())->method('hasAssigned2Role')->willReturn(true);
        $this->assertEquals($expectedResult, $this->model->authenticate($usernameIn, $password));
    }

    public function authenticateDataProvider()
    {
        return [
            'success' => [
                'usernameIn' => 'username',
                'usernameOut' => 'username',
                'expectedResult' => true
            ],
            'failedUsername' => [
                'usernameIn' => 'username1',
                'usernameOut' => 'username2',
                'expectedResult' => false
            ]
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testAuthenticateException()
    {
        $username = 'username';
        $password = 'password';
        $config = 'config';

        $this->configMock->expects($this->once())
            ->method('isSetFlag')
            ->with('admin/security/use_case_sensitive_login')
            ->willReturn($config);

        $this->eventManagerMock->expects($this->any())->method('dispatch');
        $this->resourceMock->expects($this->once())
            ->method('loadByUsername')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__()));
        $this->model->authenticate($username, $password);
    }

    public function testChangeResetPasswordLinkToken()
    {
        $token = '1';
        $this->assertInstanceOf('Magento\User\Model\User', $this->model->changeResetPasswordLinkToken($token));
        $this->assertEquals($token, $this->model->getRpToken());
        $this->assertInternalType('string', $this->model->getRpTokenCreatedAt());
    }

    public function testIsResetPasswordLinkTokenExpiredEmptyToken()
    {
        $this->assertTrue($this->model->isResetPasswordLinkTokenExpired());
    }

    public function testIsResetPasswordLinkTokenExpiredIsExpiredToken()
    {
        $this->model->setRpToken('1');
        $this->model->setRpTokenCreatedAt(
            (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        );
        $this->userDataMock->expects($this->once())->method('getResetPasswordLinkExpirationPeriod')->willReturn(0);
        $this->assertTrue($this->model->isResetPasswordLinkTokenExpired());
    }

    public function testIsResetPasswordLinkTokenExpiredIsNotExpiredToken()
    {
        $this->model->setRpToken('1');
        $this->model->setRpTokenCreatedAt(
            (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        );
        $this->userDataMock->expects($this->once())->method('getResetPasswordLinkExpirationPeriod')->willReturn(1);
        $this->assertFalse($this->model->isResetPasswordLinkTokenExpired());
    }
}
