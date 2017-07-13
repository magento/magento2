<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Inspection\JsHint;

class CommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Inspection\JsHint\Command|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cmd;

    protected function setUp()
    {
        $this->_cmd = $this->getMockBuilder(\Magento\TestFramework\Inspection\JsHint\Command::class)
            ->setMethods(
                [
                    '_getHostScript',
                    '_fileExists',
                    '_getJsHintPath',
                    '_executeCommand',
                    'getFileName',
                    '_execShellCmd',
                    '_getJsHintOptions'
                ]
            )
            ->setConstructorArgs(['mage.js', 'report.xml'])
            ->getMock();
    }

    public function testCanRun()
    {
        $this->_cmd->expects($this->any())->method('_getHostScript')->will($this->returnValue('cscript'));
        $this->_cmd->expects(
            $this->any()
        )->method(
            '_executeCommand'
        )->with(
            $this->stringContains('cscript')
        )->will(
            $this->returnValue(['output', 0])
        );
        $this->_cmd->expects($this->any())->method('_getJsHintPath')->will($this->returnValue('jshint-path'));
        $this->_cmd->expects(
            $this->any()
        )->method(
            '_fileExists'
        )->with(
            $this->isType('string')
        )->will(
            $this->returnValue(true)
        );
        $this->_cmd->expects($this->any())->method('getFileName')->will($this->returnValue('mage.js'));
        $this->assertEquals(true, $this->_cmd->canRun());
    }

    public function testCanRunHostScriptDoesNotExistException()
    {
        $this->_cmd->expects($this->any())->method('_getHostScript')->will($this->returnValue('cscript'));
        $this->_cmd->expects(
            $this->any()
        )->method(
            '_executeCommand'
        )->with(
            $this->stringContains('cscript')
        )->will(
            $this->returnValue(['output', 1])
        );
        try {
            $this->_cmd->canRun();
        } catch (\Exception $e) {
            $this->assertEquals('cscript does not exist.', $e->getMessage());
        }
    }

    public function testCanRunJsHintPathDoesNotExistException()
    {
        $this->_cmd->expects($this->any())->method('_getHostScript')->will($this->returnValue('cscript'));
        $this->_cmd->expects(
            $this->any()
        )->method(
            '_executeCommand'
        )->with(
            $this->stringContains('cscript')
        )->will(
            $this->returnValue(['output', 0])
        );
        $this->_cmd->expects($this->any())->method('_getJsHintPath')->will($this->returnValue('jshint-path'));
        $this->_cmd->expects(
            $this->any()
        )->method(
            '_fileExists'
        )->with(
            'jshint-path'
        )->will(
            $this->returnValue(false)
        );
        try {
            $this->_cmd->canRun();
        } catch (\Exception $e) {
            $this->assertEquals('jshint-path does not exist.', $e->getMessage());
        }
    }

    public function testCanRunJsFileDoesNotExistException()
    {
        $this->_cmd->expects($this->any())->method('_getHostScript')->will($this->returnValue('cscript'));
        $this->_cmd->expects(
            $this->any()
        )->method(
            '_executeCommand'
        )->with(
            $this->stringContains('cscript')
        )->will(
            $this->returnValue(['output', 0])
        );
        $this->_cmd->expects($this->any())->method('_getJsHintPath')->will($this->returnValue('jshint-path'));
        $this->_cmd->expects($this->any())->method('_fileExists')->will(
            $this->returnCallback(
                function () {
                    $arg = func_get_arg(0);
                    if ($arg == 'jshint-path') {
                        return true;
                    }
                    if ($arg == 'mage.js') {
                        return false;
                    }
                }
            )
        );
        $this->_cmd->expects($this->any())->method('getFileName')->will($this->returnValue('mage.js'));
        try {
            $this->_cmd->canRun();
        } catch (\Exception $e) {
            $this->assertEquals('mage.js does not exist.', $e->getMessage());
        }
    }

    public function testRun()
    {
        $this->_cmd->expects($this->any())->method('_getHostScript')->will($this->returnValue('cscript'));
        $this->_cmd->expects($this->any())->method('_getJsHintPath')->will($this->returnValue('jshint-path'));
        $this->_cmd->expects($this->any())->method('getFileName')->will($this->returnValue('mage.js'));
        $this->_cmd->expects($this->once())->method('_execShellCmd')->with('cscript "jshint-path" "mage.js" ');
        $this->_cmd->run([]);
    }
}
