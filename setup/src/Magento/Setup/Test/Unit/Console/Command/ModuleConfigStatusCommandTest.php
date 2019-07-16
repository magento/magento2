<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Setup\Console\Command\ModuleConfigStatusCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tests for module config status command.
 */
class ModuleConfigStatusCommandTest extends TestCase
{
    /**
     * @param array $currentConfig
     * @param array $correctConfig
     * @param string $expectedOutput
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $currentConfig, array $correctConfig, string $expectedOutput)
    {
        $configReader = $this->createMock(\Magento\Framework\App\DeploymentConfig\Reader::class);
        $configReader->expects($this->once())
                     ->method('load')
                     ->willReturn([ConfigOptionsListConstants::KEY_MODULES => $currentConfig]);

        $installer = $this->createMock(\Magento\Setup\Model\Installer::class);
        $installer->expects($this->once())
                  ->method('getModulesConfig')
                  ->willReturn($correctConfig);

        $installerFactory = $this->createMock(\Magento\Setup\Model\InstallerFactory::class);
        $installerFactory->expects($this->once())
                         ->method('create')
                         ->willReturn($installer);

        $command = new ModuleConfigStatusCommand($configReader, $installerFactory);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->assertEquals($expectedOutput, $tester->getDisplay());
    }

    public function executeDataProvider()
    {
        $successMessage = 'The modules configuration is up to date.' . PHP_EOL;
        $failureMessage = 'The modules configuration in the \'app/etc/config.php\' '
            . 'file is outdated. Run \'setup:upgrade\' to fix it.' . PHP_EOL;

        return [
            [
                ['Magento_ModuleA' => 1, 'Magento_ModuleB' => 1],
                ['Magento_ModuleA' => 1, 'Magento_ModuleB' => 1],
                $successMessage,
            ],
            [
                ['Magento_ModuleA' => 0, 'Magento_ModuleB' => 1],
                ['Magento_ModuleA' => 0, 'Magento_ModuleB' => 1],
                $successMessage,
            ],
            [
                ['Magento_ModuleA' => 1, 'Magento_ModuleB' => 1],
                ['Magento_ModuleB' => 1, 'Magento_ModuleA' => 1],
                $failureMessage,
            ],
            [
                ['Magento_ModuleA' => 0, 'Magento_ModuleB' => 1],
                ['Magento_ModuleB' => 1, 'Magento_ModuleA' => 0],
                $failureMessage,
            ],
            [
                ['Magento_ModuleA' => 1],
                ['Magento_ModuleB' => 1, 'Magento_ModuleA' => 1],
                $failureMessage,
            ],
            [
                ['Magento_ModuleA' => 1, 'Magento_ModuleB' => 1],
                ['Magento_ModuleB' => 1],
                $failureMessage,
            ],
        ];
    }
}
