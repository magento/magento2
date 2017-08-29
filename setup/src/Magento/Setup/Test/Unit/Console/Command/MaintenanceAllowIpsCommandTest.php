<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\MaintenanceAllowIpsCommand;
use Magento\Setup\Validator\IpValidator;
use Symfony\Component\Console\Tester\CommandTester;

class MaintenanceAllowIpsCommandTest extends \PHPUnit\Framework\TestCase
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
     * @var MaintenanceAllowIpsCommand
     */
    private $command;

    public function setUp()
    {
        $this->maintenanceMode = $this->createMock(\Magento\Framework\App\MaintenanceMode::class);
        $this->ipValidator = $this->createMock(\Magento\Setup\Validator\IpValidator::class);
        $this->command = new MaintenanceAllowIpsCommand($this->maintenanceMode, $this->ipValidator);
    }

    /**
     * @param array $input
     * @param array $validatorMessages
     * @param string $expectedMessage
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $input, array $validatorMessages, $expectedMessage)
    {
        if (isset($input['--none']) && !$input['--none'] && isset($input['ip'])) {
            $this->ipValidator->expects($this->once())->method('validateIps')->willReturn($validatorMessages);
            if (empty($validatorMessages) && !empty($input['ip'])) {
                $this->maintenanceMode
                    ->expects($this->once())
                    ->method('setAddresses')
                    ->with(implode(',', $input['ip']));
                $this->maintenanceMode
                    ->expects($this->once())
                    ->method('getAddressInfo')
                    ->willReturn($input['ip']);
            }
        } elseif (isset($input['--none']) && $input['--none']) {
            $this->ipValidator->expects($this->never())->method('validateIps')->willReturn($validatorMessages);
            $this->maintenanceMode
                ->expects($this->once())
                ->method('setAddresses')
                ->with('');
        }
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
                ['ip' => ['127.0.0.1', '127.0.0.2'], '--none' => false],
                [],
                'Set exempt IP-addresses: 127.0.0.1, 127.0.0.2' . PHP_EOL
            ],
            [
                ['--none' => true],
                [],
                'Set exempt IP-addresses: none' . PHP_EOL
            ],
            [
                ['ip' => ['127.0.0.1', '127.0.0.2'], '--none' => true],
                [],
                'Set exempt IP-addresses: none' . PHP_EOL
            ],
            [
                ['ip' => ['127.0'], '--none' => false],
                ['Invalid IP 127.0'],
                'Invalid IP 127.0' . PHP_EOL
            ],
            [
                ['ip' => [], '--none' => false],
                [],
                ''
            ]
        ];
    }
}
