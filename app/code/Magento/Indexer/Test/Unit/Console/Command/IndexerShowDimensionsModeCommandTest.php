<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Indexer\Console\Command\IndexerShowDimensionsModeCommand;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\ModeSwitcherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerShowDimensionsModeCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerShowDimensionsModeCommand|MockObject
     */
    private $command;

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface|MockObject
     */
    private $configReaderMock;

    /**
     * @var ModeSwitcherInterface[]
     */
    private $indexers;

    /**
     * @var Indexer|MockObject
     */
    private $indexerMock;

    /**
     * @var string[]
     */
    private $optionalIndexers;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->configReaderMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->indexers = ['indexer_1' => 'indexer_1', 'indexer_2' => 'indexer_2'];
        $this->command = $this->objectManagerHelper->getObject(
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
        $this->indexerMock = $this->createMock(Indexer::class);
        $result[] = [Indexer::class, $this->indexerMock];

        return $result;
    }

    /**
     * Tests method \Magento\Indexer\Console\Command\IndexerDimensionsModeCommand::execute
     *
     * @param array $command
     * @param string $consoleOutput
     * @dataProvider dimensionModesDataProvider
     */
    public function testExecuteWithAttributes($command, $consoleOutput)
    {
        $indexers = [['indexer_1'], ['indexer_2']];
        $indexerTitles = ['indexer_title1', 'indexer_title2'];
        $this->configureAdminArea();
        /** @var CommandTester $commandTester */
        $commandTester = new CommandTester($this->command);
        $this->indexerMock->method('load')
            ->willReturnCallback(function (...$indexers) {
                if (!empty($indexers)) {
                    return null;
                }
            });
        $this->indexerMock->method('getTitle')->willReturnOnConsecutiveCalls(...$indexerTitles);
        $commandTester->execute($command);
        $actualValue = $commandTester->getDisplay();
        $this->assertEquals(
            $consoleOutput,
            $actualValue
        );
    }

    public function testExecuteWithOptionalIndexers()
    {
        $this->optionalIndexers = ['indexer_3'];
        $this->indexers = ['indexer_3'=> 'indexer_3'];
        $this->command = $this->objectManagerHelper->getObject(
            IndexerShowDimensionsModeCommand::class,
            [
                'objectManagerFactory' => $this->objectManagerFactory,
                'configReader'         => $this->configReaderMock,
                'indexers'             => $this->indexers,
                'optionalIndexers'     => $this->optionalIndexers
            ]
        );
        $command = ['indexer' => ['indexer_3']];
        $this->configureAdminArea();
        /** @var CommandTester $commandTester */
        $commandTester = new CommandTester($this->command);
        $this->indexerMock->method('load')->willThrowException(new \InvalidArgumentException());
        $commandTester->execute($command);
        $actualValue = $commandTester->getDisplay();
        $this->assertEquals('', $actualValue);
    }

    /**
     * @return array
     */
    public static function dimensionModesDataProvider(): array
    {
        return [
            'get_all'                => [
                'command' => [],
                'consoleOutput'  => sprintf(
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
                'consoleOutput'  => sprintf(
                    '%-50s ',
                    'indexer_title1' . ':'
                ) . 'none' . PHP_EOL
                ,
            ],
            'get_by_several_indexes' => [
                'command' => [
                    'indexer' => ['indexer_1', 'indexer_2'],
                ],
                'consoleOutput'  => sprintf(
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
