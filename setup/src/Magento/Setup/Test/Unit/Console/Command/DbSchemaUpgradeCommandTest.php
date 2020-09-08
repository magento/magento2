<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Console\Command\DbSchemaUpgradeCommand;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DbSchemaUpgradeCommandTest extends TestCase
{
    /**
     * @var InstallerFactory|MockObject
     */
    private $installerFactory;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfig;

    protected function setUp(): void
    {
        $this->installerFactory = $this->createMock(InstallerFactory::class);
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
    }

    /**
     * @dataProvider executeDataProvider
     * @param $options
     * @param $expectedOptions
     */
    public function testExecute($options, $expectedOptions)
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $installer = $this->createMock(Installer::class);
        $this->installerFactory->expects($this->once())->method('create')->willReturn($installer);
        $installer
            ->expects($this->once())
            ->method('installSchema')
            ->with($expectedOptions);

        $commandTester = new CommandTester(
            new DbSchemaUpgradeCommand($this->installerFactory, $this->deploymentConfig)
        );
        $commandTester->execute($options);
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
                    '--convert-old-scripts' => false
                ],
                'expectedOptions' => [
                    'convert-old-scripts' => false,
                    'magento-init-params' => '',
                ]
            ],
        ];
    }

    public function testExecuteNoConfig()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $this->installerFactory->expects($this->never())->method('create');

        $commandTester = new CommandTester(
            new DbSchemaUpgradeCommand($this->installerFactory, $this->deploymentConfig)
        );
        $commandTester->execute([]);
        $this->assertStringMatchesFormat(
            'No information is available: the Magento application is not installed.%w',
            $commandTester->getDisplay()
        );
    }
}
