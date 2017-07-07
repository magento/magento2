<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Test\Unit\Model\Acl\Loader;

class RoleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorization\Model\Acl\Loader\Role
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_roleFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_groupFactoryMock;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $aclDataCacheMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;

    protected function setUp()
    {
        $this->_resourceMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [],
            [],
            '',
            false,
            false
        );
        $this->_groupFactoryMock = $this->getMock(
            \Magento\Authorization\Model\Acl\Role\GroupFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->_roleFactoryMock = $this->getMock(
            \Magento\Authorization\Model\Acl\Role\UserFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $this->selectMock->expects($this->any())
            ->method('from')
            ->will($this->returnValue($this->selectMock));

        $this->_adapterMock = $this->getMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class, [], [], '', false);

        $this->serializerMock = $this->getMock(
            \Magento\Framework\Serialize\Serializer\Json::class,
            ['serialize', 'unserialize'],
            [],
            '',
            false
        );
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );

        $this->serializerMock->expects($this->any())
            ->method('unserialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_decode($value, true);
                    }
                )
            );

        $this->aclDataCacheMock = $this->getMock(
            \Magento\Framework\Acl\Data\CacheInterface::class,
            [],
            [],
            '',
            false
        );

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
            ->will($this->returnArgument(1));

        $this->_adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->selectMock));

        $this->_resourceMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->_adapterMock));

        $this->_adapterMock->expects($this->once())
            ->method('fetchAll')
            ->will(
                $this->returnValue(
                    [
                        ['role_id' => 1, 'role_type' => 'G', 'parent_id' => null],
                        ['role_id' => 2, 'role_type' => 'U', 'parent_id' => 1, 'user_id' => 1],
                    ]
                )
            );

        $this->_groupFactoryMock->expects($this->once())->method('create')->with(['roleId' => '1']);
        $this->_roleFactoryMock->expects($this->once())->method('create')->with(['roleId' => '2']);

        $aclMock = $this->getMock(\Magento\Framework\Acl::class);
        $aclMock->expects($this->at(0))->method('addRole')->with($this->anything(), null);
        $aclMock->expects($this->at(2))->method('addRole')->with($this->anything(), '1');

        $this->_model->populateAcl($aclMock);
    }

    public function testPopulateAclAddsMultipleParents()
    {
        $this->_resourceMock->expects($this->once())
            ->method('getTableName')
            ->with($this->equalTo('authorization_role'))
            ->will($this->returnArgument(1));

        $this->_adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($this->selectMock));

        $this->_resourceMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($this->_adapterMock));

        $this->_adapterMock->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue([['role_id' => 1, 'role_type' => 'U', 'parent_id' => 2, 'user_id' => 3]]));

        $this->_roleFactoryMock->expects($this->never())->method('getModelInstance');
        $this->_groupFactoryMock->expects($this->never())->method('getModelInstance');

        $aclMock = $this->getMock(\Magento\Framework\Acl::class);
        $aclMock->expects($this->at(0))->method('hasRole')->with('1')->will($this->returnValue(true));
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
            ->will(
                $this->returnValue(
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
                )
            );

        $this->_roleFactoryMock->expects($this->never())->method('getModelInstance');
        $this->_groupFactoryMock->expects($this->never())->method('getModelInstance');

        $aclMock = $this->getMock(\Magento\Framework\Acl::class);
        $aclMock->expects($this->at(0))->method('hasRole')->with('1')->will($this->returnValue(true));
        $aclMock->expects($this->at(1))->method('addRoleParent')->with('1', '2');

        $this->_model->populateAcl($aclMock);
    }
}
