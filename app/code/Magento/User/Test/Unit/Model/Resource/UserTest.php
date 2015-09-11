<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Unit\Model\Resource;

/**
 * Test class for \Magento\User\Model\Resource\User testing
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\User\Model\Resource\User */
    protected $model;

    /** @var \Magento\User\Model\User|\PHPUnit_framework_MockObject_MockObject */
    protected $userMock;

    /** @var \Magento\Framework\Acl\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $aclCacheMock;

    /** @var \Magento\Framework\Model\Resource\Db\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Authorization\Model\RoleFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $roleFactoryMock;

    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTimeMock;

    /** @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;

    /** @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $dbAdapterMock;

    /** @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject */
    protected $selectMock;

    /** @var \Magento\Authorization\Model\Role|\PHPUnit_Framework_MockObject_MockObject */
    protected $roleMock;

    protected function setUp()
    {
        $this->userMock = $this->getMockBuilder('Magento\User\Model\User')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->resourceMock = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->aclCacheMock = $this->getMockBuilder('Magento\Framework\Acl\CacheInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->roleFactoryMock = $this->getMockBuilder('Magento\Authorization\Model\RoleFactory')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->roleMock = $this->getMockBuilder('Magento\Authorization\Model\Role')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();


        $this->dateTimeMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->selectMock = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->dbAdapterMock = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            'Magento\User\Model\Resource\User',
            [
                'resource' => $this->resourceMock,
                'aclCache' => $this->aclCacheMock,
                'roleFactory' => $this->roleFactoryMock,
                'dateTime' => $this->dateTimeMock
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

        $this->assertInstanceOf('Magento\User\Model\Resource\User', $this->model->recordLogin($this->userMock));
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
        $this->resourceMock->expects($this->once())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->selectMock->expects($this->once())->method('from')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('select')->willReturn($this->selectMock);
        $this->selectMock->expects($this->atLeastOnce())->method('where')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('fetchAll')->willReturn([1, 2, 3]);

        $this->assertEquals([1, 2, 3], $this->model->hasAssigned2Role(12345));
        $this->assertNull($this->model->hasAssigned2Role(0));
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
        $this->selectMock->expects($this->once())->method('where')->willReturn($this->selectMock);
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
        $this->selectMock->expects($this->once())->method('where')->willReturn($this->selectMock);
        $this->dbAdapterMock->expects($this->once())->method('fetchCol')->willReturn(false);
        $this->assertEquals([], $this->model->getRoles($this->userMock));
    }

    public function testSaveExtraEmptyId()
    {
        $this->resourceMock->expects($this->never())->method('getConnection');
        $this->assertInstanceOf(
            'Magento\User\Model\Resource\User',
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
            'Magento\User\Model\Resource\User',
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
        $returnData = 5;
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('update')->willReturn($returnData);
        $this->assertEquals($returnData, $this->model->unlock(['user1', 'user2']));
    }

    public function testLock()
    {
        $returnData = 5;
        $this->resourceMock->expects($this->atLeastOnce())->method('getConnection')->willReturn($this->dbAdapterMock);
        $this->dbAdapterMock->expects($this->once())->method('update')->willReturn($returnData);
        $this->assertEquals($returnData, $this->model->lock(['user1', 'user2'], 1, 1));
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
}
