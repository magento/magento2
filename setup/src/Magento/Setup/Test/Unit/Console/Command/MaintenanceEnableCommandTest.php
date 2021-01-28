<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\MaintenanceEnableCommand;
use Magento\Setup\Validator\IpValidator;
use Symfony\Component\Console\Tester\CommandTester;

class MaintenanceEnableCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit\Framework\MockObject\MockObject
     */
    private $maintenanceMode;

    /**
     * @var IpValidator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $ipValidator;

    /**
     * @var MaintenanceEnableCommand
     */
    private $command;

    protected function setUp(): void
    {
        $this->maintenanceMode = $this->createMock(\Magento\Framework\App\MaintenanceMode::class);
        $this->ipValidator = $this->createMock(\Magento\Setup\Validator\IpValidator::class);
        $this->command = new MaintenanceEnableCommand($this->maintenanceMode, $this->ipValidator);
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
                'Enabled maintenance mode' . PHP_EOL .
                'Set exempt IP-addresses: 127.0.0.1, 127.0.0.2' . PHP_EOL
            ],
            [
                ['--ip' => ['none']],
                [],
                'Enabled maintenance mode' . PHP_EOL .
                'Set exempt IP-addresses: none' . PHP_EOL
            ],
            [
                [],
                [],
                'Enabled maintenance mode' . PHP_EOL
            ],
            [
                ['--ip' => ['127.0']],
                ['Invalid IP 127.0'],
                'Invalid IP 127.0' . PHP_EOL
            ],
        ];
    }
}
