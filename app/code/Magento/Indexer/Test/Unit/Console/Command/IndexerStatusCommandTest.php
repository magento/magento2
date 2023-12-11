<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Mview\View;
use Magento\Framework\Mview\View\Changelog;
use Magento\Indexer\Console\Command\IndexerStatusCommand;
use Magento\Indexer\Model\Mview\View\State;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerStatusCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerStatusCommand
     */
    private $command;

    /**
     * @param MockObject $indexerMock
     * @param array $data
     * @return mixed
     */
    private function attachViewToIndexerMock($indexerMock, array $data)
    {
        /** @var Changelog|MockObject $changelog */
        $changelog = $this->getMockBuilder(Changelog::class)
            ->disableOriginalConstructor()
            ->getMock();

        $changelog->expects($this->any())
            ->method('getList')
            ->willReturn(range(0, $data['view']['changelog']['list_size']-1));

        /** @var State|MockObject $stateMock */
        $stateMock = $this->getStateMock();

        $stateMock->addData($data['view']['state']);

        /** @var View|MockObject $viewMock */
        $viewMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->setMethods(['getChangelog', 'getState'])
            ->getMock();

        $viewMock->expects($this->any())
            ->method('getState')
            ->willReturn($stateMock);
        $viewMock->expects($this->any())
            ->method('getChangelog')
            ->willReturn($changelog);

        $indexerMock->method('getView')
            ->willReturn($viewMock);

        return $indexerMock;
    }

    /**
     * @return State
     */
    private function getStateMock()
    {
        $contextMock = $this->createPartialMock(\Magento\Framework\Model\Context::class, ['getEventDispatcher']);
        $eventManagerMock = $this->getMockForAbstractClass(\Magento\Framework\Event\ManagerInterface::class);
        $contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($eventManagerMock);
        $registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $resourceMock = $this->createMock(\Magento\Indexer\Model\ResourceModel\Mview\View\State::class);
        $resourceCollectionMock = $this->createMock(
            \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection::class
        );
        $lockManagerMock = $this->createMock(\Magento\Framework\Lock\LockManagerInterface::class);
        $configReaderMock = $this->createMock(\Magento\Framework\App\DeploymentConfig::class);

        return new State(
            $contextMock,
            $registryMock,
            $resourceMock,
            $resourceCollectionMock,
            [],
            $lockManagerMock,
            $configReaderMock
        );
    }

    /**
     * @param array $indexers
     *
     * @dataProvider executeAllDataProvider
     */
    public function testExecuteAll(array $indexers)
    {
        $this->configureAdminArea();
        $indexerMocks = [];
        foreach ($indexers as $indexerData) {
            $indexerMock = $this->getIndexerMock(
                ['getStatus', 'isScheduled', 'getState', 'getView'],
                $indexerData
            );

            $indexerMock->method('getStatus')
                ->willReturn($indexerData['status']);
            $indexerMock->method('isScheduled')
                ->willReturn($indexerData['is_scheduled']);

            if ($indexerData['is_scheduled']) {
                $this->attachViewToIndexerMock($indexerMock, $indexerData);
            }

            $indexerMocks[] = $indexerMock;
        }

        $this->initIndexerCollectionByItems($indexerMocks);
        $this->command = new IndexerStatusCommand($this->objectManagerFactory);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);

        $linesOutput = array_filter(explode(PHP_EOL, $commandTester->getDisplay()));

        $spacer = '+-----------+----------------+------------------+-----------+-------------------------+'
            . '---------------------+';

        $this->assertCount(8, $linesOutput, 'There should be 8 lines output. 3 Spacers, 1 header, 4 content.');
        $this->assertEquals($linesOutput[0], $spacer, "Lines 0, 2, 7 should be spacer lines");
        $this->assertEquals($linesOutput[2], $spacer, "Lines 0, 2, 7 should be spacer lines");
        $this->assertEquals($linesOutput[7], $spacer, "Lines 0, 2, 7 should be spacer lines");

        $headerValues = array_values(array_filter(explode('|', $linesOutput[1])));
        $this->assertEquals('ID', trim($headerValues[0]));
        $this->assertEquals('Title', trim($headerValues[1]));
        $this->assertEquals('Status', trim($headerValues[2]));
        $this->assertEquals('Update On', trim($headerValues[3]));
        $this->assertEquals('Schedule Status', trim($headerValues[4]));
        $this->assertEquals('Schedule Updated', trim($headerValues[5]));

        $indexer1 = array_values(array_filter(explode('|', $linesOutput[3])));
        $this->assertEquals('indexer_1', trim($indexer1[0]));
        $this->assertEquals('Title_indexer1', trim($indexer1[1]));
        $this->assertEquals('Ready', trim($indexer1[2]));
        $this->assertEquals('Schedule', trim($indexer1[3]));
        $this->assertEquals('idle (10 in backlog)', trim($indexer1[4]));
        $this->assertEquals('2017-01-01 11:11:11', trim($indexer1[5]));

        $indexer2 = array_values(array_filter(explode('|', $linesOutput[4])));
        $this->assertEquals('indexer_2', trim($indexer2[0]));
        $this->assertEquals('Title_indexer2', trim($indexer2[1]));
        $this->assertEquals('Reindex required', trim($indexer2[2]));
        $this->assertEquals('Save', trim($indexer2[3]));
        $this->assertEquals('', trim($indexer2[4]));
        $this->assertEquals('', trim($indexer2[5]));

        $indexer3 = array_values(array_filter(explode('|', $linesOutput[5])));
        $this->assertEquals('indexer_3', trim($indexer3[0]));
        $this->assertEquals('Title_indexer3', trim($indexer3[1]));
        $this->assertEquals('Processing', trim($indexer3[2]));
        $this->assertEquals('Schedule', trim($indexer3[3]));
        $this->assertEquals('idle (100 in backlog)', trim($indexer3[4]));
        $this->assertEquals('2017-01-01 11:11:11', trim($indexer3[5]));

        $indexer4 = array_values(array_filter(explode('|', $linesOutput[6])));
        $this->assertEquals('indexer_4', trim($indexer4[0]));
        $this->assertEquals('Title_indexer4', trim($indexer4[1]));
        $this->assertEquals('unknown', trim($indexer4[2]));
        $this->assertEquals('Schedule', trim($indexer4[3]));
        $this->assertEquals('running (20 in backlog)', trim($indexer4[4]));
        $this->assertEquals('2017-01-01 11:11:11', trim($indexer4[5]));
    }

    /**
     * @return array
     */
    public function executeAllDataProvider()
    {
        return [
            [
                'indexers' => [
                    'indexer_1' => [
                        'indexer_id' => 'indexer_1',
                        'title' => 'Title_indexer1',
                        'status' => StateInterface::STATUS_VALID,
                        'is_scheduled' => true,
                        'view' => [
                            'state' => [
                                'status' => 'idle',
                                'updated' => '2017-01-01 11:11:11',
                            ],
                            'changelog' => [
                                'list_size' => 10
                            ]
                        ]
                    ],
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'title' => 'Title_indexer2',
                        'status' => StateInterface::STATUS_INVALID,
                        'is_scheduled' => false,
                        'view' => [
                            'state' => [
                                'status' => 'idle',
                                'updated' => '2017-01-01 11:11:11',
                            ],
                            'changelog' => [
                                'list_size' => 99999999
                            ]
                        ]
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'title' => 'Title_indexer3',
                        'status' => StateInterface::STATUS_WORKING,
                        'is_scheduled' => true,
                        'view' => [
                            'state' => [
                                'status' => 'idle',
                                'updated' => '2017-01-01 11:11:11',
                            ],
                            'changelog' => [
                                'list_size' => 100
                            ]
                        ]
                    ],
                    'indexer_4' => [
                        'indexer_id' => 'indexer_4',
                        'title' => 'Title_indexer4',
                        'status' => null,
                        'is_scheduled' => true,
                        'view' => [
                            'state' => [
                                'status' => 'running',
                                'updated' => '2017-01-01 11:11:11',
                            ],
                            'changelog' => [
                                'list_size' => 20
                            ]
                        ]
                    ],
                ],
            ],
        ];
    }
}
