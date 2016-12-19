<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Model;

use Magento\Framework\Serialize\SerializerInterface;
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
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;
    /**
     * Set required values
     * @return void
     */
    protected function setUp()
    {
        $this->userDataMock = $this->getMockBuilder(\Magento\User\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->resourceMock = $this->getMockBuilder(\Magento\User\Model\ResourceModel\User::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->collectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $coreRegistry = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();
        $this->validatorObjectFactoryMock = $this->getMockBuilder(\Magento\Framework\Validator\DataObjectFactory::class)
            ->disableOriginalConstructor()->setMethods(['create'])
            ->getMock();
        $this->roleFactoryMock = $this->getMockBuilder(\Magento\Authorization\Model\RoleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->transportMock = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->transportBuilderMock = $this->getMockBuilder(\Magento\Framework\Mail\Template\TransportBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->storetMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->configMock = $this->getMockBuilder(\Magento\Backend\App\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->validationRulesMock = $this->getMockBuilder(\Magento\User\Model\UserValidationRules::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->setMethods(['validateHash'])
            ->getMockForAbstractClass();

        $this->serializer = $this->getMock(SerializerInterface::class, ['serialize', 'unserialize'], [], '', false);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\User\Model\User::class,
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
                'encryptor' => $this->encryptorMock,
                'serializer' => $this->serializer
            ]
        );
    }

    /**
     * @return void
     */
    public function testSendNotificationEmailsIfRequired()
    {
        $storeId = 0;
        $email = 'test1@example.com';
        $origEmail = 'test2@example.com';

        $password = '1234567';
        $origPassword = '123456789';

        $username = 'admin1';
        $origUsername = 'admin2';

        $firstName = 'Foo';
        $lastName = 'Bar';

        $changes = __('email') . ', ' . __('password') . ', ' . __('username');

        $this->model->setEmail($email);
        $this->model->setOrigData('email', $origEmail);

        $this->model->setPassword($password);
        $this->model->setOrigData('password', $origPassword);

        $this->model->setUsername($username);
        $this->model->setOrigData('username', $origUsername);

        $this->model->setFirstname($firstName);
        $this->model->setLastname($lastName);

        $this->configMock->expects($this->exactly(4))
            ->method('getValue')
            ->withConsecutive(
                [\Magento\User\Model\User::XML_PATH_USER_NOTIFICATION_TEMPLATE],
                [\Magento\User\Model\User::XML_PATH_FORGOT_EMAIL_IDENTITY],
                [\Magento\User\Model\User::XML_PATH_USER_NOTIFICATION_TEMPLATE],
                [\Magento\User\Model\User::XML_PATH_FORGOT_EMAIL_IDENTITY]
            )->willReturnOnConsecutiveCalls(
                'templateId',
                'sender',
                'templateId',
                'sender'
            );

        $this->transportBuilderMock->expects($this->exactly(2))
            ->method('setTemplateModel')
            ->with($this->equalTo(\Magento\Email\Model\BackendTemplate::class))
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->exactly(2))
            ->method('setTemplateOptions')
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->exactly(2))
            ->method('setTemplateVars')
            ->with(['user' => $this->model, 'store' => $this->storetMock, 'changes' => $changes])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->exactly(2))
            ->method('addTo')
            ->withConsecutive(
                $this->equalTo($email),
                $this->equalTo($firstName . ' ' . $lastName),
                $this->equalTo($origEmail),
                $this->equalTo($firstName . ' ' . $lastName)
            )
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->exactly(2))
            ->method('setFrom')
            ->with('sender')
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->exactly(2))
            ->method('setTemplateIdentifier')
            ->with('templateId')
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->exactly(2))
            ->method('getTransport')
            ->willReturn($this->transportMock);
        $this->transportMock->expects($this->exactly(2))->method('sendMessage');

        $this->storeManagerMock->expects($this->exactly(2))
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storetMock);

        $this->assertInstanceOf(\Magento\User\Model\User::class, $this->model->sendNotificationEmailsIfRequired());
    }

    /**
     * @return void
     */
    public function testSendPasswordResetConfirmationEmail()
    {
        $storeId = 0;
        $email = 'test@example.com';
        $firstName = 'Foo';
        $lastName = 'Bar';

        $this->model->setEmail($email);
        $this->model->setFirstname($firstName);
        $this->model->setLastname($lastName);

        $this->configMock->expects($this->at(0))
            ->method('getValue')
            ->with(\Magento\User\Model\User::XML_PATH_FORGOT_EMAIL_TEMPLATE)
            ->willReturn('templateId');
        $this->configMock->expects($this->at(1))
            ->method('getValue')
            ->with(\Magento\User\Model\User::XML_PATH_FORGOT_EMAIL_IDENTITY)
            ->willReturn('sender');
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateModel')
            ->with($this->equalTo(\Magento\Email\Model\BackendTemplate::class))
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->with(['user' => $this->model, 'store' => $this->storetMock])
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('addTo')
            ->with($this->equalTo($email), $this->equalTo($firstName . ' ' . $lastName))
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->with('sender')
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with('templateId')
            ->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->willReturn($this->transportMock);
        $this->transportMock->expects($this->once())->method('sendMessage');

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storetMock);

        $this->assertInstanceOf(\Magento\User\Model\User::class, $this->model->sendPasswordResetConfirmationEmail());
    }

    /**
     * @return void
     */
    public function testVerifyIdentity()
    {
        $password = 'password';
        $this->encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->model->getPassword())
            ->willReturn(true);
        $this->model->setIsActive(true);
        $this->resourceMock->expects($this->once())->method('hasAssigned2Role')->willReturn(true);
        $this->assertTrue(
            $this->model->verifyIdentity($password),
            'Identity verification failed while should have passed.'
        );
    }

    /**
     * @return void
     */
    public function testVerifyIdentityFailure()
    {
        $password = 'password';
        $this->encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->model->getPassword())
            ->willReturn(false);
        $this->assertFalse(
            $this->model->verifyIdentity($password),
            'Identity verification passed while should have failed.'
        );
    }

    /**
     * @return void
     */
    public function testVerifyIdentityInactiveRecord()
    {
        $password = 'password';
        $this->encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->model->getPassword())
            ->willReturn(true);
        $this->model->setIsActive(false);
        $this->setExpectedException(
            \Magento\Framework\Exception\AuthenticationException::class,
            'You did not sign in correctly or your account is temporarily disabled.'
        );
        $this->model->verifyIdentity($password);
    }

    /**
     * @return void
     */
    public function testVerifyIdentityNoAssignedRoles()
    {
        $password = 'password';
        $this->encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->model->getPassword())
            ->willReturn(true);
        $this->model->setIsActive(true);
        $this->resourceMock->expects($this->once())->method('hasAssigned2Role')->willReturn(false);
        $this->setExpectedException(
            \Magento\Framework\Exception\AuthenticationException::class,
            'You need more permissions to access this.'
        );
        $this->model->verifyIdentity($password);
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testBeforeSave()
    {
        $this->eventManagerMock->expects($this->any())->method('dispatch');
        $this->model->setIsActive(1);
        $actualData = $this->model->beforeSave()->getData();
        $this->assertArrayHasKey('extra', $actualData);
        $this->assertArrayHasKey('password', $actualData);
        $this->assertArrayHasKey('is_active', $actualData);
    }

    /**
     * @return void
     */
    public function testValidateOk()
    {
        /** @var $validatorMock \Magento\Framework\Validator\DataObject|\PHPUnit_Framework_MockObject_MockObject */
        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\DataObject::class)
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

    /**
     * @return void
     */
    public function testValidateInvalid()
    {
        $messages = ['Invalid username'];
        /** @var $validatorMock \Magento\Framework\Validator\DataObject|\PHPUnit_Framework_MockObject_MockObject */
        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\DataObject::class)
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

    /**
     * @return void
     */
    public function testSaveExtra()
    {
        $data = [1, 2, 3];
        $this->resourceMock->expects($this->once())
            ->method('saveExtra')
            ->with($this->model, json_encode($data));

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($data)
            ->will($this->returnValue(json_encode($data)));

        $this->assertInstanceOf(\Magento\User\Model\User::class, $this->model->saveExtra($data));
    }

    /**
     * @return void
     */
    public function testGetRoles()
    {
        $this->resourceMock->expects($this->once())->method('getRoles')->with($this->model)->willReturn([]);
        $this->assertInternalType('array', $this->model->getRoles());
    }

    /**
     * @return void
     */
    public function testGetRole()
    {
        $roles = ['role'];
        $roleMock = $this->getMockBuilder(\Magento\Authorization\Model\Role::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($roleMock);
        $this->resourceMock->expects($this->once())->method('getRoles')->with($this->model)->willReturn($roles);
        $roleMock->expects($this->once())->method('load')->with($roles[0]);
        $this->assertInstanceOf(\Magento\Authorization\Model\Role::class, $this->model->getRole());
    }

    /**
     * @return void
     */
    public function testDeleteFromRole()
    {
        $this->resourceMock->expects($this->once())->method('deleteFromRole')->with($this->model);
        $this->assertInstanceOf(\Magento\User\Model\User::class, $this->model->deleteFromRole());
    }

    /**
     * @return void
     */
    public function testRoleUserExistsTrue()
    {
        $result = ['role'];
        $this->resourceMock->expects($this->once())->method('roleUserExists')->with($this->model)->willReturn($result);
        $this->assertTrue($this->model->roleUserExists());
    }

    /**
     * @return void
     */
    public function testRoleUserExistsFalse()
    {
        $result = [];
        $this->resourceMock->expects($this->once())->method('roleUserExists')->with($this->model)->willReturn($result);
        $this->assertFalse($this->model->roleUserExists());
    }

    /**
     * @return void
     */
    public function testGetAclRole()
    {
        $roles = ['role'];
        $result = 1;
        $roleMock = $this->getMockBuilder(\Magento\Authorization\Model\Role::class)
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
     * @param string $usernameIn
     * @param string $usernameOut
     * @param bool $expectedResult
     * @return void
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

    /**
     * @return array
     */
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
     * @return void
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

    /**
     * @return void
     */
    public function testChangeResetPasswordLinkToken()
    {
        $token = '1';
        $this->assertInstanceOf(\Magento\User\Model\User::class, $this->model->changeResetPasswordLinkToken($token));
        $this->assertEquals($token, $this->model->getRpToken());
        $this->assertInternalType('string', $this->model->getRpTokenCreatedAt());
    }

    /**
     * @return void
     */
    public function testIsResetPasswordLinkTokenExpiredEmptyToken()
    {
        $this->assertTrue($this->model->isResetPasswordLinkTokenExpired());
    }

    /**
     * @return void
     */
    public function testIsResetPasswordLinkTokenExpiredIsExpiredToken()
    {
        $this->model->setRpToken('1');
        $this->model->setRpTokenCreatedAt(
            (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        );
        $this->userDataMock->expects($this->once())->method('getResetPasswordLinkExpirationPeriod')->willReturn(0);
        $this->assertTrue($this->model->isResetPasswordLinkTokenExpired());
    }

    /**
     * @return void
     */
    public function testIsResetPasswordLinkTokenExpiredIsNotExpiredToken()
    {
        $this->model->setRpToken('1');
        $this->model->setRpTokenCreatedAt(
            (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        );
        $this->userDataMock->expects($this->once())->method('getResetPasswordLinkExpirationPeriod')->willReturn(1);
        $this->assertFalse($this->model->isResetPasswordLinkTokenExpired());
    }

    public function testCheckPasswordChangeEqualToCurrent()
    {
        /** @var $validatorMock \Magento\Framework\Validator\DataObject|\PHPUnit_Framework_MockObject_MockObject */
        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->validatorObjectFactoryMock->expects($this->once())->method('create')->willReturn($validatorMock);
        $this->validationRulesMock->expects($this->once())
            ->method('addUserInfoRules')
            ->with($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->willReturn(true);

        $newPassword = "NEWmYn3wpassw0rd";
        $oldPassword = "OLDmYn3wpassw0rd";
        $this->model->setPassword($newPassword)
            ->setId(1)
            ->setOrigData('password', $oldPassword);
        $this->encryptorMock->expects($this->once())
            ->method('isValidHash')
            ->with($newPassword, $oldPassword)
            ->willReturn(true);
        $result = $this->model->validate();
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContains("Sorry, but this password has already been used.", (string)$result[0]);
    }

    public function testCheckPasswordChangeEqualToPrevious()
    {
        /** @var $validatorMock \Magento\Framework\Validator\DataObject|\PHPUnit_Framework_MockObject_MockObject */
        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->validatorObjectFactoryMock->expects($this->once())->method('create')->willReturn($validatorMock);
        $this->validationRulesMock->expects($this->once())
            ->method('addUserInfoRules')
            ->with($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->willReturn(true);

        $newPassword = "NEWmYn3wpassw0rd";
        $newPasswordHash = "new password hash";
        $oldPassword = "OLDmYn3wpassw0rd";
        $this->model->setPassword($newPassword)
            ->setId(1)
            ->setOrigData('password', $oldPassword);
        $this->encryptorMock->expects($this->atLeastOnce())
            ->method('isValidHash')
            ->will($this->onConsecutiveCalls(false, true));

        $this->resourceMock->expects($this->once())->method('getOldPasswords')->willReturn(['hash1', $newPasswordHash]);

        $result = $this->model->validate();
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContains("Sorry, but this password has already been used.", (string)$result[0]);
    }

    public function testCheckPasswordChangeValid()
    {
        /** @var $validatorMock \Magento\Framework\Validator\DataObject|\PHPUnit_Framework_MockObject_MockObject */
        $validatorMock = $this->getMockBuilder(\Magento\Framework\Validator\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->validatorObjectFactoryMock->expects($this->once())->method('create')->willReturn($validatorMock);
        $this->validationRulesMock->expects($this->once())
            ->method('addUserInfoRules')
            ->with($validatorMock);
        $validatorMock->expects($this->once())->method('isValid')->willReturn(true);

        $newPassword = "NEWmYn3wpassw0rd";
        $oldPassword = "OLDmYn3wpassw0rd";
        $this->model->setPassword($newPassword)
            ->setId(1)
            ->setOrigData('password', $oldPassword);
        $this->encryptorMock->expects($this->atLeastOnce())
            ->method('isValidHash')
            ->will($this->onConsecutiveCalls(false, false, false));

        $this->resourceMock->expects($this->once())->method('getOldPasswords')->willReturn(['hash1', 'hash2']);

        $result = $this->model->validate();
        $this->assertTrue($result);
    }

    /**
     * Test for performIdentityCheck method
     *
     * @param bool $verifyIdentityResult
     * @param bool $lockExpires
     * @dataProvider dataProviderPerformIdentityCheck
     */
    public function testPerformIdentityCheck($verifyIdentityResult, $lockExpires)
    {
        $password = 'qwerty1';
        $userName = 'John Doe';

        $this->encryptorMock
            ->expects($this->once())
            ->method('validateHash')
            ->with($password, $this->model->getPassword())
            ->willReturn($verifyIdentityResult);
        $this->model->setIsActive(true);
        $this->resourceMock->expects($this->any())->method('hasAssigned2Role')->willReturn(true);

        $this->model->setUserName($userName);
        $this->model->setLockExpires($lockExpires);

        $this->eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->with(
                'admin_user_authenticate_after',
                [
                    'username' => $userName,
                    'password' => $password,
                    'user' => $this->model,
                    'result' => $verifyIdentityResult
                ]
            )
            ->willReturnSelf();

        if ($lockExpires) {
            $this->setExpectedException(
                \Magento\Framework\Exception\State\UserLockedException::class,
                __('Your account is temporarily disabled.')
            );
        }

        if (!$verifyIdentityResult) {
            $this->setExpectedException(
                \Magento\Framework\Exception\AuthenticationException::class,
                __('You have entered an invalid password for current user.')
            );
        }

        $this->model->performIdentityCheck($password);
    }

    /**
     * @return array
     */
    public function dataProviderPerformIdentityCheck()
    {
        return [
            ['verifyIdentityResult' => true, 'lockExpires' => false],
            ['verifyIdentityResult' => false, 'lockExpires' => false],
            ['verifyIdentityResult' => true, 'lockExpires' => true],
            ['verifyIdentityResult' => false, 'lockExpires' => true]
        ];
    }
}
