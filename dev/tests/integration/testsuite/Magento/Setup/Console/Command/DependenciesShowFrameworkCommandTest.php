<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Symfony\Component\Console\Tester\CommandTester;

class DependenciesShowFrameworkCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DependenciesShowFrameworkCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var array
     */
    private $backupRegistrar;

    public function setUp()
    {
        $modules = [
            'Magento_A' => __DIR__ . '/_files/root/app/code/Magento/A',
            'Magento_B' => __DIR__ . '/_files/root/app/code/Magento/B'
        ];
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $objectManager = $this->getMock('\Magento\Framework\App\ObjectManager', [], [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $themePackageListMock = $this->getMock(
            'Magento\Framework\View\Design\Theme\ThemePackageList',
            [],
            [],
            '',
            false
        );
        $componentRegistrarMock = $this->getMock('Magento\Framework\Component\ComponentRegistrar', [], [], '', false);
        $componentRegistrarMock->expects($this->any())->method('getPaths')->will($this->returnValue($modules));
        $dirSearchMock = $this->getMock('Magento\Framework\Component\DirSearch', [], [], '', false);
        $dirSearchMock->expects($this->once())->method('collectFiles')->willReturn(
            [
                __DIR__ . '/_files/root/app/code/Magento/A/etc/module.xml',
                __DIR__ . '/_files/root/app/code/Magento/B/etc/module.xml'
            ]
        );
        $objectManager->expects($this->any())->method('get')->will($this->returnValueMap([
            ['Magento\Framework\View\Design\Theme\ThemePackageList', $themePackageListMock],
            ['Magento\Framework\Component\ComponentRegistrar', $componentRegistrarMock],
            ['Magento\Framework\Component\DirSearch', $dirSearchMock]
        ]));

        $this->command = new DependenciesShowFrameworkCommand(new ComponentRegistrar(), $objectManagerProvider);
        $this->commandTester = new CommandTester($this->command);
        $reflection = new \ReflectionClass('Magento\Framework\Component\ComponentRegistrar');
        $paths = $reflection->getProperty('paths');
        $paths->setAccessible(true);
        $this->backupRegistrar = $paths->getValue();
        $paths->setValue(
            [
                ComponentRegistrar::MODULE => $modules
            ]
        );
        $paths->setAccessible(false);
    }

    public function tearDown()
    {
        if (file_exists(__DIR__ . '/_files/output/framework.csv')) {
            unlink(__DIR__ . '/_files/output/framework.csv');
        }
        $reflection = new \ReflectionClass('Magento\Framework\Component\ComponentRegistrar');
        $paths = $reflection->getProperty('paths');
        $paths->setAccessible(true);
        $paths->setValue($this->backupRegistrar);
        $paths->setAccessible(false);
    }

    public function testExecute()
    {
        $this->commandTester->execute(
            ['--output' => __DIR__ . '/_files/output/framework.csv']
        );
        $this->assertEquals('Report successfully processed.' . PHP_EOL, $this->commandTester->getDisplay());
        $fileContents = file_get_contents(__DIR__ . '/_files/output/framework.csv');
        $this->assertContains(
            '"Dependencies of framework:","Total number"' . PHP_EOL . ',2' . PHP_EOL,
            $fileContents
        );
        $this->assertContains('"Dependencies for each module:",' . PHP_EOL, $fileContents);
        $this->assertContains('"Magento\A",1' . PHP_EOL . '" -- Magento\Framework",1' . PHP_EOL, $fileContents);
        $this->assertContains('"Magento\B",1' . PHP_EOL . '" -- Magento\Framework",1' . PHP_EOL, $fileContents);

    }
}
