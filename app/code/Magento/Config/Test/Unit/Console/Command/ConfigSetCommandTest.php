<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command;

use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorFactory;
use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorInterface;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * {@inheritdoc}
 */
class ConfigSetCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigSetCommand
     */
    private $command;

    /**
     * @var ConfigSetProcessorFactory|Mock
     */
    private $configSetProcessorFactoryMock;

    /**
     * @var ValidatorInterface|Mock
     */
    private $validatorMock;

    /**
     * @var ConfigSetProcessorInterface|Mock
     */
    private $processorMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->configSetProcessorFactoryMock = $this->getMockBuilder(ConfigSetProcessorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $this->processorMock = $this->getMockBuilder(ConfigSetProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->command = new ConfigSetCommand(
            $this->configSetProcessorFactoryMock,
            $this->validatorMock
        );
    }

    public function testExecute()
    {
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_DEFAULT)
            ->willReturn($this->processorMock);

        $tester = new CommandTester($this->command);
        $tester->execute([
            ConfigSetCommand::ARG_PATH => 'test/test/test',
            ConfigSetCommand::ARG_VALUE => 'value'
        ]);
    }

    public function testExecuteLock()
    {
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->configSetProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with(ConfigSetProcessorFactory::TYPE_LOCK)
            ->willReturn($this->processorMock);

        $tester = new CommandTester($this->command);
        $tester->execute([
            ConfigSetCommand::ARG_PATH => 'test/test/test',
            ConfigSetCommand::ARG_VALUE => 'value',
            '--' . ConfigSetCommand::OPTION_LOCK => true
        ]);
    }

    public function testExecuteNotValidScope()
    {
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->willThrowException(new LocalizedException(__('Test exception')));

        $tester = new CommandTester($this->command);
        $tester->execute([
            ConfigSetCommand::ARG_PATH => 'test/test/test',
            ConfigSetCommand::ARG_VALUE => 'value'
        ]);

        $this->assertSame(Cli::RETURN_FAILURE, $tester->getStatusCode());
    }
}
