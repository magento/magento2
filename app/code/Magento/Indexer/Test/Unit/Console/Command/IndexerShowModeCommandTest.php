<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Indexer\Console\Command\IndexerShowModeCommand;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerShowModeCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerShowModeCommand
     */
    private $command;

    public function testGetOptions()
    {
        $this->stateMock->expects($this->never())->method('setAreaCode')->with(FrontNameResolver::AREA_CODE);
        $this->command = new IndexerShowModeCommand($this->objectManagerFactory);
        $optionsList = $this->command->getInputList();
        $this->assertSame(1, count($optionsList));
        $this->assertSame('index', $optionsList[0]->getName());
    }

    public function testExecuteAll()
    {
        $this->configureAdminArea();
        $indexerOne = $this->getIndexerMock(
            ['isScheduled', 'setScheduled'],
            ['indexer_id' => 'indexer_1', 'title' => 'Title_indexerOne']
        );
        $indexerOne->expects($this->once())->method('isScheduled')->willReturn(true);
        $indexerTwo = $this->getIndexerMock(
            ['isScheduled', 'setScheduled'],
            ['indexer_id' => 'indexer_2', 'title' => 'Title_indexerTwo']
        );
        $indexerTwo->expects($this->once())->method('isScheduled')->willReturn(false);
        $this->initIndexerCollectionByItems([$indexerOne, $indexerTwo]);

        $this->command = new IndexerShowModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $expectedValue = sprintf('%-50s ', 'Title_indexerOne' . ':') . 'Update by Schedule' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexerTwo' . ':') . 'Update on Save';
        $this->assertStringStartsWith($expectedValue, $actualValue);
    }

    /**
     * @param array $inputIndexers
     * @param array $indexers
     * @param array $isScheduled
     * @dataProvider executeWithIndexDataProvider
     */
    public function testExecuteWithIndex(array $inputIndexers, array $indexers, array $isScheduled)
    {
        $this->configureAdminArea();
        $indexerMocks = [];
        foreach ($indexers as $indexerData) {
            $indexerMock = $this->getIndexerMock(
                ['isScheduled', 'setScheduled'],
                $indexerData
            );
            $indexerMock->method('isScheduled')
                ->willReturn($isScheduled[$indexerData['indexer_id']]);
            $indexerMocks[] = $indexerMock;
        }

        $this->initIndexerCollectionByItems($indexerMocks);

        $this->command = new IndexerShowModeCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['index' => $inputIndexers]);
        $actualValue = $commandTester->getDisplay();
        $expectedValue = sprintf('%-50s ', 'Title_indexerOne' . ':') . 'Update by Schedule' . PHP_EOL
            . sprintf('%-50s ', 'Title_indexerTwo' . ':') . 'Update on Save';
        $this->assertStringStartsWith($expectedValue, $actualValue);
    }

    /**
     * @return array
     */
    public function executeWithIndexDataProvider()
    {
        return [
            [
                'inputIndexers' => [
                    'id_indexerOne',
                    'id_indexerTwo'
                ],
                'indexers' => [
                    'id_indexerOne' => [
                        'indexer_id' => 'id_indexerOne',
                        'title' => 'Title_indexerOne'
                    ],
                    'id_indexerTwo' => [
                        'indexer_id' => 'id_indexerTwo',
                        'title' => 'Title_indexerTwo'
                    ],
                    'id_indexerThree' => [
                        'indexer_id' => 'id_indexerThree',
                        'title' => 'Title_indexerThree'
                    ],
                ],
                'Is Scheduled' => [
                    'id_indexerOne' => true,
                    'id_indexerTwo' => false,
                    'id_indexerThree' => false,
                ]
            ],
        ];
    }
}
