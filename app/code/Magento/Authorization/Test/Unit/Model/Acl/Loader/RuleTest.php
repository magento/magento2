<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Unit\Model\Acl\Loader;

use Magento\Authorization\Model\Acl\Loader\Rule;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Data\CacheInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Framework\Acl\Role\CurrentRoleContext;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Authorization\Model\Acl\Loader\Rule
 */
class RuleTest extends TestCase
{
    /**
     * @var Rule
     */
    private $model;

    /**
     * @var RootResource
     */
    private $rootResource;

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
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->rootResource = new RootResource('Magento_Backend::all');
        $this->resourceMock = $this->getMockBuilder(ResourceConnection::class)
            ->onlyMethods(['getConnection', 'getTableName'])
            ->addMethods(['getTable'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Rule::class,
            [
                'rootResource' => $this->rootResource,
                'resource' => $this->resourceMock,
                'aclDataCache' => $this->aclDataCacheMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test populating acl rule from cache.
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testPopulateAclFromCache(): void
    {
        $rules = [
            ['role_id' => 1, 'resource_id' => 'Magento_Backend::all', 'permission' => 'allow'],
            ['role_id' => 2, 'resource_id' => 1, 'permission' => 'allow'],
            ['role_id' => 3, 'resource_id' => 1, 'permission' => 'deny']
        ];
        $this->resourceMock->expects($this->never())->method('getTable');
        $this->resourceMock->expects($this->never())
            ->method('getConnection');

        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(Rule::ACL_RULE_CACHE_KEY)
            ->willReturn(
                json_encode($rules)
            );

        $aclMock = $this->createMock(Acl::class);
        $aclMock->method('hasResource')->willReturn(true);
        $aclMock
            ->method('allow')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 == '1' && $arg2 === null && $arg3 === null) {
                    return null;
                } elseif ($arg1 == '1' && $arg2 == 'Magento_Backend::all' && $arg3 === null) {
                    return null;
                } elseif ($arg1 == '2' && $arg2 == 1 && $arg3 === null) {
                    return null;
                }
            });

        $aclMock
            ->method('deny')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 == '3' && $arg2 == 1 && is_null($arg3)) {
                    return null;
                }
            });

        $aclMock
            ->method('getResources')
            ->willReturn([
                'Magento_Backend::all',
                'Magento_Backend::admin',
                'Vendor_MyModule::menu',
                'Vendor_MyModule::index'
            ]);

        $this->model->populateAcl($aclMock);
    }

    /**
     * Ensure that when a role context is present, rules are loaded from the role-specific cache key
     * and applied accordingly.
     */
    public function testPopulateAclForSpecificRoleFromCache(): void
    {
        $roleId = 10;
        $rules = [
            ['role_id' => $roleId, 'resource_id' => 'Magento_Backend::all', 'permission' => 'allow'],
            ['role_id' => $roleId, 'resource_id' => 'Magento_Backend::admin', 'permission' => 'allow'],
        ];

        $roleContext = $this->createMock(CurrentRoleContext::class);
        $roleContext->method('getRoleId')->willReturn($roleId);

        // Expect the role-specific cache key to be read
        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(hash('sha256', Rule::ACL_RULE_CACHE_KEY . '_' . $roleId))
            ->willReturn(json_encode($rules));

        // ACL expectations: allow for root, then for specific resource
        $aclMock = $this->createMock(Acl::class);
        $aclMock->method('hasResource')->willReturn(true);
        $calls = [];
        $aclMock->method('allow')
            ->willReturnCallback(function ($role, $resource, $privilege) use (&$calls) {
                $calls[] = [$role, $resource, $privilege];
                return null;
            });

        $connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock->method('fetchRow')->willReturn([]); // Return empty array for any DB fetchRow() call

        $selectMock = $this->getMockBuilder('stdClass')
            ->addMethods(['from', 'where', 'limit'])
            ->getMock();
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $selectMock->method('limit')->willReturnSelf();
        $connectionMock->method('select')->willReturn($selectMock);
        $this->resourceMock->method('getConnection')->willReturn($connectionMock);
        $this->resourceMock->method('getTableName')->willReturn('authorization_role'); // Return dummy table name

        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(
            Rule::class,
            [
                'rootResource' => $this->rootResource,
                'resource' => $this->resourceMock,
                'aclDataCache' => $this->aclDataCacheMock,
                'serializer' => $this->serializerMock,
                'roleContext' => $roleContext,
            ]
        );

        $model->populateAcl($aclMock);

        $foundRootResourceAllow = false;
        $foundAdminResourceAllow = false;
        foreach ($calls as $call) {
            [$role, $resource, $privilege] = $call;
            if ($privilege === null && (int)$role === $roleId) {
                if ($resource === 'Magento_Backend::all') {
                    $foundRootResourceAllow = true;
                }
                if ($resource === 'Magento_Backend::admin') {
                    $foundAdminResourceAllow = true;
                }
            }
        }
        $this->assertTrue($foundRootResourceAllow, 'Expected allow() call for Magento_Backend::all with given role');
        $this->assertTrue($foundAdminResourceAllow, 'Expected allow() call for Magento_Backend::admin with given role');
    }
}
