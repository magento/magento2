<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command;

use Magento\Config\Console\Command\ConfigSet\ProcessorFacade;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacadeFactory;
use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\ValidatorException;
use PHPUnit_Framework_MockObject_MockObject as Mock;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Test for ConfigSetCommand.
 *
 * @see ConfigSetCommand
 */
class ConfigSetCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigSetCommand
     */
    private $command;

    /**
     * @var ScopeInterface|Mock
     */
    private $scopeMock;

    /**
     * @var State|Mock
     */
    private $stateMock;

    /**
     * @var ProcessorFacadeFactory|Mock
     */
    private $processorFacadeFactoryMock;

    /**
     * @var ProcessorFacade|Mock
     */
    private $processorFacadeMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->scopeMock = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorFacadeFactoryMock = $this->getMockBuilder(ProcessorFacadeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorFacadeMock = $this->getMockBuilder(ProcessorFacade::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processorFacadeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->processorFacadeMock);

        $this->command = new ConfigSetCommand(
            $this->scopeMock,
            $this->stateMock,
            $this->processorFacadeFactoryMock
        );
    }

    public function testExecuteWithException()
    {
        $this->stateMock->expects($this->once())
            ->method('emulateAreaCode')
            ->willThrowException(new ValidatorException(__('The "test/test/test" path does not exists')));
        $this->processorFacadeFactoryMock->expects($this->never())
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
