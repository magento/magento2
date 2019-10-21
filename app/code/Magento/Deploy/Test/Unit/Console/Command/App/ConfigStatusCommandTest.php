<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command\App;

use Magento\Deploy\Console\Command\App\ConfigStatusCommand;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @inheritdoc
 */
class ConfigStatusCommandTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ConfigStatusCommand
     */
    private $command;
    /**
     * @var ChangeDetector
     */
    private $changeDetector;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->changeDetector = $this->getMockBuilder(ChangeDetector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new ConfigStatusCommand($this->changeDetector);
    }

    /**
     * @param bool $hasChanges
     * @param string $expectedMessage
     * @param int $expectedCode
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute(bool $hasChanges, $expectedMessage, $expectedCode)
    {
        $this->changeDetector->expects($this->once())
            ->method('hasChanges')
            ->will($this->returnValue($hasChanges));

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $this->assertEquals($expectedMessage, $tester->getDisplay());
        $this->assertSame($expectedCode, $tester->getStatusCode());
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'Config is up to date' => [
                false,
                'Config files are up to date.' . PHP_EOL,
                Cli::RETURN_SUCCESS
            ],
            'Config needs update' => [
                true,
                'Config files have changed. ' .
                'Run app:config:import or setup:upgrade command to synchronize configuration.' . PHP_EOL,
                ConfigStatusCommand::EXIT_CODE_CONFIG_IMPORT_REQUIRED,
            ],
        ];
    }
}
