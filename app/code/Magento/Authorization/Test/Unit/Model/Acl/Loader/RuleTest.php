<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Test\Unit\Model\Acl\Loader;

class RuleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Authorization\Model\Acl\Loader\Rule
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Framework\Acl\RootResource
     */
    protected $_rootResourceMock;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $aclDataCacheMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->_resourceMock = $this->createPartialMock(
            \Magento\Framework\App\ResourceConnection::class,
            ['getTable', 'getConnection']
        );
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

        $this->_rootResourceMock = new \Magento\Framework\Acl\RootResource('Magento_Backend::all');
        $this->_model = new \Magento\Authorization\Model\Acl\Loader\Rule(
            $this->_rootResourceMock,
            $this->_resourceMock,
            [],
            $this->aclDataCacheMock,
            $this->serializerMock
        );
    }

    public function testPopulateAclFromCache()
    {
        $this->_resourceMock->expects($this->never())->method('getTable');
        $this->_resourceMock->expects($this->never())
            ->method('getConnection');

        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(\Magento\Authorization\Model\Acl\Loader\Rule::ACL_RULE_CACHE_KEY)
            ->willReturn(
                json_encode(
                    [
                        ['role_id' => 1, 'resource_id' => 'Magento_Backend::all', 'permission' => 'allow'],
                        ['role_id' => 2, 'resource_id' => 1, 'permission' => 'allow'],
                        ['role_id' => 3, 'resource_id' => 1, 'permission' => 'deny'],
                    ]
                )
            );

        $aclMock = $this->createMock(\Magento\Framework\Acl::class);
        $aclMock->expects($this->any())->method('has')->willReturn(true);
        $aclMock->expects($this->at(1))->method('allow')->with('1', null, null);
        $aclMock->expects($this->at(2))->method('allow')->with('1', 'Magento_Backend::all', null);
        $aclMock->expects($this->at(4))->method('allow')->with('2', 1, null);
        $aclMock->expects($this->at(6))->method('deny')->with('3', 1, null);

        $this->_model->populateAcl($aclMock);
    }
}
