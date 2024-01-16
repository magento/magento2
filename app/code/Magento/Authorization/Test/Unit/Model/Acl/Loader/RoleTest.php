<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Unit\Model\Acl\Loader;

use Magento\Authorization\Model\Acl\Loader\Role;
use Magento\Authorization\Model\Acl\Role\GroupFactory;
use Magento\Authorization\Model\Acl\Role\UserFactory;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Data\CacheInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Authorization\Model\Acl\Loader\Role
 */
class RoleTest extends TestCase
{
    /**
     * @var Role
     */
    private $model;

    /**
     * @var GroupFactory|MockObject
     */
    private $groupFactoryMock;

    /**
     * @var UserFactory|MockObject
     */
    private $roleFactoryMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $aclDataCacheMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var Mysql|MockObject
     */
    private $adapterMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->groupFactoryMock = $this->getMockBuilder(GroupFactory::class)
            ->onlyMethods(['create'])
            ->addMethods(['getModelInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->roleFactoryMock = $this->getMockBuilder(UserFactory::class)
            ->onlyMethods(['create'])
            ->addMethods(['getModelInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->aclDataCacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->serializerMock = $this->createPartialMock(
            Json::class,
            ['serialize', 'unserialize']
        );

        $this->serializerMock->method('serialize')
            ->willReturnCallback(
                static function ($value) {
                    return json_encode($value);
                }
            );

        $this->serializerMock->method('unserialize')
            ->willReturnCallback(
                static function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->selectMock = $this->createMock(Select::class);
        $this->selectMock->method('from')
            ->willReturn($this->selectMock);

        $this->adapterMock = $this->createMock(Mysql::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Role::class,
            [
                'groupFactory' => $this->groupFactoryMock,
                'roleFactory' => $this->roleFactoryMock,
                'resource' => $this->resourceMock,
                'aclDataCache' => $this->aclDataCacheMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test populating acl roles with children.
     *
     * @return void
     */
    public function testPopulateAclAddsRolesAndTheirChildren(): void
    {
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->with('authorization_role')
            ->willReturnArgument(1);

        $this->adapterMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->adapterMock);

        $this->adapterMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn(
                [
                    ['role_id' => 1, 'role_type' => 'G', 'parent_id' => null],
                    ['role_id' => 2, 'role_type' => 'U', 'parent_id' => 1, 'user_id' => 1]
                ]
            );

        $this->groupFactoryMock->expects($this->once())->method('create')->with(['roleId' => '1']);
        $this->roleFactoryMock->expects($this->once())->method('create')->with(['roleId' => '2']);

        $aclMock = $this->createMock(Acl::class);
        $aclMock
            ->method('addRole')
            ->willReturnCallback(function (...$args) {
                static $index = 0;
                $expectedArgs = [
                    [$this->anything(), null],
                    [$this->anything(), '1']
                ];
                $returnValue = null;
                $index++;
                return $args === $expectedArgs[$index - 1] ? $returnValue : null;
            });

        $this->model->populateAcl($aclMock);
    }

    /**
     * Test populating acl role with multiple parents.
     *
     * @return void
     */
    public function testPopulateAclAddsMultipleParents(): void
    {
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->with('authorization_role')
            ->willReturnArgument(1);

        $this->adapterMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->adapterMock);

        $this->adapterMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['role_id' => 1, 'role_type' => 'U', 'parent_id' => 2, 'user_id' => 3]]);

        $this->roleFactoryMock->expects($this->never())->method('getModelInstance');
        $this->groupFactoryMock->expects($this->never())->method('getModelInstance');

        $aclMock = $this->createMock(Acl::class);
        $aclMock
            ->method('hasRole')
            ->with('1')
            ->willReturn(true);
        $aclMock
            ->method('addRoleParent')
            ->with('1', '2');

        $this->model->populateAcl($aclMock);
    }

    /**
     * Test populating acl role from cache.
     *
     * @return void
     */
    public function testPopulateAclFromCache(): void
    {
        $this->resourceMock->expects($this->never())->method('getConnection');
        $this->resourceMock->expects($this->never())->method('getTableName');
        $this->adapterMock->expects($this->never())->method('fetchAll');
        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(Role::ACL_ROLES_CACHE_KEY)
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

        $this->roleFactoryMock->expects($this->never())->method('getModelInstance');
        $this->groupFactoryMock->expects($this->never())->method('getModelInstance');

        $aclMock = $this->createMock(Acl::class);
        $aclMock
            ->method('hasRole')
            ->with('1')
            ->willReturn(true);
        $aclMock
            ->method('addRoleParent')
            ->with('1', '2');

        $this->model->populateAcl($aclMock);
    }
}
