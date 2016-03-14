<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command\Setup\Dependencies;

use Magento\Framework\Component\ComponentRegistrar;
use Symfony\Component\Console\Tester\CommandTester;

class ShowModulesCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShowModulesCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $commandTester;

    public function setUp()
    {

        $modules = [
            'Magento_A' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/root/app/code/Magento/A',
            'Magento_B' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/root/app/code/Magento/B'
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

        $this->command = new ShowModulesCommand($objectManagerProvider);
        $this->commandTester = new CommandTester($this->command);
    }

    public function tearDown()
    {
        if (file_exists(BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/modules.csv')) {
            unlink(BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/modules.csv');
        }
    }

    public function testExecute()
    {
        $this->commandTester->execute(
            ['--output' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/modules.csv']
        );
        $this->assertEquals('Report successfully processed.' . PHP_EOL, $this->commandTester->getDisplay());
        $fileContents = file_get_contents(BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/modules.csv');
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
