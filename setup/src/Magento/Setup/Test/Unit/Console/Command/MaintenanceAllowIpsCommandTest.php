<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\MaintenanceAllowIpsCommand;
use Magento\Setup\Model\IpValidator;
use Symfony\Component\Console\Tester\CommandTester;

class MaintenanceAllowIpsCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\MaintenanceMode|\PHPUnit_Framework_MockObject_MockObject
     */
    private $maintenanceMode;

    /**
     * @var MaintenanceAllowIpsCommand
     */
    private $command;

    public function setUp()
    {
        $this->maintenanceMode = $this->getMock('Magento\Framework\App\MaintenanceMode', [], [], '', false);
        $this->command = new MaintenanceAllowIpsCommand($this->maintenanceMode, new IpValidator());
    }

    /**
     * @param array $input
     * @param string $expectedMessage
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $input, $expectedMessage)
    {
        if (isset($input['--none']) && !$input['--none'] && isset($input['ip'])) {
            $this->maintenanceMode
                ->expects($this->once())
                ->method('getAddressInfo')
                ->willReturn($input['ip']);
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
                'Set exempt IP-addresses: 127.0.0.1, 127.0.0.2' . PHP_EOL
            ],
            [
                ['--none' => true],
                'Set exempt IP-addresses: none' . PHP_EOL
            ],
            [
                ['ip' => ['127.0.0.1', '127.0.0.2'], '--none' => true],
                'Set exempt IP-addresses: none' . PHP_EOL
            ],
            [
                ['ip' => ['none']],
                "'none' is not allowed" . PHP_EOL
            ],
            [
                ['ip' => ['none'], '--none' => true],
                'Set exempt IP-addresses: none' . PHP_EOL
            ],
            [
                ['ip' => ['127.0.0.1', 'none']],
                "'none' is not allowed" . PHP_EOL
            ],
            [
                ['ip' => ['none', '127.0.0.1']],
                "'none' is not allowed" . PHP_EOL
            ],
            [
                ['ip' => ['none', 'none']],
                "'none' is not allowed" . PHP_EOL
            ],
            [
                ['ip' => ['127.0.0.1', 'none'], '--none' => true],
                'Set exempt IP-addresses: none' . PHP_EOL
            ],
            [
                ['ip' => ['127.0']],
                'Invalid IP 127.0' . PHP_EOL
            ],
            [
                [],
                ''
            ]
        ];
    }
}
