<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Unit\Model\Acl\Loader;

use Magento\Authorization\Model\Acl\Loader\Rule;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Data\CacheInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Authorization\Model\Acl\Loader\Rule
 */
class MissingDeclineRuleTest extends TestCase
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
            ->addMethods(['getTable'])
            ->onlyMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->aclDataCacheMock = $this->createMock(CacheInterface::class);
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

        $this->model = new Rule(
            $this->rootResource,
            $this->resourceMock,
            $this->aclDataCacheMock,
            $this->serializerMock
        );
    }

    /**
     * This test ensures that any new resources, which have not been explicitly defined in the authorization_rule table,
     * are automatically denied for all roles unless explicitly allowed.
     *
     * @return void
     * @throws Exception
     */
    public function testDenyAbsentResources(): void
    {
        // Vendor_MyModule::menu and Vendor_MyModule::report permissions are not present in the authorization_rules
        // table for role 3, and should be denied by default
        $authorizationRulesData = [
            ['rule_id' => 1, 'role_id' => 2, 'resource_id' => 'Magento_Backend::all', 'permission' => 'deny'],
            ['rule_id' => 2, 'role_id' => 2, 'resource_id' => 'Vendor_MyModule::index', 'permission' => 'allow'],
            ['rule_id' => 3, 'role_id' => 2, 'resource_id' => 'Vendor_MyModule::menu', 'permission' => 'deny'],
            ['rule_id' => 4, 'role_id' => 2, 'resource_id' => 'Vendor_MyModule::report', 'permission' => 'deny'],
            ['rule_id' => 5, 'role_id' => 3, 'resource_id' => 'Magento_Backend::all', 'permission' => 'deny'],
            ['rule_id' => 6, 'role_id' => 3, 'resource_id' => 'Vendor_MyModule::index', 'permission' => 'allow'],
        ];

        // Vendor_MyModule::configuration is a new resource that has not been defined in the authorization_rules table
        // for any role, and should be denied by default
        $getAclResourcesData = array_unique(array_column($authorizationRulesData, 'resource_id'));
        $getAclResourcesData[] = 'Vendor_MyModule::configuration';

        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(Rule::ACL_RULE_CACHE_KEY)
            ->willReturn(
                json_encode($authorizationRulesData)
            );

        $aclMock = $this->createMock(Acl::class);
        $aclMock->method('hasResource')->willReturn(true);

        $aclMock
            ->expects($this->exactly(2))
            ->method('allow');

        $aclMock
            ->expects($this->exactly(7))
            ->method('deny');

        $aclMock
            ->method('getResources')
            ->willReturn($getAclResourcesData);

        $this->model->populateAcl($aclMock);
    }
}
