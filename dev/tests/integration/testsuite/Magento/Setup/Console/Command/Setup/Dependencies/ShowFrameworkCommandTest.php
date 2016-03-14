<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command\Setup\Dependencies;

use Symfony\Component\Console\Tester\CommandTester;

class ShowFrameworkCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShowFrameworkCommand
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
        $dirSearchMock->expects($this->once())->method('collectFiles')->willReturn(
            [
                BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/root/app/code/Magento/A/etc/module.xml',
                BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/root/app/code/Magento/B/etc/module.xml'
            ]
        );
        $objectManager->expects($this->any())->method('get')->will($this->returnValueMap([
            ['Magento\Framework\View\Design\Theme\ThemePackageList', $themePackageListMock],
            ['Magento\Framework\Component\ComponentRegistrar', $componentRegistrarMock],
            ['Magento\Framework\Component\DirSearch', $dirSearchMock]
        ]));

        $this->command = new ShowFrameworkCommand($componentRegistrarMock, $objectManagerProvider);
        $this->commandTester = new CommandTester($this->command);
    }

    public function tearDown()
    {
        if (file_exists(BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/framework.csv')) {
            unlink(BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/framework.csv');
        }
    }

    public function testExecute()
    {
        $this->commandTester->execute(
            ['--output' => BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/framework.csv']
        );
        $this->assertEquals('Report successfully processed.' . PHP_EOL, $this->commandTester->getDisplay());
        $fileContents = file_get_contents(BP . '/dev/tests/integration/testsuite/Magento/Setup/Console/Command/_files/output/framework.csv');
        $this->assertContains(
            '"Dependencies of framework:","Total number"' . PHP_EOL . ',2' . PHP_EOL,
            $fileContents
        );
        $this->assertContains('"Dependencies for each module:",' . PHP_EOL, $fileContents);
        $this->assertContains('"Magento\A",1' . PHP_EOL . '" -- Magento\Framework",3' . PHP_EOL, $fileContents);
        $this->assertContains('"Magento\B",1' . PHP_EOL . '" -- Magento\Framework",3' . PHP_EOL, $fileContents);

    }
}
