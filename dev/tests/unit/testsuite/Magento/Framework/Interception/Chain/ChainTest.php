<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Interception\Chain;

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
        $this->_pluginListMock = $this->getMock(
            '\Magento\Framework\Interception\PluginList',
            array(),
            array(),
            '',
            false
        );
        $this->_model = new \Magento\Framework\Interception\Chain\Chain($this->_pluginListMock);
    }

    /**
     * @covers  \Magento\Framework\Interception\Chain\Chain::invokeNext
     * @covers  \Magento\Framework\Interception\Chain\Chain::__construct
     */
    public function testInvokeNextBeforePlugin()
    {
        $type = 'type';
        $method = 'method';

        $subjectMock = $this->getMock('SubjectClass', array('___callParent'));
        $pluginMock = $this->getMock('PluginClass', array('beforeMethod'));

        $pluginMock->expects($this->once())
            ->method('beforeMethod')
            ->with($subjectMock, 1, 2)
            ->will($this->returnValue('beforeMethodResult'));

        $this->_pluginListMock->expects($this->once())
            ->method('getNext')
            ->with($type, $method, null)
            ->will(
                $this->returnValue(
                    array(\Magento\Framework\Interception\Definition::LISTENER_BEFORE => array('code'))
                )
            );

        $this->_pluginListMock->expects($this->once())
            ->method('getPlugin')
            ->with($type, 'code')
            ->will($this->returnValue($pluginMock));

        $subjectMock->expects($this->once())
            ->method('___callParent')
            ->with('method', 'beforeMethodResult')
            ->will($this->returnValue('subjectMethodResult'));

        $this->assertEquals('subjectMethodResult', $this->_model->invokeNext($type, $method, $subjectMock, array(1,2)));
    }

    /**
     * @covers  \Magento\Framework\Interception\Chain\Chain::invokeNext
     */
    public function testInvokeNextAroundPlugin()
    {
        $type = 'type';
        $method = 'method';

        $subjectMock = $this->getMock('SubjectClass');
        $pluginMock = $this->getMock('PluginClass', array('aroundMethod'));

        $pluginMock->expects($this->once())
            ->method('aroundMethod')
            ->with($this->anything())
            ->will($this->returnValue('subjectMethodResult'));

        $this->_pluginListMock->expects($this->once())
            ->method('getNext')
            ->with($type, $method, null)
            ->will($this->returnValue(array(\Magento\Framework\Interception\Definition::LISTENER_AROUND => 'code')));

        $this->_pluginListMock->expects($this->once())
            ->method('getPlugin')
            ->with($type, 'code')
            ->will($this->returnValue($pluginMock));

        $this->assertEquals('subjectMethodResult', $this->_model->invokeNext($type, $method, $subjectMock, array()));
    }

    /**
     * @covers  \Magento\Framework\Interception\Chain\Chain::invokeNext
     */
    public function testInvokeNextAfterPlugin()
    {
        $type = 'type';
        $method = 'method';

        $subjectMock = $this->getMock('SubjectClass', array('___callParent'));
        $pluginMock = $this->getMock('PluginClass', array('afterMethod'));

        $pluginMock->expects($this->once())
            ->method('afterMethod')
            ->with($subjectMock, 'subjectMethodResult')
            ->will($this->returnValue('afterMethodResult'));

        $this->_pluginListMock->expects($this->once())
            ->method('getNext')
            ->with($type, $method, null)
            ->will(
                $this->returnValue(
                    array(\Magento\Framework\Interception\Definition::LISTENER_AFTER => array('code'))
                )
            );

        $this->_pluginListMock->expects($this->once())
            ->method('getPlugin')
            ->with($type, 'code')
            ->will($this->returnValue($pluginMock));

        $subjectMock->expects($this->once())
            ->method('___callParent')
            ->with('method', array(1, 2))
            ->will($this->returnValue('subjectMethodResult'));

        $this->assertEquals('afterMethodResult', $this->_model->invokeNext($type, $method, $subjectMock, array(1, 2)));
    }
}
