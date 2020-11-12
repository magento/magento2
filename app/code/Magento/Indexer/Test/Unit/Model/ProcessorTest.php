<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model;

use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Mview\ProcessorInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\Collection;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Magento\Indexer\Model\Indexer\State;
use Magento\Indexer\Model\Processor;
use Magento\Indexer\Model\Processor\MakeSharedIndexValid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @var Processor|MockObject
     */
    protected $model;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var IndexerInterfaceFactory|MockObject
     */
    protected $indexerFactoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $indexersFactoryMock;

    /**
     * @var ProcessorInterface|MockObject
     */
    protected $viewProcessorMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockForAbstractClass(
            ConfigInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getIndexers']
        );
        $this->indexerFactoryMock = $this->createPartialMock(
            IndexerInterfaceFactory::class,
            ['create']
        );
        $this->indexersFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->viewProcessorMock = $this->getMockForAbstractClass(
            ProcessorInterface::class,
            [],
            '',
            false
        );
        $this->model = new Processor(
            $this->configMock,
            $this->indexerFactoryMock,
            $this->indexersFactoryMock,
            $this->viewProcessorMock
        );
    }

    /**
     * @return void
     */
    public function testReindexAllInvalid(): void
    {
        $indexers = ['indexer1' => [], 'indexer2' => []];

        $this->configMock->expects($this->once())->method('getIndexers')->willReturn($indexers);

        $state1Mock = $this->createPartialMock(State::class, ['getStatus', '__wakeup']);
        $state1Mock->expects(
            $this->once()
        )->method(
            'getStatus'
        )->willReturn(
            StateInterface::STATUS_INVALID
        );
        $indexer1Mock = $this->createPartialMock(
            Indexer::class,
            ['load', 'getState', 'reindexAll']
        );
        $indexer1Mock->expects($this->once())->method('getState')->willReturn($state1Mock);
        $indexer1Mock->expects($this->once())->method('reindexAll');

        $state2Mock = $this->createPartialMock(State::class, ['getStatus', '__wakeup']);
        $state2Mock->expects(
            $this->once()
        )->method(
            'getStatus'
        )->willReturn(
            StateInterface::STATUS_VALID
        );
        $indexer2Mock = $this->createPartialMock(
            Indexer::class,
            ['load', 'getState', 'reindexAll']
        );
        $indexer2Mock->expects($this->never())->method('reindexAll');
        $indexer2Mock->expects($this->once())->method('getState')->willReturn($state2Mock);

        $this->indexerFactoryMock->expects($this->at(0))->method('create')->willReturn($indexer1Mock);
        $this->indexerFactoryMock->expects($this->at(1))->method('create')->willReturn($indexer2Mock);

        $this->model->reindexAllInvalid();
    }

    /**
     * @dataProvider sharedIndexDataProvider
     * @param array $indexers
     * @param array $indexerStates
     * @param array $expectedReindexAllCalls
     * @param array $executedSharedIndexers
     */
    public function testReindexAllInvalidWithSharedIndex(
        array $indexers,
        array $indexerStates,
        array $expectedReindexAllCalls,
        array $executedSharedIndexers
    ): void {
        $this->configMock->expects($this->any())->method('getIndexers')->willReturn($indexers);
        $this->configMock
            ->method('getIndexer')
            ->willReturnMap(
                array_map(
                    function ($elem) {
                        return [$elem['indexer_id'], $elem];
                    },
                    $indexers
                )
            );
        $indexerMocks = [];
        foreach ($indexers as $indexerData) {
            $stateMock = $this->createPartialMock(State::class, ['getStatus', '__wakeup']);
            $stateMock->expects($this->any())
                ->method('getStatus')
                ->willReturn($indexerStates[$indexerData['indexer_id']]);
            $indexerMock = $this->createPartialMock(Indexer::class, ['load', 'getState', 'reindexAll']);
            $indexerMock->expects($this->any())->method('getState')->willReturn($stateMock);
            $indexerMock->expects($expectedReindexAllCalls[$indexerData['indexer_id']])->method('reindexAll');

            $this->indexerFactoryMock->expects($this->at(count($indexerMocks)))
                ->method('create')
                ->willReturn($indexerMock);

            $indexerMocks[] = $indexerMock;
        }
        $indexerRegistryMock = $this->getIndexRegistryMock($executedSharedIndexers);

        $makeSharedValidMock = new MakeSharedIndexValid(
            $this->configMock,
            $indexerRegistryMock
        );
        $model = new Processor(
            $this->configMock,
            $this->indexerFactoryMock,
            $this->indexersFactoryMock,
            $this->viewProcessorMock,
            $makeSharedValidMock
        );
        $model->reindexAllInvalid();
    }

    /**
     * Reindex all test
     *
     * return void
     */
    public function testReindexAll(): void
    {
        $indexerMock = $this->createMock(Indexer::class);
        $indexerMock->expects($this->exactly(2))->method('reindexAll');
        $indexers = [$indexerMock, $indexerMock];

        $indexersMock = $this->createMock(Collection::class);
        $this->indexersFactoryMock->expects($this->once())->method('create')->willReturn($indexersMock);
        $indexersMock->expects($this->once())->method('getItems')->willReturn($indexers);

        $this->model->reindexAll();
    }

    /**
     * Update mview test
     *
     * @return void
     */
    public function testUpdateMview()
    {
        $this->viewProcessorMock->expects($this->once())->method('update')->with('indexer')->willReturnSelf();
        $this->model->updateMview();
    }

    /**
     * Clear change log test
     *
     * @return void
     */
    public function testClearChangelog()
    {
        $this->viewProcessorMock->expects($this->once())->method('clearChangelog')->with('indexer')->willReturnSelf();
        $this->model->clearChangelog();
    }

    /**
     * @return array
     */
    public function sharedIndexDataProvider()
    {
        return [
            'Without dependencies' => [
                'indexers' => [
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'title' => 'Title_indexer_1',
                        'shared_index' => null,
                        'dependencies' => [],
                    ],
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'title' => 'Title_indexer_2',
                        'shared_index' => 'with_indexer_3',
                        'dependencies' => [],
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'title' => 'Title_indexer_3',
                        'shared_index' => 'with_indexer_3',
                        'dependencies' => [],
                    ],
                ],
                'indexer_states' => [
                    'indexer_1' => StateInterface::STATUS_INVALID,
                    'indexer_2' => StateInterface::STATUS_VALID,
                    'indexer_3' => StateInterface::STATUS_VALID,
                ],
                'expected_reindex_all_calls' => [
                    'indexer_1' => $this->once(),
                    'indexer_2' => $this->never(),
                    'indexer_3' => $this->never(),
                ],
                'executed_shared_indexers' => [],
            ],
            'With dependencies and some indexers is invalid' => [
                'indexers' => [
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'title' => 'Title_indexer_1',
                        'shared_index' => null,
                        'dependencies' => ['indexer_2', 'indexer_3'],
                    ],
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'title' => 'Title_indexer_2',
                        'shared_index' => 'with_indexer_3',
                        'dependencies' => [],
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'title' => 'Title_indexer_3',
                        'shared_index' => 'with_indexer_3',
                        'dependencies' => [],
                    ],
                    'indexer_4' => [
                        'indexer_id' => 'indexer_4',
                        'title' => 'Title_indexer_4',
                        'shared_index' => null,
                        'dependencies' => ['indexer_1'],
                    ],
                ],
                'indexer_states' => [
                    'indexer_1' => StateInterface::STATUS_INVALID,
                    'indexer_2' => StateInterface::STATUS_VALID,
                    'indexer_3' => StateInterface::STATUS_INVALID,
                    'indexer_4' => StateInterface::STATUS_VALID,
                ],
                'expected_reindex_all_calls' => [
                    'indexer_1' => $this->once(),
                    'indexer_2' => $this->never(),
                    'indexer_3' => $this->once(),
                    'indexer_4' => $this->never(),
                ],
                'executed_shared_indexers' => [['indexer_2'], ['indexer_3']],
            ],
        ];
    }

    /**
     * @param array $executedSharedIndexers
     * @return IndexerRegistry|MockObject
     */
    private function getIndexRegistryMock(array $executedSharedIndexers)
    {
        /** @var MockObject|IndexerRegistry $indexerRegistryMock */
        $indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emptyIndexer = $this->createPartialMock(Indexer::class, ['load', 'getState', 'reindexAll']);
        /** @var MockObject|StateInterface $state */
        $state = $this->getMockBuilder(StateInterface::class)
            ->setMethods(['setStatus', 'save'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $state->method('getStatus')
            ->willReturn(StateInterface::STATUS_INVALID);
        $emptyIndexer->method('getState')->willReturn($state);
        $indexerRegistryMock
            ->expects($this->exactly(count($executedSharedIndexers)))
            ->method('get')
            ->withConsecutive(...$executedSharedIndexers)
            ->willReturn($emptyIndexer);

        return $indexerRegistryMock;
    }
}
