<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Indexer\Console\Command\IndexerShowDimensionsModeCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class IndexerShowDimensionsModeCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerShowDimensionsModeCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $command;

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configReaderMock;

    /**
     * @var \Magento\Indexer\Model\ModeSwitcherInterface[]
     */
    private $indexers;

    /**
     * @var \Magento\Indexer\Model\Indexer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->configReaderMock = $this->createMock(ScopeConfigInterface::class);
        $this->indexers = ['indexer_1' => 'indexer_1', 'indexer_2' => 'indexer_2'];
        $this->command = $objectManagerHelper->getObject(
            IndexerShowDimensionsModeCommand::class,
            [
                'objectManagerFactory' => $this->objectManagerFactory,
                'configReader'         => $this->configReaderMock,
                'indexers'             => $this->indexers,
            ]
        );
    }

    /**
     * Get return value map for object manager
     *
     * @return array
     */
    protected function getObjectManagerReturnValueMap(): array
    {
        $result = parent::getObjectManagerReturnValueMap();
        $this->indexerMock = $this->createMock(\Magento\Indexer\Model\Indexer::class);
        $result[] = [\Magento\Indexer\Model\Indexer::class, $this->indexerMock];

        return $result;
    }

    /**
     * Tests method \Magento\Indexer\Console\Command\IndexerDimensionsModeCommand::execute
     *
     * @param $command
     * @param $consoleOutput
     * @dataProvider dimensionModesDataProvider
     */
    public function testExecuteWithAttributes($command, $consoleOutput)
    {
        $indexers = [['indexer_1'], ['indexer_2']];
        $indexerTitles = ['indexer_title1', 'indexer_title2'];
        $this->configureAdminArea();
        /** @var CommandTester $commandTester */
        $commandTester = new CommandTester($this->command);
        $this->indexerMock->method('load')->withConsecutive(...$indexers);
        $this->indexerMock->method('getTitle')->willReturnOnConsecutiveCalls(...$indexerTitles);
        $commandTester->execute($command);
        $actualValue = $commandTester->getDisplay();
        $this->assertEquals(
            $consoleOutput,
            $actualValue
        );
    }

    /**
     * @return array
     */
    public function dimensionModesDataProvider(): array
    {
        return [
            'get_all'                => [
                'command' => [],
                'output'  =>
                    sprintf(
                        '%-50s ',
                        'indexer_title1' . ':'
                    ) . 'none' . PHP_EOL .
                    sprintf(
                        '%-50s ',
                        'indexer_title2' . ':'
                    ) . 'none' . PHP_EOL
                ,
            ],
            'get_by_index'           => [
                'command' => [
                    'indexer' => ['indexer_1'],
                ],
                'output'  =>
                    sprintf(
                        '%-50s ',
                        'indexer_title1' . ':'
                    ) . 'none' . PHP_EOL
                ,
            ],
            'get_by_several_indexes' => [
                'command' => [
                    'indexer' => ['indexer_1', 'indexer_2'],
                ],
                'output'  =>
                    sprintf(
                        '%-50s ',
                        'indexer_title1' . ':'
                    ) . 'none' . PHP_EOL .
                    sprintf(
                        '%-50s ',
                        'indexer_title2' . ':'
                    ) . 'none' . PHP_EOL
                ,
            ],
        ];
    }
}
