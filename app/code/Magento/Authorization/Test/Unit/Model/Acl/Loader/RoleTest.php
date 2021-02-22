<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Test\Unit\Model\Acl\Loader;

class RoleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\Acl\Loader\Role
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_resourceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_adapterMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_roleFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_groupFactoryMock;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $aclDataCacheMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    private $selectMock;

    protected function setUp(): void
    {
        $this->_resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->_groupFactoryMock = $this->getMockBuilder(\Magento\Authorization\Model\Acl\Role\GroupFactory::class)
            ->setMethods(['create', 'getModelInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_roleFactoryMock = $this->getMockBuilder(\Magento\Authorization\Model\Acl\Role\UserFactory::class)
            ->setMethods(['create', 'getModelInstance'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->selectMock->expects($this->any())
            ->method('from')
            ->willReturn($this->selectMock);

        $this->_adapterMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);

        $this->serializerMock = $this->createPartialMock(
            \Magento\Framework\Serialize\Serializer\Json::class,
            ['serialize', 'unserialize']
        );
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->aclDataCacheMock = $this->createMock(\Magento\Framework\Acl\Data\CacheInterface::class);

        $this->_model = new \Magento\Authorization\Model\Acl\Loader\Role(
            $this->_groupFactoryMock,
            $this->_roleFactoryMock,
            $this->_resourceMock,
            $this->aclDataCacheMock,
            $this->serializerMock
        );
    }

    public function testPopulateAclAddsRolesAndTheirChildren()
    {
        $this->_resourceMock->expects($this->once())
            ->method('getTableName')
            ->with($this->equalTo('authorization_role'))
            ->willReturnArgument(1);

        $this->_adapterMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->_resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->_adapterMock);

        $this->_adapterMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn(
                [
                    ['role_id' => 1, 'role_type' => 'G', 'parent_id' => null],
                    ['role_id' => 2, 'role_type' => 'U', 'parent_id' => 1, 'user_id' => 1],
                ]
            );

        $this->_groupFactoryMock->expects($this->once())->method('create')->with(['roleId' => '1']);
        $this->_roleFactoryMock->expects($this->once())->method('create')->with(['roleId' => '2']);

        $aclMock = $this->createMock(\Magento\Framework\Acl::class);
        $aclMock->expects($this->at(0))->method('addRole')->with($this->anything(), null);
        $aclMock->expects($this->at(2))->method('addRole')->with($this->anything(), '1');

        $this->_model->populateAcl($aclMock);
    }

    public function testPopulateAclAddsMultipleParents()
    {
        $this->_resourceMock->expects($this->once())
            ->method('getTableName')
            ->with($this->equalTo('authorization_role'))
            ->willReturnArgument(1);

        $this->_adapterMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->_resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->_adapterMock);

        $this->_adapterMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['role_id' => 1, 'role_type' => 'U', 'parent_id' => 2, 'user_id' => 3]]);

        $this->_roleFactoryMock->expects($this->never())->method('getModelInstance');
        $this->_groupFactoryMock->expects($this->never())->method('getModelInstance');

        $aclMock = $this->createMock(\Magento\Framework\Acl::class);
        $aclMock->expects($this->at(0))->method('hasRole')->with('1')->willReturn(true);
        $aclMock->expects($this->at(1))->method('addRoleParent')->with('1', '2');

        $this->_model->populateAcl($aclMock);
    }

    public function testPopulateAclFromCache()
    {
        $this->_resourceMock->expects($this->never())->method('getConnection');
        $this->_resourceMock->expects($this->never())->method('getTableName');
        $this->_adapterMock->expects($this->never())->method('fetchAll');
        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(\Magento\Authorization\Model\Acl\Loader\Role::ACL_ROLES_CACHE_KEY)
            ->willReturn(
                json_encode(
                    [
                        [
                            'role_id' => 1,
                            'role_type' => 'U',
                            'parent_id' => 2,
                            'user_id' => 3
                        ]
                    ]
                )
            );

        $this->_roleFactoryMock->expects($this->never())->method('getModelInstance');
        $this->_groupFactoryMock->expects($this->never())->method('getModelInstance');

        $aclMock = $this->createMock(\Magento\Framework\Acl::class);
        $aclMock->expects($this->at(0))->method('hasRole')->with('1')->willReturn(true);
        $aclMock->expects($this->at(1))->method('addRoleParent')->with('1', '2');

        $this->_model->populateAcl($aclMock);
    }
}
