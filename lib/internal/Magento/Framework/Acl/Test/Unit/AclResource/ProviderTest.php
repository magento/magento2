<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Acl\Test\Unit\AclResource;

use Magento\Framework\Acl\AclResource\Provider;
use Magento\Framework\Acl\AclResource\TreeBuilder;
use Magento\Framework\Acl\Data\CacheInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProviderTest extends TestCase
{
    /**
     * @var Provider
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_configReaderMock;

    /**
     * @var MockObject
     */
    protected $_treeBuilderMock;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $aclDataCacheMock;

    protected function setUp(): void
    {
        $this->_configReaderMock = $this->getMockForAbstractClass(ReaderInterface::class);
        $this->_treeBuilderMock = $this->createMock(TreeBuilder::class);
        $this->serializerMock = $this->createPartialMock(
            Json::class,
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

        $this->aclDataCacheMock = $this->getMockForAbstractClass(CacheInterface::class);

        $this->_model = new Provider(
            $this->_configReaderMock,
            $this->_treeBuilderMock,
            $this->aclDataCacheMock,
            $this->serializerMock
        );
    }

    public function testGetIfAclResourcesExist()
    {
        $aclResourceConfig['config']['acl']['resources'] = ['ExpectedValue'];
        $this->_configReaderMock->expects($this->once())->method('read')->willReturn($aclResourceConfig);
        $this->_treeBuilderMock->expects($this->once())->method('build')->willReturn('ExpectedResult');
        $this->aclDataCacheMock->expects($this->once())->method('save')->with(
            json_encode('ExpectedResult'),
            Provider::ACL_RESOURCES_CACHE_KEY
        );
        $this->assertEquals('ExpectedResult', $this->_model->getAclResources());
    }

    public function testGetIfAclResourcesExistInCache()
    {
        $this->_configReaderMock->expects($this->never())->method('read');
        $this->_treeBuilderMock->expects($this->never())->method('build');
        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(Provider::ACL_RESOURCES_CACHE_KEY)
            ->willReturn(json_encode('ExpectedResult'));
        $this->assertEquals('ExpectedResult', $this->_model->getAclResources());
    }

    public function testGetIfAclResourcesEmpty()
    {
        $this->_configReaderMock->expects($this->once())->method('read')->willReturn([]);
        $this->_treeBuilderMock->expects($this->never())->method('build');
        $this->assertEquals([], $this->_model->getAclResources());
    }
}
