<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\UpgradeCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Magento\Framework\Console\Cli;

class UpgradeCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $options
     * @param string $expectedString
     * @dataProvider executeDataProvider
     */
    public function testExecute($options = [], $expectedString = '')
    {
        $installerFactory = $this->getMock(\Magento\Setup\Model\InstallerFactory::class, [], [], '', false);
        $installer = $this->getMock(\Magento\Setup\Model\Installer::class, [], [], '', false);
        $installer->expects($this->at(0))->method('updateModulesSequence');
        $installer->expects($this->at(1))->method('installSchema');
        $installer->expects($this->at(2))->method('installDataFixtures');
        $installerFactory->expects($this->once())->method('create')->willReturn($installer);
        $commandTester = new CommandTester(new UpgradeCommand($installerFactory));
        $this->assertSame(Cli::RETURN_SUCCESS, $commandTester->execute($options));
        $this->assertEquals($expectedString, $commandTester->getDisplay());
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                'options' => [],
                'expectedString' => 'Please re-run Magento compile command. Use the command "setup:di:compile"'
                    . PHP_EOL
            ],
            [
                'options' => ['--keep-generated' => true],
                'expectedString' => ''
            ],
        ];
    }
}
