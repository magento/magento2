<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command;

use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorFactory;
use Magento\Config\Console\Command\ConfigSet\ConfigSetProcessorInterface;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Config\Model\Config\PathValidator;
use Magento\Config\Model\Config\PathValidatorFactory;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test for ConfigSetCommand.
 *
 * @see ConfigSetCommand
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var ScopeInterface|Mock
     */
    private $scopeMock;

    /**
     * @var PathValidatorFactory|Mock
     */
    private $pathValidatorFactoryMock;

    /**
     * @var PathValidator|Mock
     */
    private $pathValidator;

    /**
     * @inheritdoc
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
        $this->scopeMock = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();
        $this->pathValidatorFactoryMock = $this->getMockBuilder(PathValidatorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->pathValidator = $this->getMockBuilder(PathValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pathValidatorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->pathValidator);

        $this->command = new ConfigSetCommand(
            $this->configSetProcessorFactoryMock,
            $this->validatorMock,
            $this->scopeMock,
            $this->pathValidatorFactoryMock
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

    public function testExecuteWithException()
    {
        $this->validatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $this->pathValidator->expects($this->once())
            ->method('validate')
            ->willThrowException(new ValidatorException(__('The "test/test/test" path does not exists')));
        $this->configSetProcessorFactoryMock->expects($this->never())
            ->method('create');

        $tester = new CommandTester($this->command);
        $tester->execute([
            ConfigSetCommand::ARG_PATH => 'test/test/test',
            ConfigSetCommand::ARG_VALUE => 'value'
        ]);

        $this->assertContains(
            __('The "test/test/test" path does not exists')->render(),
            $tester->getDisplay()
        );
        $this->assertSame(Cli::RETURN_FAILURE, $tester->getStatusCode());
    }
}
