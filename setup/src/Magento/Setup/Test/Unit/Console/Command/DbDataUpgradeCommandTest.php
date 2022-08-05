<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Console\Command\DbDataUpgradeCommand;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DbDataUpgradeCommandTest extends TestCase
{
    /**
     * @var InstallerFactory|MockObject
     */
    protected $installerFactory;

    /**
     * @var DeploymentConfig|MockObject
     */
    protected $deploymentConfig;

    protected function setup(): void
    {
        $this->installerFactory = $this->createMock(InstallerFactory::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
    }

    public function testExecute()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $installer = $this->createMock(Installer::class);
        $this->installerFactory->expects($this->once())->method('create')->willReturn($installer);
        $installer->expects($this->once())->method('installDataFixtures');

        $commandTester = new CommandTester(
            new DbDataUpgradeCommand($this->installerFactory, $this->deploymentConfig)
        );
        $commandTester->execute([]);
    }

    public function testExecuteNoConfig()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->installerFactory->expects($this->never())->method('create');

        $commandTester = new CommandTester(
            new DbDataUpgradeCommand($this->installerFactory, $this->deploymentConfig)
        );
        $commandTester->execute([]);
        $this->assertStringMatchesFormat(
            'No information is available: the Magento application is not installed.%w',
            $commandTester->getDisplay()
        );
    }
}
