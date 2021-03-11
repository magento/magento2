<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MessageQueue\Test\Unit\Console;

use Magento\MessageQueue\Console\StartConsumerCommand;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\Framework\Lock\LockManagerInterface;

/**
 * Unit tests for StartConsumerCommand.
 */
class StartConsumerCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\ConsumerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $consumerFactory;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit\Framework\MockObject\MockObject
     */
    private $appState;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var WriteFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $writeFactoryMock;

    /**
     * @var LockManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $lockManagerMock;

    /**
     * @var StartConsumerCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->lockManagerMock = $this->getMockBuilder(LockManagerInterface::class)
            ->getMockForAbstractClass();
        $this->consumerFactory = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()->getMock();
        $this->writeFactoryMock = $this->getMockBuilder(WriteFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->command = $this->objectManager->getObject(
            \Magento\MessageQueue\Console\StartConsumerCommand::class,
            [
                'consumerFactory' => $this->consumerFactory,
                'appState' => $this->appState,
                'writeFactory' => $this->writeFactoryMock,
                'lockManager' => $this->lockManagerMock,
            ]
        );
        parent::setUp();
    }

    /**
     * Test for execute method.
     *
     * @param string|null $pidFilePath
     * @param bool $singleThread
     * @param int $lockExpects
     * @param int $isLockedExpects
     * @param bool $isLocked
     * @param int $unlockExpects
     * @param int $runProcessExpects
     * @param int $expectedReturn
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        $pidFilePath,
        $singleThread,
        $lockExpects,
        $isLocked,
        $unlockExpects,
        $runProcessExpects,
        $expectedReturn
    ) {
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
        $input->expects($this->exactly(5))->method('getOption')
            ->withConsecutive(
                [\Magento\MessageQueue\Console\StartConsumerCommand::OPTION_NUMBER_OF_MESSAGES],
                [\Magento\MessageQueue\Console\StartConsumerCommand::OPTION_BATCH_SIZE],
                [\Magento\MessageQueue\Console\StartConsumerCommand::OPTION_AREACODE],
                [\Magento\MessageQueue\Console\StartConsumerCommand::PID_FILE_PATH],
                [\Magento\MessageQueue\Console\StartConsumerCommand::OPTION_SINGLE_THREAD]
            )->willReturnOnConsecutiveCalls(
                $numberOfMessages,
                $batchSize,
                $areaCode,
                $pidFilePath,
                $singleThread
            );
        $this->appState->expects($this->exactly($runProcessExpects))->method('setAreaCode')->with($areaCode);
        $consumer = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->consumerFactory->expects($this->exactly($runProcessExpects))
            ->method('get')->with($consumerName, $batchSize)->willReturn($consumer);
        $consumer->expects($this->exactly($runProcessExpects))->method('process')->with($numberOfMessages);

        $this->lockManagerMock->expects($this->exactly($lockExpects))
            ->method('lock')
            ->with(md5($consumerName))//phpcs:ignore
            ->willReturn($isLocked);

        $this->lockManagerMock->expects($this->exactly($unlockExpects))
            ->method('unlock')
            ->with(md5($consumerName)); //phpcs:ignore

        $this->assertEquals(
            $expectedReturn,
            $this->command->run($input, $output)
        );
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                'pidFilePath' => null,
                'singleThread' => false,
                'lockExpects' => 0,
                'isLocked' => true,
                'unlockExpects' => 0,
                'runProcessExpects' => 1,
                'expectedReturn' => \Magento\Framework\Console\Cli::RETURN_SUCCESS,
            ],
            [
                'pidFilePath' => '/var/consumer.pid',
                'singleThread' => true,
                'lockExpects' => 1,
                'isLocked' => true,
                'unlockExpects' => 1,
                'runProcessExpects' => 1,
                'expectedReturn' => \Magento\Framework\Console\Cli::RETURN_SUCCESS,
            ],
            [
                'pidFilePath' => '/var/consumer.pid',
                'singleThread' => true,
                'lockExpects' => 1,
                'isLocked' => false,
                'unlockExpects' => 0,
                'runProcessExpects' => 0,
                'expectedReturn' => \Magento\Framework\Console\Cli::RETURN_FAILURE,
            ],
        ];
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
        $this->command->getDefinition()->getOption(StartConsumerCommand::PID_FILE_PATH);
        $this->command->getDefinition()->getOption(StartConsumerCommand::OPTION_SINGLE_THREAD);
        $this->assertStringContainsString('To start consumer which will process', $this->command->getHelp());
    }
}
