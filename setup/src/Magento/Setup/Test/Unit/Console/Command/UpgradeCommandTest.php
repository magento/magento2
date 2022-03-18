<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Console\Cli;
use Magento\Setup\Console\Command\UpgradeCommand;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use Symfony\Component\Console\Tester\CommandTester;

class UpgradeCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeploymentConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var InstallerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $installerFactoryMock;

    /**
     * @var Installer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $installerMock;

    /**
     * @var AppState|\PHPUnit\Framework\MockObject\MockObject
     */
    private $appStateMock;

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

        $this->upgradeCommand = new UpgradeCommand(
            $this->installerFactoryMock,
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
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString($expectedString, $display);
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
