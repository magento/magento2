<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit\AclResource;

class ProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Acl\AclResource\Provider
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_configReaderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_treeBuilderMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Framework\Acl\Data\CacheInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $aclDataCacheMock;

    protected function setUp(): void
    {
        $this->_configReaderMock = $this->createMock(\Magento\Framework\Config\ReaderInterface::class);
        $this->_treeBuilderMock = $this->createMock(\Magento\Framework\Acl\AclResource\TreeBuilder::class);
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

        $this->_model = new \Magento\Framework\Acl\AclResource\Provider(
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
            \Magento\Framework\Acl\AclResource\Provider::ACL_RESOURCES_CACHE_KEY
        );
        $this->assertEquals('ExpectedResult', $this->_model->getAclResources());
    }

    public function testGetIfAclResourcesExistInCache()
    {
        $this->_configReaderMock->expects($this->never())->method('read');
        $this->_treeBuilderMock->expects($this->never())->method('build');
        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(\Magento\Framework\Acl\AclResource\Provider::ACL_RESOURCES_CACHE_KEY)
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
