<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\IndexersStatesApplyFixture;

class IndexersStatesApplyFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\IndexersStatesApplyFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMock(\Magento\Setup\Fixtures\FixtureModel::class, [], [], '', false);

        $this->model = new IndexersStatesApplyFixture($this->fixtureModelMock);
    }

    public function testExecute()
    {
        $cacheInterfaceMock = $this->getMock(\Magento\Framework\App\CacheInterface::class, [], [], '', false);
        $indexerRegistryMock = $this->getMock(\Magento\Framework\Indexer\IndexerRegistry::class, [], [], '', false);
        $indexerMock = $this->getMockForAbstractClass(\Magento\Framework\Indexer\IndexerInterface::class);

        $indexerRegistryMock->expects($this->once())
            ->method('get')
            ->willReturn($indexerMock);

        $indexerMock->expects($this->once())
            ->method('setScheduled');

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManager\ObjectManager::class, [], [], '', false);
        $objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturn($cacheInterfaceMock);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($indexerRegistryMock);

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn([
                'indexer' => ['id' => 1]
            ]);
        $this->fixtureModelMock
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $cacheInterfaceMock = $this->getMock(\Magento\Framework\App\CacheInterface::class, [], [], '', false);
        $cacheInterfaceMock->expects($this->never())->method('clean');

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManager\ObjectManager::class, [], [], '', false);
        $objectManagerMock->expects($this->never())
            ->method('get')
            ->willReturn($cacheInterfaceMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Indexers Mode Changes', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([], $this->model->introduceParamLabels());
    }
}
