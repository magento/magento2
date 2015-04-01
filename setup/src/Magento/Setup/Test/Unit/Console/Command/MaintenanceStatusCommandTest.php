<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Test\Unit\Command;

use Magento\Setup\Console\Command\MaintenanceStatusCommand;
use Symfony\Component\Console\Tester\CommandTester;

class MaintenanceAllowIpsCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * @var MaintenanceStatusCommand
     */
    private $command;

    public function setUp()
    {
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $this->command = new MaintenanceStatusCommand($this->maintenanceMode);
    }

    /**
     * @param array $maintenanceData
     * @param string $expectedMessage
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $maintenanceData, $expectedMessage)
    {
        $this->maintenanceMode->expects($this->once())->method('isOn')->willReturn($maintenanceData[0]);
        $this->maintenanceMode->expects($this->once())->method('getAddressInfo')->willReturn($maintenanceData[1]);
        $tester = new CommandTester($this->command);
        $tester->execute([]);
        $this->assertEquals($expectedMessage, $tester->getDisplay());

    }

    /**
     * return array
     */
    public function executeDataProvider()
    {
        return [
            [
                [true, ['127.0.0.1', '127.0.0.2']],
                "Status: maintenance mode is active\nList of exempt IP-addresses: 127.0.0.1, 127.0.0.2\n"
            ],
            [
                [true, []],
                "Status: maintenance mode is active\n"
            ],
            [
                [false, []],
                "Status: maintenance mode is not active\n"
            ],
            [
                [false, ['127.0.0.1', '127.0.0.2']],
                "Status: maintenance mode is not active\nList of exempt IP-addresses: 127.0.0.1, 127.0.0.2\n"
            ],
        ];
    }
}
