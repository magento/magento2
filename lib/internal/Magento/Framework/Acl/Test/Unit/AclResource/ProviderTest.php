<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Acl\Test\Unit\AclResource;

use Magento\Framework\Acl\AclResource\Provider;
use Magento\Framework\Acl\AclResource\TreeBuilder;
use Magento\Framework\Acl\Data\CacheInterface;
use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
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

    /**
     * @var ConfigWriterInterface|MockObject
     */
    private $configWriterMock;

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
        $this->configWriterMock = $this->getMockForAbstractClass(ConfigWriterInterface::class);

        $this->_model = $this->getMockBuilder(Provider::class)
            ->setConstructorArgs([
                $this->_configReaderMock,
                $this->_treeBuilderMock,
                $this->aclDataCacheMock,
                $this->serializerMock,
                Provider::ACL_RESOURCES_CACHE_KEY,
                $this->configWriterMock,
            ])
            ->onlyMethods(['isCompiledConfigAvailable'])
            ->getMock();

        $this->_model->expects($this->any())
            ->method('isCompiledConfigAvailable')
            ->willReturn(false);
    }

    public function testGetIfAclResourcesExist()
    {
        $expectedTree = [['id' => 'Magento_Backend::admin', 'children' => []]];
        $aclResourceConfig['config']['acl']['resources'] = ['ExpectedValue'];
        $this->_configReaderMock->expects($this->once())->method('read')->willReturn($aclResourceConfig);
        $this->_treeBuilderMock->expects($this->once())->method('build')->willReturn($expectedTree);
        $this->aclDataCacheMock->expects($this->once())->method('save')->with(
            json_encode($expectedTree),
            Provider::ACL_RESOURCES_CACHE_KEY
        );
        $this->configWriterMock->expects($this->once())->method('write')->with(
            Provider::ACL_RESOURCES_CACHE_KEY,
            $expectedTree
        );
        $this->assertEquals($expectedTree, $this->_model->getAclResources());
    }

    public function testGetIfAclResourcesExistInCache()
    {
        $expectedTree = [['id' => 'Magento_Backend::admin', 'children' => []]];
        $this->_configReaderMock->expects($this->never())->method('read');
        $this->_treeBuilderMock->expects($this->never())->method('build');
        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(Provider::ACL_RESOURCES_CACHE_KEY)
            ->willReturn(json_encode($expectedTree));
        $this->configWriterMock->expects($this->once())->method('write')->with(
            Provider::ACL_RESOURCES_CACHE_KEY,
            $expectedTree
        );
        $this->assertEquals($expectedTree, $this->_model->getAclResources());
    }

    public function testGetIfAclResourcesEmpty()
    {
        $this->_configReaderMock->expects($this->once())->method('read')->willReturn([]);
        $this->_treeBuilderMock->expects($this->never())->method('build');
        $this->assertEquals([], $this->_model->getAclResources());
    }

    public function testGetAclResourcesFromCompiledConfig()
    {
        $expectedTree = [['id' => 'Magento_Backend::admin', 'children' => []]];

        $model = $this->getMockBuilder(Provider::class)
            ->setConstructorArgs([
                $this->_configReaderMock,
                $this->_treeBuilderMock,
                $this->aclDataCacheMock,
                $this->serializerMock,
                Provider::ACL_RESOURCES_CACHE_KEY,
                $this->configWriterMock,
            ])
            ->onlyMethods(['isCompiledConfigAvailable', 'loadCompiledConfig'])
            ->getMock();

        $model->expects($this->once())
            ->method('isCompiledConfigAvailable')
            ->willReturn(true);
        $model->expects($this->once())
            ->method('loadCompiledConfig')
            ->willReturn($expectedTree);

        $this->_configReaderMock->expects($this->never())->method('read');
        $this->aclDataCacheMock->expects($this->never())->method('load');

        $this->assertEquals($expectedTree, $model->getAclResources());
    }

    public function testCompiledConfigTakesPrecedenceOverCache()
    {
        $compiledTree = [['id' => 'Magento_Backend::admin', 'children' => []]];

        $model = $this->getMockBuilder(Provider::class)
            ->setConstructorArgs([
                $this->_configReaderMock,
                $this->_treeBuilderMock,
                $this->aclDataCacheMock,
                $this->serializerMock,
                Provider::ACL_RESOURCES_CACHE_KEY,
                $this->configWriterMock,
            ])
            ->onlyMethods(['isCompiledConfigAvailable', 'loadCompiledConfig'])
            ->getMock();

        $model->expects($this->once())
            ->method('isCompiledConfigAvailable')
            ->willReturn(true);
        $model->expects($this->once())
            ->method('loadCompiledConfig')
            ->willReturn($compiledTree);

        // Cache should never be consulted when compiled config exists
        $this->aclDataCacheMock->expects($this->never())->method('load');
        $this->_configReaderMock->expects($this->never())->method('read');

        $this->assertEquals($compiledTree, $model->getAclResources());
    }

    public function testCacheHitWritesCompiledConfig()
    {
        $expectedTree = [['id' => 'Magento_Backend::admin', 'children' => []]];

        $model = $this->getMockBuilder(Provider::class)
            ->setConstructorArgs([
                $this->_configReaderMock,
                $this->_treeBuilderMock,
                $this->aclDataCacheMock,
                $this->serializerMock,
                Provider::ACL_RESOURCES_CACHE_KEY,
                $this->configWriterMock,
            ])
            ->onlyMethods(['isCompiledConfigAvailable'])
            ->getMock();

        $model->expects($this->once())
            ->method('isCompiledConfigAvailable')
            ->willReturn(false);

        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(Provider::ACL_RESOURCES_CACHE_KEY)
            ->willReturn(json_encode($expectedTree));

        $this->_configReaderMock->expects($this->never())->method('read');

        $this->configWriterMock->expects($this->once())
            ->method('write')
            ->with(Provider::ACL_RESOURCES_CACHE_KEY, $expectedTree);

        $this->assertEquals($expectedTree, $model->getAclResources());
    }

    public function testNoCompilationWithoutConfigWriter()
    {
        $expectedTree = [['id' => 'Magento_Backend::admin', 'children' => []]];
        $aclResourceConfig['config']['acl']['resources'] = ['ExpectedValue'];

        $model = new Provider(
            $this->_configReaderMock,
            $this->_treeBuilderMock,
            $this->aclDataCacheMock,
            $this->serializerMock,
            Provider::ACL_RESOURCES_CACHE_KEY,
            null
        );

        $this->_configReaderMock->expects($this->once())->method('read')->willReturn($aclResourceConfig);
        $this->_treeBuilderMock->expects($this->once())->method('build')->willReturn($expectedTree);
        $this->aclDataCacheMock->expects($this->once())->method('save')->with(
            json_encode($expectedTree),
            Provider::ACL_RESOURCES_CACHE_KEY
        );
        $this->configWriterMock->expects($this->never())->method('write');

        $this->assertEquals($expectedTree, $model->getAclResources());
    }

    public function testCacheMissWritesCompiledConfig()
    {
        $expectedTree = ['id' => 'Magento_Backend::admin', 'children' => []];
        $aclResourceConfig['config']['acl']['resources'] = ['resources_data'];

        $model = $this->getMockBuilder(Provider::class)
            ->setConstructorArgs([
                $this->_configReaderMock,
                $this->_treeBuilderMock,
                $this->aclDataCacheMock,
                $this->serializerMock,
                Provider::ACL_RESOURCES_CACHE_KEY,
                $this->configWriterMock,
            ])
            ->onlyMethods(['isCompiledConfigAvailable'])
            ->getMock();

        $model->expects($this->once())
            ->method('isCompiledConfigAvailable')
            ->willReturn(false);

        $this->aclDataCacheMock->expects($this->once())
            ->method('load')
            ->with(Provider::ACL_RESOURCES_CACHE_KEY)
            ->willReturn(false);

        $this->_configReaderMock->expects($this->once())
            ->method('read')
            ->willReturn($aclResourceConfig);

        $this->_treeBuilderMock->expects($this->once())
            ->method('build')
            ->with(['resources_data'])
            ->willReturn($expectedTree);

        $this->aclDataCacheMock->expects($this->once())->method('save');

        $this->configWriterMock->expects($this->once())
            ->method('write')
            ->with(Provider::ACL_RESOURCES_CACHE_KEY, $expectedTree);

        $this->assertEquals($expectedTree, $model->getAclResources());
    }
}
