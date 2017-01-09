<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MessageQueue\Test\Unit\Console;

use Magento\MessageQueue\Console\StartConsumerCommand;

/**
 * Unit tests for StartConsumerCommand
 */
class StartConsumerCommandTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
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
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        parent::setUp();
    }

    /**
     * Test configure() method implicitly via construct invocation.
     *
     * @return void
     */
    public function testConfigure()
    {
        $this->command = $this->objectManager->getObject(\Magento\MessageQueue\Console\StartConsumerCommand::class);

        $this->assertEquals(StartConsumerCommand::COMMAND_QUEUE_CONSUMERS_START, $this->command->getName());
        $this->assertEquals('Start MessageQueue consumer', $this->command->getDescription());
        /** Exception will be thrown if argument is not declared */
        $this->command->getDefinition()->getArgument(StartConsumerCommand::ARGUMENT_CONSUMER);
        $this->command->getDefinition()->getOption(StartConsumerCommand::OPTION_NUMBER_OF_MESSAGES);
        $this->command->getDefinition()->getOption(StartConsumerCommand::OPTION_AREACODE);
        $this->assertContains('To start consumer which will process', $this->command->getHelp());
    }
}
