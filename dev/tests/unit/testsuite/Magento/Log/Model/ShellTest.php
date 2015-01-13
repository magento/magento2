<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shellMock;

    /**
     * @var \Magento\Log\Model\Shell
     */
    protected $_model;

    protected function setUp()
    {
        $this->_factoryMock = $this->getMock('Magento\Log\Model\Shell\Command\Factory', [], [], '', false);
        $filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->_model = $this->getMock(
            'Magento\Log\Model\Shell',
            ['_applyPhpVariables'],
            [$filesystemMock, 'entryPoint.php', $this->_factoryMock]
        );
    }

    public function testRunWithShowHelp()
    {
        $this->expectOutputRegex('/Usage\:  php -f entryPoint\.php/');
        $this->_model->setRawArgs(['h']);
        $this->_factoryMock->expects($this->never())->method('createCleanCommand');
        $this->_factoryMock->expects($this->never())->method('createStatusCommand');
        $this->_model->run();
    }

    public function testRunWithCleanCommand()
    {
        $this->expectOutputRegex('/clean command message/');
        $this->_model->setRawArgs(['clean', '--days', 10]);
        $commandMock = $this->getMock('Magento\Log\Model\Shell\CommandInterface');
        $this->_factoryMock->expects(
            $this->once()
        )->method(
            'createCleanCommand'
        )->with(
            10
        )->will(
            $this->returnValue($commandMock)
        );
        $commandMock->expects($this->once())->method('execute')->will($this->returnValue('clean command message'));
        $this->_factoryMock->expects($this->never())->method('createStatusCommand');
        $this->_model->run();
    }

    public function testRunWithStatusCommand()
    {
        $this->expectOutputRegex('/status command message/');
        $this->_model->setRawArgs(['status']);
        $commandMock = $this->getMock('Magento\Log\Model\Shell\CommandInterface');
        $this->_factoryMock->expects(
            $this->once()
        )->method(
            'createStatusCommand'
        )->will(
            $this->returnValue($commandMock)
        );
        $commandMock->expects($this->once())->method('execute')->will($this->returnValue('status command message'));
        $this->_factoryMock->expects($this->never())->method('createCleanCommand');
        $this->_model->run();
    }

    public function testRunWithoutCommand()
    {
        $this->expectOutputRegex('/Usage\:  php -f entryPoint\.php/');
        $this->_factoryMock->expects($this->never())->method('createStatusCommand');
        $this->_factoryMock->expects($this->never())->method('createCleanCommand');
        $this->_model->run();
    }
}
