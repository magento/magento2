<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Console\Command\IndexerStatusCommand;
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
     * @param array $indexers
     * @param array $statuses
     * @dataProvider executeAllDataProvider
     */
    public function testExecuteAll(array $indexers, array $statuses)
    {
        $this->configureAdminArea();
        $indexerMocks = [];
        foreach ($indexers as $indexerData) {
            $indexerMock = $this->getIndexerMock(
                ['getStatus'],
                $indexerData
            );
            $indexerMock->method('getStatus')
                ->willReturn($statuses[$indexerData['indexer_id']]);
            $indexerMocks[] = $indexerMock;
        }
        $this->initIndexerCollectionByItems($indexerMocks);
        $this->command = new IndexerStatusCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $expectedValue = sprintf('%-50s ', 'Title_indexerOne' . ':') . 'Ready' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexerTwo' . ':') . 'Reindex required' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexerThree' . ':') . 'Processing' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexerFour' . ':') . 'unknown' . PHP_EOL;

        $this->assertStringStartsWith($expectedValue, $actualValue);
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
                        'title' => 'Title_indexerOne'
                    ],
                    'indexer_2' => [
                        'indexer_id' => 'indexer_2',
                        'title' => 'Title_indexerTwo'
                    ],
                    'indexer_3' => [
                        'indexer_id' => 'indexer_3',
                        'title' => 'Title_indexerThree'
                    ],
                    'indexer_4' => [
                        'indexer_id' => 'indexer_4',
                        'title' => 'Title_indexerFour'
                    ],
                ],
                'Statuses' => [
                    'indexer_1' => StateInterface::STATUS_VALID,
                    'indexer_2' => StateInterface::STATUS_INVALID,
                    'indexer_3' => StateInterface::STATUS_WORKING,
                    'indexer_4' => null,
                ]
            ],
        ];
    }
}
