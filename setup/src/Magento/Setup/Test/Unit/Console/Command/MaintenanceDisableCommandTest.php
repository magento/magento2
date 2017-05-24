<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\MaintenanceDisableCommand;
use Magento\Setup\Validator\IpValidator;
use Symfony\Component\Console\Tester\CommandTester;

class MaintenanceDisableCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * @var IpValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ipValidator;

    /**
     * @var MaintenanceDisableCommand
     */
    private $command;

    public function setUp()
    {
        $this->maintenanceMode = $this->getMock(\Magento\Framework\App\MaintenanceMode::class, [], [], '', false);
        $this->ipValidator = $this->getMock(\Magento\Setup\Validator\IpValidator::class, [], [], '', false);
        $this->command = new MaintenanceDisableCommand($this->maintenanceMode, $this->ipValidator);
    }

    /**
     * @param array $input
     * @param array $validatorMessages
     * @param string $expectedMessage
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $input, array $validatorMessages, $expectedMessage)
    {
        $return = isset($input['--ip']) ? ($input['--ip'] !== ['none'] ? $input['--ip'] : []) : [];
        $this->maintenanceMode
            ->expects($this->any())
            ->method('getAddressInfo')
            ->willReturn($return);
        $this->ipValidator->expects($this->once())->method('validateIps')->willReturn($validatorMessages);
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
                [],
                'Disabled maintenance mode' . PHP_EOL .
                'Set exempt IP-addresses: 127.0.0.1, 127.0.0.2' . PHP_EOL
            ],
            [
                ['--ip' => ['none']],
                [],
                'Disabled maintenance mode' . PHP_EOL .
                'Set exempt IP-addresses: none' . PHP_EOL
            ],
            [
                [],
                [],
                'Disabled maintenance mode' . PHP_EOL
            ],
            [
                ['--ip' => ['127.0']],
                ['Invalid IP 127.0'],
                'Invalid IP 127.0' . PHP_EOL
            ],
        ];
    }

    /**
     * @dataProvider isSetAddressInfoDataProvider
     * @param array $ip
     * @param bool $expected
     */
    public function testIsSetAddressInfo($ip, $expected)
    {
        $this->maintenanceMode
            ->expects($this->any())
            ->method('getAddressInfo')
            ->willReturn($ip);

        $this->assertEquals($expected, $this->command->isSetAddressInfo());
    }

    /**
     * return array
     */
    public function isSetAddressInfoDataProvider()
    {
        return [
            [
                'ip' => ['127.0.0.1', '127.0.0.2'],
                'expected' => true
            ],
            [
                'ip' => [],
                'expected' => false
            ],
        ];
    }
}
