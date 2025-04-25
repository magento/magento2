<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\MaintenanceEnableCommand;
use Magento\Framework\App\MaintenanceMode;
use Magento\Backend\Model\Validator\IpValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class MaintenanceEnableCommandTest extends TestCase
{
    /**
     * @var MaintenanceMode|MockObject
     */
    private $maintenanceMode;

    /**
     * @var IpValidator|MockObject
     */
    private $ipValidator;

    /**
     * @var MaintenanceEnableCommand
     */
    private $command;

    protected function setUp(): void
    {
        $this->maintenanceMode = $this->createMock(MaintenanceMode::class);
        $this->ipValidator = $this->createMock(IpValidator::class);
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
    public static function executeDataProvider()
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
