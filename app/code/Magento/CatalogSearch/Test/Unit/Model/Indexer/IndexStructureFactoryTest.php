<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\IndexStructureFactory;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexStructureFactoryTest extends TestCase
{
    /** @var IndexStructureFactory */
    private $model;

    /** @var ObjectManagerInterface|MockObject */
    private $objectManagerMock;

    /** @var EngineResolverInterface|MockObject */
    private $engineResolverMock;

    protected function setUp(): void
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

    public function testCreateWithoutStructures()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('There is no such index structure: current_structure');
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

    public function testCreateWithWrongStructure()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('current_structure index structure doesn\'t implement');
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
