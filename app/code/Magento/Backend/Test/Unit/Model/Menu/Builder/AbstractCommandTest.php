<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $this->_model = $this->getMockForAbstractClass(
            AbstractCommand::class,
            [['id' => 'item']]
        );
    }

    public function testConstructorRequiresObligatoryParams()
    {
        $this->expectException('InvalidArgumentException');
        $this->getMockForAbstractClass(AbstractCommand::class);
    }

    public function testChainAddsNewCommandAsNextInChain()
    {
        $command1 = $this->getMockBuilder(Update::class)
            ->setConstructorArgs([['id' => 1]])
            ->getMock();
        $command2 = $this->getMockBuilder(Remove::class)
            ->setConstructorArgs([['id' => 1]])
            ->getMock();
        $command1->expects($this->once())->method('chain')->with($this->equalTo($command2));

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
        )->will(
            $this->returnValue($itemParams)
        );

        $command1 = $this->getMockBuilder(Update::class)
            ->setConstructorArgs([['id' => 1]])
            ->getMock();

        $command1->expects(
            $this->once()
        )->method(
            'execute'
        )->with(
            $this->equalTo($itemParams)
        )->will(
            $this->returnValue($itemParams)
        );

        $this->_model->chain($command1);
        $this->assertEquals($itemParams, $this->_model->execute($itemParams));
    }
}
