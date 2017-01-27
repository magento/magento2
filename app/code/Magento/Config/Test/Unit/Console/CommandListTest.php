<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console;

use Magento\Config\Console\Command\ConfigSetCommand;
use Magento\Config\Console\CommandList;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * Test for CommandList.
 *
 * @see \Magento\Config\Console\CommandList
 */
class CommandListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandList
     */
    private $model;

    /**
     * @var ObjectManagerInterface|Mock
     */
    private $objectManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();

        $this->model = new CommandList(
            $this->objectManagerMock
        );
    }

    public function testGetCommands()
    {
        $this->objectManagerMock->expects($this->exactly(1))
            ->method('get')
            ->withConsecutive([
                ConfigSetCommand::class
            ]);

        $this->model->getCommands();
    }
}
