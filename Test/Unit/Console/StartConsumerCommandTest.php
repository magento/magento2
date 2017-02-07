<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MessageQueue\Test\Unit\Console;

use Magento\MessageQueue\Console\StartConsumerCommand;

/**
 * Unit tests for StartConsumerCommand.
 */
class StartConsumerCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\ConsumerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $consumerFactory;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appState;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var StartConsumerCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->consumerFactory = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()->getMock();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->command = $this->objectManager->getObject(
            \Magento\MessageQueue\Console\StartConsumerCommand::class,
            [
                'consumerFactory' => $this->consumerFactory,
                'appState' => $this->appState,
            ]
        );
        parent::setUp();
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $areaCode = 'area_code';
        $numberOfMessages = 10;
        $batchSize = null;
        $consumerName = 'consumer_name';
        $input = $this->getMockBuilder(\Symfony\Component\Console\Input\InputInterface::class)
            ->disableOriginalConstructor()->getMock();
        $output = $this->getMockBuilder(\Symfony\Component\Console\Output\OutputInterface::class)
            ->disableOriginalConstructor()->getMock();
        $input->expects($this->once())->method('getArgument')
            ->with(\Magento\MessageQueue\Console\StartConsumerCommand::ARGUMENT_CONSUMER)
            ->willReturn($consumerName);
        $input->expects($this->exactly(3))->method('getOption')
            ->withConsecutive(
                [\Magento\MessageQueue\Console\StartConsumerCommand::OPTION_NUMBER_OF_MESSAGES],
                [\Magento\MessageQueue\Console\StartConsumerCommand::OPTION_BATCH_SIZE],
                [\Magento\MessageQueue\Console\StartConsumerCommand::OPTION_AREACODE]
            )->willReturnOnConsecutiveCalls(
                $numberOfMessages,
                $batchSize,
                $areaCode
            );
        $this->appState->expects($this->once())->method('setAreaCode')->with($areaCode);
        $consumer = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->consumerFactory->expects($this->once())
            ->method('get')->with($consumerName, $batchSize)->willReturn($consumer);
        $consumer->expects($this->once())->method('process')->with($numberOfMessages);
        $this->assertEquals(
            \Magento\Framework\Console\Cli::RETURN_SUCCESS,
            $this->command->run($input, $output)
        );
    }

    /**
     * Test configure() method implicitly via construct invocation.
     *
     * @return void
     */
    public function testConfigure()
    {
        $this->assertEquals(StartConsumerCommand::COMMAND_QUEUE_CONSUMERS_START, $this->command->getName());
        $this->assertEquals('Start MessageQueue consumer', $this->command->getDescription());
        /** Exception will be thrown if argument is not declared */
        $this->command->getDefinition()->getArgument(StartConsumerCommand::ARGUMENT_CONSUMER);
        $this->command->getDefinition()->getOption(StartConsumerCommand::OPTION_NUMBER_OF_MESSAGES);
        $this->command->getDefinition()->getOption(StartConsumerCommand::OPTION_AREACODE);
        $this->assertContains('To start consumer which will process', $this->command->getHelp());
    }
}
