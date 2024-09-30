<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\MaintenanceAllowIpsCommand;
use Magento\Framework\App\MaintenanceMode;
use Magento\Backend\Model\Validator\IpValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class MaintenanceAllowIpsCommandTest extends TestCase
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
     * @var MaintenanceAllowIpsCommand
     */
    private $command;

    protected function setUp(): void
    {
        $this->maintenanceMode = $this->createMock(MaintenanceMode::class);
        $this->ipValidator = $this->createMock(IpValidator::class);
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
     * @param array $addressInfo
     * @param array $input
     * @param array $validatorMessages
     * @param string $expectedMessage
     * @dataProvider executeWithAddDataProvider
     */
    public function testExecuteWithAdd(array $addressInfo, array $input, array $validatorMessages, $expectedMessage)
    {
        $newAddressInfo = array_unique(array_merge($addressInfo, $input['ip']));

        $this->ipValidator->expects($this->once())->method('validateIps')->willReturn($validatorMessages);
        $this->maintenanceMode
            ->expects($this->once())
            ->method('setAddresses')
            ->with(implode(',', $newAddressInfo));

        $this->maintenanceMode
            ->expects($this->exactly(2))
            ->method('getAddressInfo')
            ->willReturnOnConsecutiveCalls($addressInfo, $newAddressInfo);

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
                'Set exempt IP-addresses: 127.0.0.1 127.0.0.2' . PHP_EOL
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

    /**
     * return array
     */
    public function executeWithAddDataProvider()
    {
        return [
            [
                [],
                ['ip' => ['127.0.0.1'], '--add' => true],
                [],
                'Set exempt IP-addresses: 127.0.0.1' . PHP_EOL,
            ],
            [
                ['127.0.0.1'],
                ['ip' => ['127.0.0.1'], '--add' => true],
                [],
                'Set exempt IP-addresses: 127.0.0.1' . PHP_EOL,
            ],
            [
                ['127.0.0.1'],
                ['ip' => ['127.0.0.2'], '--add' => true],
                [],
                'Set exempt IP-addresses: 127.0.0.1 127.0.0.2' . PHP_EOL,
            ],
        ];
    }
}
