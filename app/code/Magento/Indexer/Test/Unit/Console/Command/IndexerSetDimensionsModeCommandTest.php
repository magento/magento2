<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Console\Command;

use Magento\Indexer\Console\Command\IndexerSetDimensionsModeCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for class \Magento\Indexer\Model\ModeSwitcherInterface.
 */
class IndexerSetDimensionsModeCommandTest extends AbstractIndexerCommandCommonSetup
{
    /**
     * Command being tested
     *
     * @var IndexerSetDimensionsModeCommand|\PHPUnit\Framework\MockObject\MockObject
     */
    private $command;

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configReaderMock;

    /**
     * @var \Magento\Indexer\Model\ModeSwitcherInterface[]
     */
    private $dimensionProviders;

    /**
     * @var \Magento\Indexer\Model\ModeSwitcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dimensionModeSwitcherMock;

    /**
     * @var \Magento\Indexer\Model\Indexer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexerMock;

    /**
     * @var \Magento\Indexer\Model\DimensionModes|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dimensionModes;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->configReaderMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->dimensionModeSwitcherMock =
            $this->createMock(\Magento\Indexer\Model\ModeSwitcherInterface::class);
        $this->dimensionProviders = [
            'indexer_title' => $this->dimensionModeSwitcherMock,
        ];
        $this->dimensionModes = $this->createMock(\Magento\Indexer\Model\DimensionModes::class);
        $this->command = $objectManagerHelper->getObject(
            IndexerSetDimensionsModeCommand::class,
            [
                'objectManagerFactory' => $this->objectManagerFactory,
                'configReader'         => $this->configReaderMock,
                'dimensionSwitchers'   => $this->dimensionProviders,
            ]
        );
    }

    /**
     * Get return value map for object manager
     *
     * @return array
     */
    protected function getObjectManagerReturnValueMap()
    {
        $result = parent::getObjectManagerReturnValueMap();
        $this->indexerMock = $this->createMock(\Magento\Indexer\Model\Indexer::class);
        $result[] = [\Magento\Indexer\Model\Indexer::class, $this->indexerMock];

        return $result;
    }

    /**
     * Tests method \Magento\Indexer\Console\Command\IndexerDimensionsModeCommand::execute
     *
     * @param string $indexerTitle
     * @param string $previousMode
     * @param string $command
     * @param string $consoleOutput
     * @dataProvider dimensionModesDataProvider
     * @return void
     */
    public function testExecuteWithAttributes($indexerTitle, $previousMode, $command, $consoleOutput)
    {
        $this->configureAdminArea();
        $commandTester = new CommandTester($this->command);
        $this->dimensionModes->method('getDimensions')->willReturn([
            $previousMode    => 'dimension1',
            $command['mode'] => 'dimension2',
        ]);
        $this->dimensionModeSwitcherMock->method('getDimensionModes')->willReturn($this->dimensionModes);
        $this->indexerMock->method('load')->willReturnSelf();
        $this->indexerMock->method('getTitle')->willReturn($indexerTitle);
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
            'was_changed'     => [
                'indexer_title' => 'indexer_title',
                'previousMode'  => 'none',
                'command'       => [
                    'indexer' => 'indexer_title',
                    'mode'    => 'store',
                ],
                'output'        =>
                    sprintf(
                        'Dimensions mode for indexer "%s" was changed from \'%s\' to \'%s\'',
                        'indexer_title',
                        'none',
                        'store'
                    ) . PHP_EOL
                ,
            ],
            'was_not_changed' => [
                'indexer_title' => 'indexer_title',
                'previousMode'  => 'none',
                'command'       => [
                    'indexer' => 'indexer_title',
                    'mode'    => 'none',
                ],
                'output'        =>
                    sprintf(
                        'Dimensions mode for indexer "%s" has not been changed',
                        'indexer_title'
                    ) . PHP_EOL
                ,
            ],
        ];
    }

    /**
     * Tests indexer exception of method \Magento\Indexer\Console\Command\IndexerDimensionsModeCommand::execute
     *
     * @return void
     */
    public function testExecuteWithIndxerException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid value for "<indexer>" argument. Accepted values for "<indexer>" are \'indexer_title\''
        );

        $commandTester = new CommandTester($this->command);
        $this->indexerMock->method('getTitle')->willReturn('indexer_title');
        $commandTester->execute(['indexer' => 'non_existing_title']);
    }

    /**
     * Tests indexer exception of method \Magento\Indexer\Console\Command\IndexerDimensionsModeCommand::execute
     *
     * @return void
     */
    public function testExecuteWithModeException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing argument "<mode>". Accepted values for "<mode>" are \'store,website\'');

        $commandTester = new CommandTester($this->command);
        $this->dimensionModes->method('getDimensions')->willReturn([
            'store'   => 'dimension1',
            'website' => 'dimension2',
        ]);
        $this->dimensionModeSwitcherMock->method('getDimensionModes')->willReturn($this->dimensionModes);
        $this->indexerMock->method('getTitle')->willReturn('indexer_title');
        $commandTester->execute([
            'indexer' => 'indexer_title',
        ]);
    }

    /**
     * Test execution of command without any arguments
     *
     * @return void
     */
    public function testExecuteWithNoArguments()
    {
        $indexerTitle = 'indexer_title';
        $modesConfig = [
            'store'   => 'dimension1',
            'website' => 'dimension2',
        ];
        $this->configureAdminArea();
        $commandTester = new CommandTester($this->command);
        $this->indexerMock->method('getTitle')->willReturn($indexerTitle);
        $this->dimensionModes->method('getDimensions')->willReturn($modesConfig);
        $this->dimensionModeSwitcherMock->method('getDimensionModes')->willReturn($this->dimensionModes);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $consoleOutput = sprintf('%-50s', 'Indexer') . 'Available modes' . PHP_EOL
            . sprintf('%-50s', $indexerTitle) . 'store,website' . PHP_EOL;
        $this->assertEquals(
            $consoleOutput,
            $actualValue
        );
    }
}
