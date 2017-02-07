<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Model\ResourceModel;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Test class for \Magento\User\Model\ResourceModel\User testing
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\User\Model\ResourceModel\User */
    protected $model;

    /** @var \Magento\User\Model\User|\PHPUnit_framework_MockObject_MockObject */
    protected $userMock;

    /** @var \Magento\Framework\Acl\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $aclCacheMock;

    /** @var \Magento\Framework\Model\ResourceModel\Db\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Authorization\Model\RoleFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $roleFactoryMock;

    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTimeMock;

    /** @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $dbAdapterMock;

    /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject */
    protected $selectMock;

    /** @var \Magento\Authorization\Model\Role|\PHPUnit_Framework_MockObject_MockObject */
    protected $roleMock;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aclDataCacheMock;

    protected function setUp()
    {
        $this->userMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->resourceMock = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->aclCacheMock = $this->getMockBuilder(\Magento\Framework\Acl\CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->roleFactoryMock = $this->getMockBuilder(\Magento\Authorization\Model\RoleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->roleMock = $this->getMockBuilder(\Magento\Authorization\Model\Role::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->dbAdapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->aclDataCacheMock = $this->getMockBuilder(\Magento\Framework\Acl\Data\CacheInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\User\Model\ResourceModel\User::class,
            [
                'resource' => $this->resourceMock,
                'aclCache' => $this->aclCacheMock,
                'roleFactory' => $this->roleFactoryMock,
                'dateTime' => $this->dateTimeMock,
                'aclDataCache' => $this->aclDataCacheMock,
            ]
        );
    }

    public function testIsUserUnique()
    {
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('from')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('fetchRow')->willReturn([true]);

        $this->assertFalse($this->model->isUserUnique($this->userMock));
    }

    public function testRecordLogin()
    {
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('update');

        $this->assertInstanceOf(
            \Magento\User\Model\ResourceModel\User::class,
            $this->model->recordLogin($this->userMock)
        );
    }

    public function testLoadByUsername()
    {
        $returnData = [1, 2, 3];
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('from')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('fetchRow')->willReturn($returnData);

        $this->assertEquals($returnData, $this->model->loadByUsername('user1'));
    }

    public function testHasAssigned2Role()
    {
        $returnData = [1, 2, 3];
        $uid = 1234;
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->selectMock->expects($this->once())->method('from')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('fetchAll')->willReturn($returnData);

        $this->assertEquals($returnData, $this->model->hasAssigned2Role($uid));
        $this->assertNull($this->model->hasAssigned2Role(0));
    }

    public function testHasAssigned2RolePassAnObject()
    {
        $methodUserMock = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId'])
            ->getMock();
        $returnData = [1, 2, 3];
        $uid = 1234;
        $methodUserMock->expects($this->once())->method('getUserId')->willReturn($uid);
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('from')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('fetchAll')->willReturn($returnData);

        $this->assertEquals($returnData, $this->model->hasAssigned2Role($methodUserMock));
    }

    public function testClearUserRoles()
    {
        $uid = 123;
        $this->userMock->expects($this->once())->method('getId')->willReturn($uid);
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('delete');
        $this->model->_clearUserRoles($this->userMock);
    }

    public function testDeleteSucess()
    {
        $uid = 123;
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('beginTransaction');
        $this->userMock->expects($this->once())->method('getId')->willReturn($uid);
        $this->dbAdapterMock->expects($this->atLeastOnce())->method('delete');

        $this->assertTrue($this->model->delete($this->userMock));
    }

    public function testGetRolesEmptyId()
    {
        $this->assertEquals([], $this->model->getRoles($this->userMock));
    }

    public function testGetRolesReturnFilledArray()
    {
        $uid = 123;
        $this->userMock->expects($this->atLeastOnce())->method('getId')->willReturn($uid);
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('from')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('joinLeft')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('fetchCol')->willReturn([1, 2, 3]);
        $this->assertEquals([1, 2, 3], $this->model->getRoles($this->userMock));
    }

    public function testGetRolesFetchRowFailure()
    {
        $uid = 123;
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->userMock->expects($this->atLeastOnce())->method('getId')->willReturn($uid);
        $this->dbAdapterMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('from')->willReturn($this->selectMock);
        $this->selectMock->expects($this->once())->method('joinLeft')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('fetchCol')->willReturn(false);
        $this->assertEquals([], $this->model->getRoles($this->userMock));
    }

    public function testSaveExtraEmptyId()
    {
        $this->resourceMock->expects($this->never())->method('getConnection');
        $this->assertInstanceOf(
            \Magento\User\Model\ResourceModel\User::class,
            $this->model->saveExtra($this->userMock, [1, 2, 3])
        );
    }

    public function testSaveExtraFilledId()
    {
        $uid = 123;
        $this->userMock->expects($this->atLeastOnce())->method('getId')->willReturn($uid);
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('update');
        $this->assertInstanceOf(
            \Magento\User\Model\ResourceModel\User::class,
            $this->model->saveExtra($this->userMock, [1, 2, 3])
        );
    }

    public function testCountAll()
    {
        $returnData = 123;
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('fetchOne')->willReturn($returnData);
        $this->assertEquals($returnData, $this->model->countAll());
    }

    public function testUpdateRoleUsersAclWithUsers()
    {
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->roleMock->expects($this->once())->method('getRoleUsers')->willReturn(['user1', 'user2']);
        $this->dbAdapterMock->expects($this->once())->method('update')->willReturn(1);
        $this->assertTrue($this->model->updateRoleUsersAcl($this->roleMock));
    }

    public function testUpdateRoleUsersAclNoUsers()
    {
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->roleMock->expects($this->once())->method('getRoleUsers')->willReturn([]);
        $this->dbAdapterMock->expects($this->never())->method('update');
        $this->assertFalse($this->model->updateRoleUsersAcl($this->roleMock));
    }

    public function testUpdateRoleUsersAclUpdateFail()
    {
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->roleMock->expects($this->once())->method('getRoleUsers')->willReturn(['user1', 'user2']);
        $this->dbAdapterMock->expects($this->once())->method('update')->willReturn(0);
        $this->assertFalse($this->model->updateRoleUsersAcl($this->roleMock));
    }

    public function testUnlock()
    {
        $inputData = [1, 2, 3];
        $returnData = 5;
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('update')->willReturn($returnData);
        $this->assertEquals($returnData, $this->model->unlock($inputData));
    }

    public function testUnlockWithInteger()
    {
        $inputData = 123;
        $returnData = 5;
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('update')->willReturn($returnData);
        $this->assertEquals($returnData, $this->model->unlock($inputData));
    }

    public function testLock()
    {
        $inputData = [1, 2, 3];
        $returnData = 5;
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('update')->willReturn($returnData);
        $this->assertEquals($returnData, $this->model->lock($inputData, 1, 1));
    }

    public function testLockWithInteger()
    {
        $inputData = 123;
        $returnData = 5;
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('update')->willReturn($returnData);
        $this->assertEquals($returnData, $this->model->lock($inputData, 1, 1));
    }

    public function testGetOldPassword()
    {
        $returnData = ['password1', 'password2'];
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->atLeastOnce())->method('select')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('from')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('order')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->atLeastOnce())->method('fetchCol')->willReturn($returnData);
        $this->assertEquals($returnData, $this->model->getOldPasswords($this->userMock));
    }

    public function testDeleteFromRole()
    {
        $methodUserMock = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId', 'getRoleId'])
            ->getMock();
        $uid = 1234;
        $roleId = 44;
        $methodUserMock->expects($this->once())->method('getUserId')->willReturn($uid);
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $methodUserMock->expects($this->atleastOnce())->method('getRoleId')->willReturn($roleId);
        $this->dbAdapterMock->expects($this->once())->method('delete');

        $this->assertInstanceOf(
            \Magento\User\Model\ResourceModel\User::class,
            $this->model->deleteFromRole($methodUserMock)
        );
    }

    public function testRoleUserExists()
    {
        $methodUserMock = $this->getMockBuilder(\Magento\Framework\Model\AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUserId', 'getRoleId'])
            ->getMock();
        $uid = 1234;
        $roleId = 44;
        $returnData = [1, 2, 3];
        $methodUserMock->expects($this->atLeastOnce())->method('getUserId')->willReturn($uid);
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $methodUserMock->expects($this->once())->method('getRoleId')->willReturn($roleId);
        $this->dbAdapterMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('from')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('fetchCol')->willReturn($returnData);

        $this->assertEquals($returnData, $this->model->roleUserExists($methodUserMock));
        $this->assertEquals([], $this->model->roleUserExists($this->userMock));
    }

    public function testGetValidationBeforeSave()
    {
        $this->assertInstanceOf('\Zend_Validate_Callback', $this->model->getValidationRulesBeforeSave());
    }

    public function testUpdateFailure()
    {
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('update')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('quoteInto')->willReturn($this->selectMock);
        $this->model->updateFailure($this->userMock, 1, 1);
    }

    public function testTrackPassword()
    {
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('insert')->willReturn($this->selectMock);
        $this->model->trackPassword($this->userMock, "myPas#w0rd", 1);
    }

    public function testGetLatestPassword()
    {
        $uid = 123;
        $returnData = ['password1', 'password2'];
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('fetchRow')->willReturn($returnData);
        $this->dbAdapterMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('from')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('order')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('limit')->willReturn($this->selectMock);
        $this->assertEquals($returnData, $this->model->getLatestPassword($uid));
    }

    public function testInitUniqueFields()
    {
        $this->assertInstanceOf(
            \Magento\User\Model\ResourceModel\User::class,
            $this->invokeMethod($this->model, '_initUniqueFields', [])
        );
    }

    public function testAfterSave()
    {
        $roleId = 123;
        $methodUserMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasRoleId', 'getRoleId', 'getExtra', 'setExtra'])
            ->getMock();
        $methodUserMock->expects($this->once())->method('hasRoleId')->willReturn(true);
        $methodUserMock->expects($this->once())->method('getRoleId')->willReturn($roleId);
        $extraData = ['user', 'extra', 'data'];

        $serializerMock = $this->getMock(Json::class, ['serialize', 'unserialize'], [], '', false);
        $serializerMock->expects($this->once())
            ->method('unserialize')
            ->with(json_encode($extraData))
            ->will($this->returnValue($extraData));

        $methodUserMock->expects($this->once())
            ->method('getExtra')
            ->will($this->returnValue(json_encode($extraData)));

        $methodUserMock->expects($this->once())
            ->method('setExtra')
            ->with($extraData);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $objectManager->setBackwardCompatibleProperty($this->model, 'serializer', $serializerMock);

        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->roleFactoryMock->expects($this->once())->method('create')->willReturn($this->roleMock);
        $this->roleMock->expects($this->once())->method('load')->willReturn($this->roleMock);
        $this->roleMock->expects($this->atLeastOnce())->method('getId')->willReturn($roleId);
        $this->dbAdapterMock->expects($this->once())->method('describeTable')->willReturn([1, 2, 3]);
        $this->aclDataCacheMock->expects($this->once())->method('clean');
        $this->assertInstanceOf(
            \Magento\User\Model\ResourceModel\User::class,
            $this->invokeMethod($this->model, '_afterSave', [$methodUserMock])
        );
    }

    public function testAfterLoad()
    {
        $methodUserMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtra', 'setExtra'])
            ->getMock();
        $extraData = ['user', 'extra', 'data'];

        $serializerMock = $this->getMock(Json::class, ['serialize', 'unserialize'], [], '', false);
        $serializerMock->expects($this->once())
            ->method('unserialize')
            ->with(json_encode($extraData))
            ->will($this->returnValue($extraData));

        $methodUserMock->expects($this->exactly(2))
            ->method('getExtra')
            ->will($this->returnValue(json_encode($extraData)));

        $methodUserMock->expects($this->once())
            ->method('setExtra')
            ->with($extraData);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $objectManager->setBackwardCompatibleProperty($this->model, 'serializer', $serializerMock);

        $this->assertInstanceOf(
            \Magento\User\Model\ResourceModel\User::class,
            $this->invokeMethod($this->model, '_afterLoad', [$methodUserMock])
        );
    }

    public function testAfterLoadNoExtra()
    {
        $methodUserMock = $this->getMockBuilder(\Magento\User\Model\User::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtra', 'setExtra'])
            ->getMock();
        $extraData = null;

        $serializerMock = $this->getMock(Json::class, ['serialize', 'unserialize'], [], '', false);
        $serializerMock->expects($this->never())
            ->method('unserialize');

        $methodUserMock->expects($this->exactly(1))
            ->method('getExtra')
            ->will($this->returnValue($extraData));

        $methodUserMock->expects($this->never())
            ->method('setExtra');

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $objectManager->setBackwardCompatibleProperty($this->model, 'serializer', $serializerMock);

        $this->assertInstanceOf(
            \Magento\User\Model\ResourceModel\User::class,
            $this->invokeMethod($this->model, '_afterLoad', [$methodUserMock])
        );
    }

    /**
     * Call protected/private method of a class.
     *
     * @param $object
     * @param $methodName
     * @param array $parameters
     * @return mixed
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
