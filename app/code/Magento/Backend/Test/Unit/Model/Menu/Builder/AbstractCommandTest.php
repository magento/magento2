<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Menu\Builder;

class AbstractCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu\Builder\AbstractCommand
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockForAbstractClass(
            \Magento\Backend\Model\Menu\Builder\AbstractCommand::class,
            [['id' => 'item']]
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorRequiresObligatoryParams()
    {
        $this->getMockForAbstractClass(\Magento\Backend\Model\Menu\Builder\AbstractCommand::class);
    }

    public function testChainAddsNewCommandAsNextInChain()
    {
        $command1 = $this->getMockBuilder(\Magento\Backend\Model\Menu\Builder\Command\Update::class)
            ->setConstructorArgs([['id' => 1]])
            ->getMock();
        $command2 = $this->getMockBuilder(\Magento\Backend\Model\Menu\Builder\Command\Remove::class)
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

        $command1 = $this->getMockBuilder(\Magento\Backend\Model\Menu\Builder\Command\Update::class)
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
