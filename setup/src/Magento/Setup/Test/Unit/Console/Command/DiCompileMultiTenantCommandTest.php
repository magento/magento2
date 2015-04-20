<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\DiCompileMultiTenantCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DiCompileMultiTenantCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfig;

    /**
     * @var \Magento\Setup\Model\ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var DiCompileMultiTenantCommand
     */
    private $command;

    public function setUp()
    {
        $this->deploymentConfig = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->objectManagerProvider = $this->getMock(
            'Magento\Setup\Model\ObjectManagerProvider',
            [],
            [],
            '',
            false
        );
        $this->objectManager = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false
        );
        $this->objectManagerProvider->expects($this->once())->method('get')->willReturn($this->objectManager);
        $this->command = new DiCompileMultiTenantCommand($this->objectManagerProvider, $this->deploymentConfig);
    }

    public function testExecuteNotInstalled()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(false);
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertEquals('You cannot run this command as Magento application is not installed.'
            . PHP_EOL, $tester->getDisplay());
    }

    public function testExecute()
    {
        $this->deploymentConfig->expects($this->once())->method('isAvailable')->willReturn(true);
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertStringEndsWith(
            'On *nix systems, verify the Magento application has permissions to modify files '
            . 'created by the compiler in the "var" directory. For instance, if you run the Magento application using '
            . 'Apache, the owner of the files in the "var" directory should be the Apache user (example command:'
            . ' "chown -R www-data:www-data <MAGENTO_ROOT>/var" where MAGENTO_ROOT is the Magento root directory).'
            . PHP_EOL,
            $tester->getDisplay()
        );
    }
}
