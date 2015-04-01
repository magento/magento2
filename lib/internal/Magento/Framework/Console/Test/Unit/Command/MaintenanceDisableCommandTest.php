<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Console\Test\Unit\Command;

use Magento\Framework\Console\Command\MaintenanceDisableCommand;
use Symfony\Component\Console\Tester\CommandTester;

class MaintenanceDisableCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * @var MaintenanceDisableCommand
     */
    private $command;

    public function setUp()
    {
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $this->command = new MaintenanceDisableCommand($this->maintenanceMode);
    }

    /**
     * @param array $input
     * @param string $expectedMessage
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $input, $expectedMessage)
    {
        $return = isset($input['--ip']) ? ($input['--ip'] !== ['none'] ? $input['--ip'] : []) : [];
        $this->maintenanceMode
            ->expects($this->any())
            ->method('getAddressInfo')
            ->willReturn($return);
        $tester = new CommandTester($this->command);
        $tester->execute($input);
        $this->assertEquals($expectedMessage, $tester->getDisplay());
    }

    /**
     * return array
     */
    public function executeDataProvider()
    {
        return [
            [
                ['--ip' => ['127.0.0.1', '127.0.0.2']],
                "Disabled maintenance mode\n" .
                "Set exempt IP-addresses: 127.0.0.1, 127.0.0.2\n"
            ],
            [
                [],
                "Disabled maintenance mode\n"
            ],
            [
                ['--ip' => ['none']],
                "Disabled maintenance mode\n" .
                "Set exempt IP-addresses: none\n"
            ],
        ];
    }
}
