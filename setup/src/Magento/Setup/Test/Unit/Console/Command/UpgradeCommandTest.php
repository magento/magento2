<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Setup\Console\Command\UpgradeCommand;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use Symfony\Component\Console\Tester\CommandTester;

class UpgradeCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var InstallerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $installerFactoryMock;

    /**
     * @var Installer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $installerMock;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

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
    protected function setUp()
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
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->upgradeCommand = new UpgradeCommand(
            $this->installerFactoryMock,
            $this->deploymentConfigMock,
            $this->stateMock
        );

        $this->commandTester = new CommandTester($this->upgradeCommand);
    }

    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute($options, $expectedString = '', $state = 'developer')
    {
        $this->installerMock->expects($this->at(0))
            ->method('updateModulesSequence');
        $this->installerMock->expects($this->at(1))
            ->method('installSchema');
        $this->installerMock->expects($this->at(2))
            ->method('installDataFixtures');

        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($state);

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute($options));
        $this->assertEquals($expectedString, $this->commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        $rerunDiCompileString = 'Please re-run Magento compile command. Use the command "setup:di:compile"' . PHP_EOL;

        return [
            // "developer" & don't keep generated
            [
                'options'        => [],
                'expectedString' => '',
                'state'          => State::MODE_DEVELOPER,
            ],
            // "developer" & keep generated
            [
                'options'        => ['--keep-generated' => true],
                'expectedString' => '',
                'state'          => State::MODE_DEVELOPER,
            ],
            // not "developer" & don't keep generated
            [
                'options'        => [],
                'expectedString' => $rerunDiCompileString,
                'state'          => 'not_developer',
            ],
            // not "developer" & keep generated
            [
                'options'        => ['--keep-generated' => true],
                'expectedString' => '',
                'state'          => 'not_developer',
            ],
        ];
    }
}
