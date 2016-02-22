<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Symfony\Component\Console\Tester\CommandTester;

class DependenciesShowModulesCircularCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DependenciesShowModulesCircularCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

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
        $objectManager->expects($this->any())->method('get')->will($this->returnValueMap([
            ['Magento\Framework\View\Design\Theme\ThemePackageList', $themePackageListMock],
            ['Magento\Framework\Component\ComponentRegistrar', $componentRegistrarMock],
            ['Magento\Framework\Component\DirSearch', $dirSearchMock]
        ]));

        $this->command = new DependenciesShowModulesCircularCommand($objectManagerProvider);
        $this->commandTester = new CommandTester($this->command);
    }

    public function tearDown()
    {
        if (file_exists(__DIR__ . '/_files/output/circular.csv')) {
            unlink(__DIR__ . '/_files/output/circular.csv');
        }
    }

    public function testExecute()
    {
        $this->commandTester->execute(
            ['--output' => __DIR__ . '/_files/output/circular.csv']
        );
        $this->assertEquals('Report successfully processed.' . PHP_EOL, $this->commandTester->getDisplay());
        $fileContents = file_get_contents(__DIR__ . '/_files/output/circular.csv');
        $this->assertContains(
            '"Circular dependencies:","Total number of chains"' . PHP_EOL . ',2' . PHP_EOL,
            $fileContents
        );
        $this->assertContains('"Circular dependencies for each module:",' . PHP_EOL, $fileContents);
        $this->assertContains(
            'magento/module-a,1' . PHP_EOL . 'magento/module-a->magento/module-b->magento/module-a' . PHP_EOL,
            $fileContents
        );
        $this->assertContains(
            'magento/module-b,1' . PHP_EOL . 'magento/module-b->magento/module-a->magento/module-b' . PHP_EOL,
            $fileContents
        );
    }
}
