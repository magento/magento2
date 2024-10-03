<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model\Indexer;

use Magento\Framework\Indexer\Config\DependencyInfoProviderInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Mview\View;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\DependencyDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DependencyDecoratorTest extends TestCase
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
     * @var IndexerInterface|MockObject
     */
    private $indexerMock;

    /**
     * @var DependencyInfoProviderInterface|MockObject
     */
    private $dependencyInfoProviderMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @return void
     */
    protected function setUp(): void
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
     * @param array $params
     * @param mixed $result
     * @dataProvider overloadDataProvider
     */
    public function testOverload(string $methodName, array $params = [], $result = null)
    {
        $indexerMock = $this->getMockBuilder(Indexer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dependencyDecorator = $this->objectManagerHelper->getObject(
            DependencyDecorator::class,
            [
                'indexer' => $indexerMock,
                'dependencyInfoProvider' => $this->dependencyInfoProviderMock,
                'indexerRegistry' => $this->indexerRegistryMock,
            ]
        );
        $indexerMock
            ->expects($this->once())
            ->method($methodName)
            ->with(...$params)
            ->willReturn($result);
        $this->assertSame($result, $dependencyDecorator->{$methodName}(...$params));
    }

    /**
     * @return array
     */
    public static function overloadDataProvider()
    {
        return [
            ['getData', [], ['field_id' => 'field_value']],
            ['setId', ['newId'], true]
        ];
    }

    /**
     * @param string|\Closure $methodName
     * @param mixed $result
     * @dataProvider transitMethodsDataProvider
     */
    public function testTransitMethods(string|\Closure $methodName, $result)
    {
        if (is_callable($result)) {
            $result = $result($this);
        }
        $this->indexerMock
            ->expects($this->once())
            ->method($methodName)
            ->with()
            ->willReturn($result);
        $this->assertSame($result, $this->dependencyDecorator->{$methodName}());
    }

    protected function getMockForViewClass()
    {
        return $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getMockForStateInterfaceClass()
    {
        return $this->getMockBuilder(StateInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * @return array
     */
    public static function transitMethodsDataProvider()
    {
        return [
            ['getId', 'indexer_1'],
            ['getViewId', 'view1'],
            ['getActionClass', 'className'],
            ['getDescription', 'some_text'],
            ['getFields', ['one', 'two']],
            ['getSources', ['one', 'two']],
            ['getHandlers', ['one', 'two']],
            ['getView', static fn (self $testCase) => $testCase->getMockForViewClass()],
            ['getState', static fn (self $testCase) => $testCase->getMockForStateInterfaceClass()],
            ['isScheduled', true],
            ['isValid', false],
            ['isInvalid', true],
            ['isWorking', true],
            ['getStatus', 'valid'],
            ['getLatestUpdated', '42'],
        ];
    }

    /**
     * @param string $methodName
     * @param array $params
     * @dataProvider transitMethodsWithParamsAndEmptyReturnDataProvider
     */
    public function testTransitMethodsWithParamsAndEmptyReturn(string $methodName, array $params)
    {
        $this->indexerMock
            ->expects($this->once())
            ->method($methodName)
            ->with(...$params);
        $this->assertEmpty($this->dependencyDecorator->{$methodName}(...$params));
    }

    /**
     * @return array
     */
    public static function transitMethodsWithParamsAndEmptyReturnDataProvider()
    {
        return [
            ['setScheduled', [true]],
        ];
    }

    /**
     * @param string $methodName
     * @param array $params
     * @dataProvider transitMethodsWithParamsAndSelfReturnDataProvider
     */
    public function testTransitMethodsWithParamsAndSelfReturn(string $methodName, array $params)
    {
        if (!empty($params) && is_callable($params[0])) {
            $params[0] = $params[0]($this);
        }
        $this->indexerMock
            ->expects($this->once())
            ->method($methodName)
            ->with(...$params);
        $this->assertEquals($this->dependencyDecorator, $this->dependencyDecorator->{$methodName}(...$params));
    }

    /**
     * @return array
     */
    public static function transitMethodsWithParamsAndSelfReturnDataProvider()
    {
        return [
            [
                'setState',
                [
                    static fn (self $testCase) => $testCase->getMockForStateInterfaceClass()
                ]
            ],
            ['load', ['indexer_1']],
        ];
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
        $this->dependencyInfoProviderMock
            ->expects($this->never())
            ->method('getIndexerIdsToRunBefore');
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
     * @return MockObject|IndexerInterface
     */
    private function getIndexerMock()
    {
        $indexer = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();
        return $indexer;
    }
}
