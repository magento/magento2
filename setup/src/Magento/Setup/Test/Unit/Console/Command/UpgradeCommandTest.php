<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Console\Cli;
use Magento\Setup\Console\Command\UpgradeCommand;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Setup\Model\SearchConfig;
use Magento\Setup\Model\SearchConfigFactory;
use Symfony\Component\Console\Tester\CommandTester;

class UpgradeCommandTest extends TestCase
{
    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var InstallerFactory|MockObject
     */
    private $installerFactoryMock;

    /**
     * @var Installer|MockObject
     */
    private $installerMock;

    /**
     * @var AppState|MockObject
     */
    private $appStateMock;

    /**
     * @var SearchConfig|MockObject
     */
    private $searchConfigMock;

    /**
     * @var UpgradeCommand
     */
    private $upgradeCommand;
    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->installerFactoryMock = $this->getMockBuilder(InstallerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->installerMock = $this->getMockBuilder(Installer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->installerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->installerMock);
        $this->appStateMock = $this->getMockBuilder(AppState::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchConfigMock = $this->getMockBuilder(SearchConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|SearchConfigFactory $searchConfigFactoryMock */
        $searchConfigFactoryMock = $this->getMockBuilder(SearchConfigFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $searchConfigFactoryMock->expects($this->once())->method('create')->willReturn($this->searchConfigMock);

        $this->upgradeCommand = new UpgradeCommand(
            $this->installerFactoryMock,
            $searchConfigFactoryMock,
            $this->deploymentConfigMock,
            $this->appStateMock
        );
        $this->commandTester = new CommandTester($this->upgradeCommand);
    }

    /**
     * @dataProvider executeDataProvider
     * @param array $options
     * @param string $deployMode
     * @param string $expectedString
     * @param array $expectedOptions
     */
    public function testExecute($options, $deployMode, $expectedString, $expectedOptions)
    {
        $this->appStateMock->method('getMode')->willReturn($deployMode);
        $this->installerMock->expects($this->at(0))
            ->method('updateModulesSequence');
        $this->installerMock->expects($this->once())
            ->method('installSchema')
            ->with($expectedOptions);
        $this->installerMock->expects($this->at(2))
            ->method('installDataFixtures');

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute($options));
        $this->assertEquals($expectedString, $this->commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                'options' => [
                    '--magento-init-params' => '',
                    '--convert-old-scripts' => false,
                ],
                'deployMode' => \Magento\Framework\App\State::MODE_PRODUCTION,
                'expectedString' => 'Please re-run Magento compile command. Use the command "setup:di:compile"'
                    . PHP_EOL,
                'expectedOptions' => [
                    'keep-generated' => false,
                    'convert-old-scripts' => false,
                    'safe-mode' => false,
                    'data-restore' => false,
                    'dry-run' => false,
                    'magento-init-params' => '',
                ]
            ],
            [
                'options' => [
                    '--magento-init-params' => '',
                    '--convert-old-scripts' => false,
                    '--keep-generated' => true,
                ],
                'deployMode' => \Magento\Framework\App\State::MODE_PRODUCTION,
                'expectedString' => '',
                'expectedOptions' => [
                    'keep-generated' => true,
                    'convert-old-scripts' => false,
                    'safe-mode' => false,
                    'data-restore' => false,
                    'dry-run' => false,
                    'magento-init-params' => '',
                ]
            ],
            [
                'options' => ['--magento-init-params' => '', '--convert-old-scripts' => false],
                'deployMode' => \Magento\Framework\App\State::MODE_DEVELOPER,
                'expectedString' => '',
                'expectedOptions' => [
                    'keep-generated' => false,
                    'convert-old-scripts' => false,
                    'safe-mode' => false,
                    'data-restore' => false,
                    'dry-run' => false,
                    'magento-init-params' => '',
                ]
            ],
            [
                'options' => ['--magento-init-params' => '', '--convert-old-scripts' => false],
                'deployMode' => \Magento\Framework\App\State::MODE_DEFAULT,
                'expectedString' => '',
                'expectedOptions' => [
                    'keep-generated' => false,
                    'convert-old-scripts' => false,
                    'safe-mode' => false,
                    'data-restore' => false,
                    'dry-run' => false,
                    'magento-init-params' => '',
                ]
            ],
        ];
    }
}
