<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorization\Test\Unit\Model\Acl\Loader;

use Magento\Authorization\Model\Acl\Loader\Rule;
use Magento\Framework\Acl;
use Magento\Framework\Acl\Data\CacheInterface;
use Magento\Framework\Acl\RootResource;
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
            ->addMethods(['getTable'])
            ->onlyMethods(['getConnection'])
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
     * Test populating acl rule from cache
     */
    public function testPopulateAclFromCache()
    {
        $this->resourceMock->expects($this->never())->method('getTable');
        $this->resourceMock->expects($this->never())
            ->method('getConnection');

        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(Rule::ACL_RULE_CACHE_KEY)
            ->willReturn(
                json_encode(
                    [
                        ['role_id' => 1, 'resource_id' => 'Magento_Backend::all', 'permission' => 'allow'],
                        ['role_id' => 2, 'resource_id' => 1, 'permission' => 'allow'],
                        ['role_id' => 3, 'resource_id' => 1, 'permission' => 'deny'],
                    ]
                )
            );

        $aclMock = $this->createMock(Acl::class);
        $aclMock->method('has')->willReturn(true);
        $aclMock->expects($this->at(1))->method('allow')->with('1', null, null);
        $aclMock->expects($this->at(2))->method('allow')->with('1', 'Magento_Backend::all', null);
        $aclMock->expects($this->at(4))->method('allow')->with('2', 1, null);
        $aclMock->expects($this->at(6))->method('deny')->with('3', 1, null);

        $this->model->populateAcl($aclMock);
    }
}
