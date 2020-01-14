<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\IndexStructureFactory;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;

class IndexStructureFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var IndexStructureFactory */
    private $model;

    /** @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $objectManagerMock;

    /** @var EngineResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $engineResolverMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->engineResolverMock = $this->getMockBuilder(EngineResolverInterface::class)
            ->getMockForAbstractClass();
    }

    public function testCreate()
    {
        $currentStructure = 'current_structure';
        $currentStructureClass = IndexStructureInterface::class;
        $structures = [
            $currentStructure => $currentStructureClass,
        ];
        $data = ['data'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentStructure);

        $indexerStructureMock = $this->getMockBuilder($currentStructureClass)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($currentStructureClass, $data)
            ->willReturn($indexerStructureMock);

        $this->model = new IndexStructureFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $structures
        );

        $this->assertEquals($indexerStructureMock, $this->model->create($data));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage There is no such index structure: current_structure
     */
    public function testCreateWithoutStructures()
    {
        $currentStructure = 'current_structure';
        $structures = [];
        $data = ['data'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentStructure);

        $this->model = new IndexStructureFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $structures
        );

        $this->model->create($data);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage current_structure index structure doesn't implement
     */
    public function testCreateWithWrongStructure()
    {
        $currentStructure = 'current_structure';
        $currentStructureClass = \stdClass::class;
        $structures = [
            $currentStructure => $currentStructureClass,
        ];
        $data = ['data'];

        $this->engineResolverMock->expects($this->once())
            ->method('getCurrentSearchEngine')
            ->willReturn($currentStructure);

        $indexerStructureMock = $this->getMockBuilder($currentStructureClass)
            ->getMockForAbstractClass();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($currentStructureClass, $data)
            ->willReturn($indexerStructureMock);

        $this->model = new IndexStructureFactory(
            $this->objectManagerMock,
            $this->engineResolverMock,
            $structures
        );

        $this->model->create($data);
    }
}
