<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Interception\Test\Unit\Chain;

class ChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Interception\Chain\Chain
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pluginListMock;

    protected function setUp()
    {
        $this->_pluginListMock = $this->getMock('Magento\Framework\Interception\PluginListInterface');
        $this->_model = new \Magento\Framework\Interception\Chain\Chain($this->_pluginListMock);
    }

    /**
     * @covers \Magento\Framework\Interception\Chain\Chain::invokeNext
     * @covers \Magento\Framework\Interception\Chain\Chain::__construct
     */
    public function testInvokeNextBeforePlugin()
    {
        $type = 'type';
        $method = 'method';

        $subjectMock = $this->getMock('Magento\Framework\Interception\InterceptorInterface');
        $pluginMock = $this->getMock('PluginClass', ['beforeMethod']);

        $pluginMock->expects($this->once())
            ->method('beforeMethod')
            ->with($subjectMock, 1, 2)
            ->will($this->returnValue(['beforeMethodResult']));

        $this->_pluginListMock->expects($this->once())
            ->method('getNext')
            ->with($type, $method, null)
            ->will(
                $this->returnValue(
                    [\Magento\Framework\Interception\DefinitionInterface::LISTENER_BEFORE => ['code']]
                )
            );

        $this->_pluginListMock->expects($this->once())
            ->method('getPlugin')
            ->with($type, 'code')
            ->will($this->returnValue($pluginMock));

        $subjectMock->expects($this->once())
            ->method('___callParent')
            ->with('method', ['beforeMethodResult'])
            ->will($this->returnValue('subjectMethodResult'));

        $this->assertEquals('subjectMethodResult', $this->_model->invokeNext($type, $method, $subjectMock, [1, 2]));
    }

    /**
     * @covers \Magento\Framework\Interception\Chain\Chain::invokeNext
     */
    public function testInvokeNextAroundPlugin()
    {
        $type = 'type';
        $method = 'method';

        $subjectMock = $this->getMock('Magento\Framework\Interception\InterceptorInterface');
        $pluginMock = $this->getMock('PluginClass', ['aroundMethod']);

        $pluginMock->expects($this->once())
            ->method('aroundMethod')
            ->with($this->anything())
            ->will($this->returnValue('subjectMethodResult'));

        $this->_pluginListMock->expects($this->once())
            ->method('getNext')
            ->with($type, $method, null)
            ->will($this->returnValue([
                \Magento\Framework\Interception\DefinitionInterface::LISTENER_AROUND => 'code',
            ]));

        $this->_pluginListMock->expects($this->once())
            ->method('getPlugin')
            ->with($type, 'code')
            ->will($this->returnValue($pluginMock));

        $this->assertEquals('subjectMethodResult', $this->_model->invokeNext($type, $method, $subjectMock, []));
    }

    /**
     * @covers \Magento\Framework\Interception\Chain\Chain::invokeNext
     */
    public function testInvokeNextAfterPlugin()
    {
        $type = 'type';
        $method = 'method';

        $subjectMock = $this->getMock('Magento\Framework\Interception\InterceptorInterface');
        $pluginMock = $this->getMock('PluginClass', ['afterMethod']);

        $pluginMock->expects($this->once())
            ->method('afterMethod')
            ->with($subjectMock, 'subjectMethodResult')
            ->will($this->returnValue('afterMethodResult'));

        $this->_pluginListMock->expects($this->once())
            ->method('getNext')
            ->with($type, $method, null)
            ->will(
                $this->returnValue(
                    [\Magento\Framework\Interception\DefinitionInterface::LISTENER_AFTER => ['code']]
                )
            );

        $this->_pluginListMock->expects($this->once())
            ->method('getPlugin')
            ->with($type, 'code')
            ->will($this->returnValue($pluginMock));

        $subjectMock->expects($this->once())
            ->method('___callParent')
            ->with('method', [1, 2])
            ->will($this->returnValue('subjectMethodResult'));

        $this->assertEquals('afterMethodResult', $this->_model->invokeNext($type, $method, $subjectMock, [1, 2]));
    }
}
