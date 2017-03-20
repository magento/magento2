<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Symfony\Component\Console\Tester\CommandTester;

class DependenciesShowModulesCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DependenciesShowModulesCommand
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

        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $objectManager = $this->getMock(\Magento\Framework\App\ObjectManager::class, [], [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);

        $themePackageListMock = $this->getMock(
            \Magento\Framework\View\Design\Theme\ThemePackageList::class,
            [],
            [],
            '',
            false
        );
        $componentRegistrarMock = $this->getMock(
            \Magento\Framework\Component\ComponentRegistrar::class,
            [],
            [],
            '',
            false
        );
        $componentRegistrarMock->expects($this->any())->method('getPaths')->will($this->returnValue($modules));
        $dirSearchMock = $this->getMock(\Magento\Framework\Component\DirSearch::class, [], [], '', false);
        $objectManager->expects($this->any())->method('get')->will($this->returnValueMap([
            [\Magento\Framework\View\Design\Theme\ThemePackageList::class, $themePackageListMock],
            [\Magento\Framework\Component\ComponentRegistrar::class, $componentRegistrarMock],
            [\Magento\Framework\Component\DirSearch::class, $dirSearchMock]
        ]));

        $this->command = new DependenciesShowModulesCommand($objectManagerProvider);
        $this->commandTester = new CommandTester($this->command);
    }

    public function tearDown()
    {
        if (file_exists(__DIR__ . '/_files/output/modules.csv')) {
            unlink(__DIR__ . '/_files/output/modules.csv');
        }
    }

    public function testExecute()
    {
        $this->commandTester->execute(
            ['--output' => __DIR__ . '/_files/output/modules.csv']
        );
        $this->assertEquals('Report successfully processed.' . PHP_EOL, $this->commandTester->getDisplay());
        $fileContents = file_get_contents(__DIR__ . '/_files/output/modules.csv');
        $this->assertContains(
            ',All,Hard,Soft' . PHP_EOL . '"Total number of dependencies",2,2,0' . PHP_EOL,
            $fileContents
        );
        $this->assertContains('"Dependencies for each module:",All,Hard,Soft'. PHP_EOL, $fileContents);
        $this->assertContains(
            'magento/module-a,1,1,0' . PHP_EOL . '" -- magento/module-b",,1,0' . PHP_EOL,
            $fileContents
        );
        $this->assertContains(
            'magento/module-b,1,1,0' . PHP_EOL . '" -- magento/module-a",,1,0' . PHP_EOL,
            $fileContents
        );
    }
}
