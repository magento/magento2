<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Framework\Console\Cli;
use Magento\Framework\Indexer\Config\DependencyInfoProvider;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Indexer\Console\Command\IndexerReindexCommand;
use Magento\Indexer\Model\Config;
use Magento\Indexer\Model\Processor\MakeSharedIndexValid;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerReindexCommandTest extends AbstractIndexerCommandCommonSetup
{
    private const STUB_INDEXER_NAME = 'Indexer Name';
    /**
     * Command being tested
     *
     * @var IndexerReindexCommand
     */
    private $command;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var DependencyInfoProvider|MockObject
     */
    private $dependencyInfoProviderMock;

    /**
     * @var MakeSharedIndexValid|MockObject
     */
    private $makeSharedValidMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->configMock = $this->createMock(Config::class);
        $this->indexerRegistryMock = $this->getMockBuilder(IndexerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->makeSharedValidMock = $this->getMockBuilder(MakeSharedIndexValid::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dependencyInfoProviderMock = $this->objectManagerHelper->getObject(DependencyInfoProvider::class, [
            'config' => $this->configMock,
        ]);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        parent::setUp();
    }

    /**
     * Get return value map for object manager
     *
     * @return array
     */
    protected function getObjectManagerReturnValueMap()
    {
        $result = parent::getObjectManagerReturnValueMap();
        $result[] = [ConfigInterface::class, $this->configMock];
        $result[] = [DependencyInfoProvider::class, $this->dependencyInfoProviderMock];
        return $result;
    }

    public function testGetOptions()
    {
        $this->stateMock->expects($this->never())->method('setAreaCode');
        $this->command = new IndexerReindexCommand(
            $this->objectManagerFactory,
            $this->indexerRegistryMock,
            $this->dependencyInfoProviderMock,
            $this->makeSharedValidMock,
            $this->loggerMock
        );
        $optionsList = $this->command->getInputList();
        $this->assertCount(1, $optionsList);
        $this->assertSame('index', $optionsList[0]->getName());
    }

    public function testExecuteAll()
    {
        $this->configMock->expects($this->once())
            ->method('getIndexer')
            ->willReturn(
                [
                    'title' => 'Title_indexerOne',
                    'shared_index' => null
                ]
            );
        $this->configureAdminArea();
        $this->initIndexerCollectionByItems(
            [
                $this->getIndexerMock(
                    ['reindexAll', 'getStatus'],
                    ['indexer_id' => 'id_indexerOne', 'title' => self::STUB_INDEXER_NAME]
                )
            ]
        );
        $this->indexerFactory->expects($this->never())->method('create');
        $this->command = new IndexerReindexCommand(
            $this->objectManagerFactory,
            $this->indexerRegistryMock,
            $this->dependencyInfoProviderMock,
            $this->makeSharedValidMock,
            $this->loggerMock
        );
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $this->assertStringStartsWith(
            self::STUB_INDEXER_NAME . ' index has been rebuilt successfully in',
            $actualValue
        );
        $this->assertMatchesRegularExpression(
            '/' . self::STUB_INDEXER_NAME
            . ' index has been rebuilt successfully in (?:(?:([01]?\d|2[0-3]):)?([0-5]?\d):)?([0-5]?\d)/m',
            $actualValue
        );
    }

    /**
     * @param array $inputIndexers
     * @param array $indexers
     * @param array $states
     * @param array $reindexAllCallMatchers
     * @param array $executedIndexers
     * @param array $executedSharedIndexers
     *
     * @dataProvider executeWithIndexDataProvider
     */
    public function testExecuteWithIndex(
        array $inputIndexers,
        array $indexers,
        array $states,
        array $reindexAllCallMatchers,
        array $executedIndexers,
        array $executedSharedIndexers
    ) {
        $this->addSeparateIndexersToConfigMock($indexers);
        $this->addAllIndexersToConfigMock($indexers);

        $indexerMocks = [];
        foreach ($indexers as $indexerData) {
            $indexer = $this->getIndexerMock(['getState', 'reindexAll', 'isInvalid'], $indexerData);
            $indexer->method('getState')
                ->willReturn(
                    $this->getStateMock(
                        ['loadByIndexer', 'setStatus'],
                        $states[$indexer->getId()] ?? []
                    )
                );
            $indexer->method('isInvalid')
                ->willReturn(StateInterface::STATUS_INVALID === ($states[$indexer->getId()]['status'] ?? ''));
            $indexer->expects($reindexAllCallMatchers[$indexer->getId()])
                ->method('reindexAll');
            $indexerMocks[] = $indexer;
        }
        $this->initIndexerCollectionByItems($indexerMocks);

        $emptyIndexer = $this->getIndexerMock(['load', 'getState']);
        $this->indexerRegistryMock
            ->expects($this->exactly(count($executedSharedIndexers)))
            ->method('get')
            ->willReturnCallback(function (...$executedSharedIndexers) use ($emptyIndexer) {
                return $emptyIndexer;
            });
        $emptyIndexer->method('getState')
            ->willReturn($this->getStateMock(['setStatus']));

        $this->makeSharedValidMock = $this->objectManagerHelper->getObject(MakeSharedIndexValid::class, [
            'config' => $this->configMock,
            'indexerRegistry' => $this->indexerRegistryMock
        ]);
        $this->configureAdminArea();

        $this->command = new IndexerReindexCommand(
            $this->objectManagerFactory,
            $this->indexerRegistryMock,
            $this->dependencyInfoProviderMock,
            $this->makeSharedValidMock,
            $this->loggerMock
        );

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => $inputIndexers]);
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->getStatusCode());
        $pattern = '#^';
        $template = '{Title} index has been rebuilt successfully in \d{2}:\d{2}:\d{2}\W*';
        foreach ($executedIndexers as $indexerId) {
            $pattern .= str_replace(
                '{Title}',
                $indexers[$indexerId]['title'],
                $template
            );
        }
        $pattern .= '$#';
        $this->assertMatchesRegularExpression($pattern, $commandTester->getDisplay());
    }

    /**
     * @param array $indexers
     */
    private function addSeparateIndexersToConfigMock(array $indexers)
    {
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
    }

    /**
     * @param array $indexers
     */
    private function addAllIndexersToConfigMock(array $indexers)
    {
        $this->configMock
            ->method('getIndexers')
            ->willReturn($indexers);
    }

    /**
     * @param array|null $methods
     * @param array $data
     *
     * @return MockObject|StateInterface
     */
    private function getStateMock(?array $methods = null, array $data = [])
    {
        /** @var MockObject|StateInterface $state */
        $state = $this->getMockBuilder(StateInterface::class)
            ->addMethods(['save'])
            ->onlyMethods($methods)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $state->method('getStatus')
            ->willReturn($data['status'] ?? StateInterface::STATUS_INVALID);
        return $state;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function executeWithIndexDataProvider()
    {
        return [
            'Without dependencies' => [
                'inputIndexers' => [
                    'indexer_1'
                ],
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
                'states' => [
                    'indexer_2' => [
                        'status' => StateInterface::STATUS_VALID,
                    ],
                    'indexer_3' => [
                        'status' => StateInterface::STATUS_VALID,
                    ],
                ],
                'reindexAllCallMatchers' => [
                    'indexer_1' => self::once(),
                    'indexer_2' => self::never(),
                    'indexer_3' => self::never(),
                ],
                'executedIndexers' => ['indexer_1'],
                'executedSharedIndexers' => [],
            ],
            'With dependencies and some indexers is invalid' => [
                'inputIndexers' => [
                    'indexer_1'
                ],
                'indexers' => [
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
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'title' => 'Title_indexer_1',
                        'shared_index' => null,
                        'dependencies' => ['indexer_2', 'indexer_3'],
                    ],
                    'indexer_4' => [
                        'indexer_id' => 'indexer_4',
                        'title' => 'Title_indexer_4',
                        'shared_index' => null,
                        'dependencies' => [],
                    ],
                    'indexer_5' => [
                        'indexer_id' => 'indexer_5',
                        'title' => 'Title_indexer_5',
                        'shared_index' => null,
                        'dependencies' => ['indexer_1'],
                    ],
                ],
                'states' => [
                    'indexer_2' => [
                        'status' => StateInterface::STATUS_VALID,
                    ],
                    'indexer_3' => [
                        'status' => StateInterface::STATUS_INVALID,
                    ],
                    'indexer_4' => [
                        'status' => StateInterface::STATUS_INVALID,
                    ],
                    'indexer_5' => [
                        'status' => StateInterface::STATUS_VALID,
                    ],
                ],
                'reindexAllCallMatchers' => [
                    'indexer_1' => self::once(),
                    'indexer_2' => self::never(),
                    'indexer_3' => self::once(),
                    'indexer_4' => self::never(),
                    'indexer_5' => self::once(),
                ],
                'executedIndexers' => ['indexer_3', 'indexer_1', 'indexer_5'],
                'executedSharedIndexers' => [['indexer_2'], ['indexer_3']],
            ],
            'With dependencies and multiple indexers in request' => [
                'inputIndexers' => [
                    'indexer_1',
                    'indexer_3'
                ],
                'indexers' => [
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'title' => 'Title_indexer_2',
                        'shared_index' => null,
                        'dependencies' => [],
                    ],
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'title' => 'Title_indexer_1',
                        'shared_index' => null,
                        'dependencies' => ['indexer_2'],
                    ],
                    'indexer_4' => [
                        'indexer_id' => 'indexer_4',
                        'title' => 'Title_indexer_4',
                        'shared_index' => null,
                        'dependencies' => [],
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'title' => 'Title_indexer_3',
                        'shared_index' => null,
                        'dependencies' => ['indexer_4'],
                    ],
                    'indexer_5' => [
                        'indexer_id' => 'indexer_5',
                        'title' => 'Title_indexer_5',
                        'shared_index' => null,
                        'dependencies' => ['indexer_1'],
                    ],
                ],
                'states' => [
                    'indexer_2' => [
                        'status' => StateInterface::STATUS_VALID,
                    ],
                    'indexer_4' => [
                        'status' => StateInterface::STATUS_INVALID,
                    ],
                    'indexer_5' => [
                        'status' => StateInterface::STATUS_VALID,
                    ],
                ],
                'reindexAllCallMatchers' => [
                    'indexer_1' => self::once(),
                    'indexer_2' => self::never(),
                    'indexer_3' => self::once(),
                    'indexer_4' => self::once(),
                    'indexer_5' => self::once(),
                ],
                'executedIndexers' => ['indexer_1', 'indexer_4', 'indexer_3', 'indexer_5'],
                'executedSharedIndexers' => [],
            ],
        ];
    }

    public function testExecuteWithException()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getIndexerMock(
            ['reindexAll', 'getStatus'],
            ['indexer_id' => 'indexer_1', 'title' => 'Title_indexer_1']
        );
        $indexerOne->expects($this->once())
            ->method('reindexAll')
            ->willThrowException(new \Exception());
        $this->initIndexerCollectionByItems([$indexerOne]);
        $this->command = new IndexerReindexCommand(
            $this->objectManagerFactory,
            $this->indexerRegistryMock,
            $this->dependencyInfoProviderMock,
            $this->makeSharedValidMock,
            $this->loggerMock
        );
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => ['indexer_1']]);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame(Cli::RETURN_FAILURE, $commandTester->getStatusCode());
        $this->assertStringStartsWith(
            'Title_indexer_1' . ' index process error during indexation process:',
            $actualValue
        );
    }

    public function testExecuteWithExceptionInGetIndexers()
    {
        $this->configureAdminArea();
        $inputIndexers = ['indexer_2'];
        $indexerData = [
            'indexer_id' => 'indexer_1',
            'shared_index' => 'new',
        ];
        $indexerOne = $this->getIndexerMock(
            ['reindexAll', 'getStatus', 'load'],
            $indexerData
        );
        $this->initIndexerCollectionByItems([$indexerOne]);

        $indexerOne->expects($this->never())->method('getTitle');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "The following requested index types are not supported: '"
            . join("', '", $inputIndexers)
            . "'." . PHP_EOL . 'Supported types: '
            . join(
                ", ",
                array_map(
                    function ($item) {
                        /** @var IndexerInterface $item */
                        $item->getId();
                    },
                    $this->indexerCollectionMock->getItems()
                )
            )
        );
        $this->command = new IndexerReindexCommand(
            $this->objectManagerFactory,
            $this->indexerRegistryMock,
            $this->dependencyInfoProviderMock,
            $this->makeSharedValidMock,
            $this->loggerMock
        );
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => $inputIndexers]);
        $this->assertSame(Cli::RETURN_FAILURE, $commandTester->getStatusCode());
    }
}
