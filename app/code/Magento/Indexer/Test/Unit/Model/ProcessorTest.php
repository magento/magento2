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

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @inheritDoc
     */
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

        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );

        $indexerRegistryMock = $this->getIndexRegistryMock([]);
        $makeSharedValidMock = new MakeSharedIndexValid(
            $this->configMock,
            $indexerRegistryMock
        );

        $this->model = new Processor(
            $this->configMock,
            $this->indexerFactoryMock,
            $this->indexersFactoryMock,
            $this->viewProcessorMock,
            $makeSharedValidMock,
            $this->indexerRegistryMock
        );
    }

    /**
     * @return void
     */
    public function testReindexAllInvalid(): void
    {
        $indexers = [
            'indexer1' => [],
            'indexer2' => [],
            'indexer3' => []
        ];
        $indexerReturnMap = [
            ['indexer1', ['shared_index' => null]],
            ['indexer2', ['shared_index' => null]],
            ['indexer3', ['shared_index' => null]]
        ];

        $this->configMock->expects($this->once())->method('getIndexers')->willReturn($indexers);
        $this->configMock->method('getIndexer')->willReturnMap($indexerReturnMap);

        // Invalid Indexer
        $state1Mock = $this->createPartialMock(State::class, ['getStatus', '__wakeup']);
        $state1Mock->expects($this->exactly(3))
            ->method('getStatus')
            ->willReturnOnConsecutiveCalls(
                StateInterface::STATUS_INVALID,
                StateInterface::STATUS_INVALID,
                StateInterface::STATUS_VALID
            );
        $indexer1Mock = $this->createPartialMock(Indexer::class, ['load', 'getState', 'reindexAll']);
        $indexer1Mock->expects($this->exactly(3))->method('getState')->willReturn($state1Mock);
        $indexer1Mock->expects($this->once())->method('reindexAll');

        // Valid Indexer
        $state2Mock = $this->createPartialMock(State::class, ['getStatus', '__wakeup']);
        $state2Mock->expects($this->once())->method('getStatus')->willReturn(StateInterface::STATUS_VALID);
        $indexer2Mock = $this->createPartialMock(Indexer::class, ['load', 'getState', 'reindexAll']);
        $indexer2Mock->expects($this->once())->method('getState')->willReturn($state2Mock);
        $indexer2Mock->expects($this->never())->method('reindexAll');

        // Suspended Indexer
        $state3Mock = $this->createPartialMock(State::class, ['getStatus', '__wakeup']);
        $state3Mock->expects($this->exactly(2))->method('getStatus')->willReturnOnConsecutiveCalls(
            StateInterface::STATUS_INVALID,
            StateInterface::STATUS_SUSPENDED
        );
        $indexer3Mock = $this->createPartialMock(Indexer::class, ['load', 'getState', 'reindexAll']);
        $indexer3Mock->expects($this->exactly(2))->method('getState')->willReturn($state3Mock);
        $indexer3Mock->expects($this->never())->method('reindexAll');

        $this->indexerFactoryMock
            ->method('create')
            ->willReturnOnConsecutiveCalls($indexer1Mock, $indexer2Mock, $indexer3Mock);

        $this->model->reindexAllInvalid();
    }

    /**
     * @param array $indexers
     * @param array $indexerStates
     * @param array $expectedReindexAllCalls
     * @param array $executedSharedIndexers
     *
     * @return void
     * @dataProvider sharedIndexDataProvider
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
            $sequence = $indexerStates[$indexerData['indexer_id']] ?? [StateInterface::STATUS_VALID];
            $stateMock->method('getStatus')->willReturnOnConsecutiveCalls(...$sequence);

            $indexerMock = $this->createPartialMock(Indexer::class, ['load', 'getState', 'reindexAll']);
            $indexerMock->expects($this->any())->method('getState')->willReturn($stateMock);
            $indexerMock->expects($expectedReindexAllCalls[$indexerData['indexer_id']])->method('reindexAll');
            $indexerMocks[] = $indexerMock;
        }
        $this->indexerFactoryMock
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$indexerMocks);

        $stateMock = $this->createPartialMock(State::class, ['getStatus', '__wakeup']);
        $stateMock->expects($this->any())
            ->method('getStatus')
            ->willReturn(StateInterface::STATUS_INVALID);
        $indexerMock = $this->createPartialMock(Indexer::class, ['load', 'getState', 'reindexAll']);
        $indexerMock->expects($this->any())->method('getState')->willReturn($stateMock);

        $this->indexerRegistryMock->method('get')
            ->willReturn($indexerMock);

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
            $makeSharedValidMock,
            $this->indexerRegistryMock
        );
        $model->reindexAllInvalid();
    }

    /**
     * Test that any indexers within a group that share a common 'shared_index' ID are suspended.
     *
     * @param array $indexers
     * @param array $indexerStates
     * @param array $expectedReindexAllCalls
     *
     * @return void
     * @dataProvider suspendedIndexDataProvider
     */
    public function testReindexAllInvalidWithSuspendedStatus(
        array $indexers,
        array $indexerStates,
        array $expectedReindexAllCalls
    ): void {
        $this->configMock->expects($this->exactly(3))->method('getIndexers')->willReturn($indexers);
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
            $sequence = $indexerStates[$indexerData['indexer_id']] ?? [StateInterface::STATUS_VALID];
            $stateMock->method('getStatus')->willReturnOnConsecutiveCalls(...$sequence);

            $indexerMock = $this->createPartialMock(Indexer::class, ['load', 'getState', 'reindexAll']);
            $indexerMock->expects($this->any())->method('getState')->willReturn($stateMock);
            $indexerMock->expects($expectedReindexAllCalls[$indexerData['indexer_id']])->method('reindexAll');
            $indexerMocks[] = $indexerMock;
        }
        $this->indexerFactoryMock
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$indexerMocks);

        $stateMock = $this->createPartialMock(State::class, ['getStatus', '__wakeup']);
        $stateMock->expects($this->exactly(3))
            ->method('getStatus')
            ->willReturnOnConsecutiveCalls(
                StateInterface::STATUS_SUSPENDED,
                StateInterface::STATUS_INVALID,
                StateInterface::STATUS_SUSPENDED
            );
        $indexerMock = $this->createPartialMock(Indexer::class, ['load', 'getState', 'reindexAll']);
        $indexerMock->expects($this->exactly(3))->method('getState')->willReturn($stateMock);

        $this->indexerRegistryMock->method('get')->willReturn($indexerMock);

        $this->model->reindexAllInvalid();
    }

    /**
     * Reindex all test.
     *
     * @return void
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
     * Update mview test.
     *
     * @return void
     */
    public function testUpdateMview(): void
    {
        $this->viewProcessorMock->expects($this->once())->method('update')->with('indexer')->willReturnSelf();
        $this->model->updateMview();
    }

    /**
     * Clear change log test.
     *
     * @return void
     */
    public function testClearChangelog(): void
    {
        $this->viewProcessorMock->expects($this->once())->method('clearChangelog')->with('indexer')->willReturnSelf();
        $this->model->clearChangelog();
    }

    /**
     * @return array
     */
    public static function sharedIndexDataProvider(): array
    {
        return [
            'Without dependencies' => [
                'indexers' => [
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'title' => 'Title_indexer_1',
                        'shared_index' => null,
                        'dependencies' => []
                    ],
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'title' => 'Title_indexer_2',
                        'shared_index' => 'with_indexer_3',
                        'dependencies' => []
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'title' => 'Title_indexer_3',
                        'shared_index' => 'with_indexer_3',
                        'dependencies' => []
                    ],
                ],
                'indexerStates' => [
                    'indexer_1' => [
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_VALID
                    ],
                    'indexer_2' => [StateInterface::STATUS_VALID],
                    'indexer_3' => [StateInterface::STATUS_VALID]
                ],
                'expectedReindexAllCalls' => [
                    'indexer_1' => self::once(),
                    'indexer_2' => self::never(),
                    'indexer_3' => self::never()
                ],
                'executedSharedIndexers' => []
            ],
            'With dependencies and some indexers is invalid' => [
                'indexers' => [
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'title' => 'Title_indexer_1',
                        'shared_index' => null,
                        'dependencies' => ['indexer_2', 'indexer_3']
                    ],
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'title' => 'Title_indexer_2',
                        'shared_index' => 'with_indexer_3',
                        'dependencies' => []
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'title' => 'Title_indexer_3',
                        'shared_index' => 'with_indexer_3',
                        'dependencies' => []
                    ],
                    'indexer_4' => [
                        'indexer_id' => 'indexer_4',
                        'title' => 'Title_indexer_4',
                        'shared_index' => null,
                        'dependencies' => ['indexer_1']
                    ]
                ],
                'indexerStates' => [
                    'indexer_1' => [
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_VALID
                    ],
                    'indexer_2' => [StateInterface::STATUS_VALID],
                    'indexer_3' => [
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_VALID
                    ],
                    'indexer_4' => [StateInterface::STATUS_VALID]
                ],
                'expectedReindexAllCalls' => [
                    'indexer_1' => self::once(),
                    'indexer_2' => self::never(),
                    'indexer_3' => self::once(),
                    'indexer_4' => self::never()
                ],
                'executedSharedIndexers' => [['indexer_2'], ['indexer_3']]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function suspendedIndexDataProvider(): array
    {
        return [
            'Indexers' => [
                'indexers' => [
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'title' => 'Title indexer 1',
                        'shared_index' => null,
                        'dependencies' => []
                    ],
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'title' => 'Title indexer 2',
                        'shared_index' => 'common_shared_index',
                        'dependencies' => []
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'title' => 'Title indexer 3',
                        'shared_index' => 'common_shared_index',
                        'dependencies' => []
                    ]
                ],
                'indexerStates' => [
                    'indexer_1' => [
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_VALID
                    ],
                    'indexer_2' => [
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_VALID
                    ],
                    'indexer_3' => [
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_INVALID,
                        StateInterface::STATUS_VALID
                    ]
                ],
                'expectedReindexAllCalls' => [
                    'indexer_1' => self::once(),
                    'indexer_2' => self::never(),
                    'indexer_3' => self::never()
                ]
            ]
        ];
    }

    /**
     * @param array $executedSharedIndexers
     *
     * @return IndexerRegistry|MockObject
     */
    private function getIndexRegistryMock(array $executedSharedIndexers): MockObject
    {
        /** @var MockObject|IndexerRegistry $indexerRegistryMock */
        $indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emptyIndexer = $this->createPartialMock(Indexer::class, ['load', 'getState', 'reindexAll']);
        /** @var MockObject|StateInterface $state */
        $state = $this->getMockBuilder(StateInterface::class)
            ->onlyMethods(['setStatus'])
            ->addMethods(['save'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $state->method('getStatus')
            ->willReturn(StateInterface::STATUS_INVALID);
        $emptyIndexer->method('getState')->willReturn($state);
        $indexerRegistryMock
            ->expects($this->exactly(count($executedSharedIndexers)))
            ->method('get')
            ->willReturnCallback(function ($arg1) use ($emptyIndexer, $executedSharedIndexers) {
                static $callCount = 0;
                if (in_array($arg1, $executedSharedIndexers[$callCount])) {
                    $callCount++;
                    return $emptyIndexer;
                }
            });

        return $indexerRegistryMock;
    }
}
