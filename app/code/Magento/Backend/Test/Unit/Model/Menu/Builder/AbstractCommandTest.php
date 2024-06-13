<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Menu\Builder;

use Magento\Backend\Model\Menu\Builder\AbstractCommand;
use Magento\Backend\Model\Menu\Builder\Command\Remove;
use Magento\Backend\Model\Menu\Builder\Command\Update;
use PHPUnit\Framework\TestCase;

class AbstractCommandTest extends TestCase
{
    /**
     * @var AbstractCommand
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = $this->getMockBuilder(AbstractCommand::class)
            ->setConstructorArgs([['id' => 'item']])
            ->onlyMethods(['_execute'])
            ->getMock();
    }

    public function testConstructorRequiresObligatoryParams()
    {
        $this->expectException('InvalidArgumentException');
        $this->getMockBuilder(AbstractCommand::class)
            ->onlyMethods(['_execute'])
            ->getMock();
    }

    public function testChainAddsNewCommandAsNextInChain()
    {
        $command1 = $this->createMock(Update::class);

        $command2 = $this->createMock(Remove::class);

        $command1->expects($this->once())->method('chain')->with($command2);

        $this->_model->chain($command1);
        $this->_model->chain($command2);
    }

    public function testExecuteCallsNextCommandInChain()
    {
        $itemParams = [];
        $this->_model->expects(
            $this->once()
        )->method(
            '_execute'
        )->with(
            $this->equalTo($itemParams)
        )->willReturn(
            $itemParams
        );

        $command1 = $this->createMock(Update::class);

        $command1->expects(
            $this->once()
        )->method(
            'execute'
        )->with(
            $itemParams
        )->willReturn(
            $itemParams
        );

        $this->_model->chain($command1);
        $this->assertEquals($itemParams, $this->_model->execute($itemParams));
    }
}
