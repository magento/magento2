<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\App;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Log\App\Shell
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shellFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    protected function setUp()
    {
        $this->_shellFactoryMock = $this->getMock(
            'Magento\Log\Model\ShellFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_responseMock = $this->getMock('Magento\Framework\App\Console\Response', [], [], '', false);
        $this->_model = new \Magento\Log\App\Shell('shell.php', $this->_shellFactoryMock, $this->_responseMock);
    }

    public function testProcessRequest()
    {
        $shellMock = $this->getMock('Magento\Log\App\Shell', ['run'], [], '', false);
        $this->_shellFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            ['entryPoint' => 'shell.php']
        )->will(
            $this->returnValue($shellMock)
        );
        $shellMock->expects($this->once())->method('run');
        $this->assertEquals($this->_responseMock, $this->_model->launch());
    }

    public function testCatchException()
    {
        $bootstrap = $this->getMock('Magento\Framework\App\Bootstrap', [], [], '', false);
        $this->assertFalse($this->_model->catchException($bootstrap, new \Exception()));
    }
}
