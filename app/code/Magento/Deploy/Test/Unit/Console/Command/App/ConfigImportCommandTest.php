<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command\App;

use Magento\Deploy\Console\Command\App\ConfigImportCommand;
use Magento\Framework\App\DeploymentConfig\ConfigHashManager;
use Magento\Framework\App\DeploymentConfig\ConfigImporterPool;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;

class ConfigImportCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigHashManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHashManagerMock;

    /**
     * @var ConfigImporterPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configImporterPoolMock;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configHashManagerMock = $this->getMockBuilder(ConfigHashManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configImporterPoolMock = $this->getMockBuilder(ConfigImporterPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configImportCommand = new ConfigImportCommand(
            $this->configHashManagerMock,
            $this->configImporterPoolMock,
            $this->deploymentConfigMock
        );

        $this->commandTester = new CommandTester($configImportCommand);
    }

    /**
     * @param array $importers
     * @param bool $isHashValid
     * @return void
     * @dataProvider executeNothingImportDataProvider
     */
    public function testExecuteNothingImport($importers, $isHashValid)
    {
        $this->commandTester->execute([]);
        $this->configImporterPoolMock->expects($this->once())
            ->method('getImporters')
            ->willReturn($importers);
        $this->configHashManagerMock->expects($this->any())
            ->method('isHashValid')
            ->willReturn($isHashValid);

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute([]));
        $this->assertContains('Start import:', $this->commandTester->getDisplay());
        $this->assertContains('Nothing to import', $this->commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public function executeNothingImportDataProvider()
    {
        return [
            [
                'importers' => [],
                'isHashValid' => true,
            ],
            [
                'importers' => [],
                'isHashValid' => false,
            ],
            [
                'importers' => ['test' => 'test'],
                'isHashValid' => true,
            ],
        ];
    }

    /**
     * @return void
     */
    public function testExecuteWithImport()
    {
        $section = 'testSection';
        $data = ['someField' => 'some data'];
        $messages = ['First message', 'Second message'];

        /** @var ImporterInterface|\PHPUnit_Framework_MockObject_MockObject $importerMock */
        $importerMock = $this->getMockBuilder(ImporterInterface::class)
            ->getMockForAbstractClass();
        $importerMock->expects($this->once())
            ->method('import')
            ->with($data)
            ->willReturn($messages);

        $this->commandTester->execute([]);
        $this->configImporterPoolMock->expects($this->once())
            ->method('getImporters')
            ->willReturn([$section => $importerMock]);
        $this->configHashManagerMock->expects($this->once())
            ->method('isHashValid')
            ->willReturn(false);
        $this->configHashManagerMock->expects($this->once())
            ->method('generateHash');
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with($section)
            ->willReturn($data);

        $this->assertSame(Cli::RETURN_SUCCESS, $this->commandTester->execute([]));
        $this->assertContains('Start import:', $this->commandTester->getDisplay());
        $this->assertContains('First message', $this->commandTester->getDisplay());
        $this->assertContains('Second message', $this->commandTester->getDisplay());
    }
}
