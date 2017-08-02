<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Test\Unit\Model\Indexer;

use Magento\Framework\Indexer\Config\DependencyInfoProviderInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Indexer\Model\Indexer\DependencyDecorator;

class DependencyDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var DependencyDecorator
     */
    private $dependencyDecorator;

    /**
     * @var IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerMock;

    /**
     * @var DependencyInfoProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dependencyInfoProviderMock;

    /**
     * @var IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerRegistryMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->indexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();

        $this->dependencyInfoProviderMock = $this->getMockBuilder(DependencyInfoProviderInterface::class)
            ->getMockForAbstractClass();

        $this->indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dependencyDecorator = $this->objectManagerHelper->getObject(
            DependencyDecorator::class,
            [
                'indexer' => $this->indexerMock,
                'dependencyInfoProvider' => $this->dependencyInfoProviderMock,
                'indexerRegistry' => $this->indexerRegistryMock,
            ]
        );
    }

    /**
     * @param string $methodName
     * @dataProvider transitMethodsDataProvider
     */
    public function testTransitMethods(string $methodName)
    {
        $value = 42;
        $this->indexerMock
            ->expects($this->once())
            ->method($methodName)
            ->with()
            ->willReturn($value);
        $this->assertSame($value, $this->dependencyDecorator->{$methodName}());
    }

    /**
     * @return array
     */
    public function transitMethodsDataProvider()
    {
        return [
            ['getId'],
            ['getViewId'],
            ['getActionClass'],
            ['getDescription'],
            ['getFields'],
            ['getSources'],
            ['getHandlers'],
            ['getView'],
            ['getState'],
            ['isScheduled'],
            ['isValid'],
            ['isInvalid'],
            ['isWorking'],
            ['getStatus'],
            ['getLatestUpdated'],
        ];
    }

    /**
     * @param string $methodName
     * @param array $params
     * @dataProvider transitMethodsWithParamsDataProvider
     */
    public function testTransitMethodsWithParams(string $methodName, array $params)
    {
        $this->indexerMock
            ->expects($this->once())
            ->method($methodName)
            ->with(...$params);
        $this->dependencyDecorator->{$methodName}(...$params);
    }

    /**
     * @return array
     */
    public function transitMethodsWithParamsDataProvider()
    {
        return [
            [
                'setState',
                [
                    $this->getMockBuilder(StateInterface::class)
                        ->getMockForAbstractClass()
                ]
            ],
            ['setScheduled', [true]],
        ];
    }

    public function testLoad()
    {
        $inputValue = 42;
        $this->indexerMock
            ->expects($this->once())
            ->method('load')
            ->with($inputValue)
            ->willReturn($inputValue);
        $this->assertInstanceOf(DependencyDecorator::class, $this->dependencyDecorator->load($inputValue));
    }

    public function testReindexAll()
    {
        $this->indexerMock
            ->expects($this->once())
            ->method('reindexAll')
            ->with();
        $this->dependencyDecorator->reindexAll();
    }

    public function testInvalidate()
    {
        $indexerId = 'indexer_1';
        $dependentIds = ['indexer_2', 'indexer_3'];
        $calls = [];
        foreach ($dependentIds as $dependentId) {
            $indexer = $this->getIndexerMock();
            $indexer->expects($this->once())
                ->method('invalidate');
            $calls[] = [$dependentId, $indexer];
        }
        $this->indexerMock
            ->expects($this->once())
            ->method('invalidate')
            ->with();
        $this->indexerMock
            ->method('getId')
            ->willReturn($indexerId);
        $this->dependencyInfoProviderMock
            ->expects($this->once())
            ->method('getIndexerIdsToRunAfter')
            ->with($indexerId)
            ->willReturn($dependentIds);
        $this->indexerRegistryMock
            ->expects($this->exactly(count($dependentIds)))
            ->method('get')
            ->willReturnMap($calls);
        $this->dependencyDecorator->invalidate();
    }

    public function testReindexRow()
    {
        $inputId = 100200;
        $indexerId = 'indexer_1';
        $dependentIds = ['indexer_2', 'indexer_3'];
        $calls = [];
        foreach ($dependentIds as $dependentId) {
            $indexer = $this->getIndexerMock();
            $indexer->expects($this->once())
                ->method('reindexRow')
                ->with($inputId);
            $calls[] = [$dependentId, $indexer];
        }
        $this->indexerMock
            ->expects($this->once())
            ->method('reindexRow')
            ->with($inputId);
        $this->indexerMock
            ->method('getId')
            ->willReturn($indexerId);
        $this->dependencyInfoProviderMock
            ->expects($this->once())
            ->method('getIndexerIdsToRunAfter')
            ->with($indexerId)
            ->willReturn($dependentIds);
        $this->indexerRegistryMock
            ->expects($this->exactly(count($dependentIds)))
            ->method('get')
            ->willReturnMap($calls);
        $this->dependencyDecorator->reindexRow($inputId);
    }

    public function testReindexList()
    {
        $inputIds = [100200, 100300];
        $indexerId = 'indexer_1';
        $dependentIds = ['indexer_2', 'indexer_3'];
        $calls = [];
        foreach ($dependentIds as $dependentId) {
            $indexer = $this->getIndexerMock();
            $indexer->expects($this->once())
                ->method('reindexList')
                ->with($inputIds);
            $calls[] = [$dependentId, $indexer];
        }
        $this->indexerMock
            ->expects($this->once())
            ->method('reindexList')
            ->with($inputIds);
        $this->indexerMock
            ->method('getId')
            ->willReturn($indexerId);
        $this->dependencyInfoProviderMock
            ->expects($this->once())
            ->method('getIndexerIdsToRunAfter')
            ->with($indexerId)
            ->willReturn($dependentIds);
        $this->indexerRegistryMock
            ->expects($this->exactly(count($dependentIds)))
            ->method('get')
            ->willReturnMap($calls);
        $this->dependencyDecorator->reindexList($inputIds);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IndexerInterface
     */
    private function getIndexerMock()
    {
        $indexer = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        return $indexer;
    }
}
