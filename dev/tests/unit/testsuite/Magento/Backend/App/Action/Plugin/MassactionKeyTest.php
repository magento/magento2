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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\App\Action\Plugin;

class MassactionKeyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Backend\App\Action\Plugin\MassactionKey
     */
    protected $_plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_invocationChainMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $this->_invocationChainMock = $this->getMock(
            'Magento\Code\Plugin\InvocationChain', array(), array(), '', false
        );
        $this->_requestMock = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);
        $this->_plugin = new \Magento\Backend\App\Action\Plugin\MassactionKey();
    }

    /**
     * @covers \Magento\Backend\App\Action\Plugin\MassactionKey::aroundDispatch
     *
     * @param $postData array|string
     * @param array $convertedData
     * @dataProvider aroundDispatchDataProvider
     */
    public function testAroundDispatchWhenMassactionPrepareKeyRequestExists($postData, $convertedData)
    {

        $this->_requestMock ->expects($this->at(0))
            ->method('getPost')->with('massaction_prepare_key')->will($this->returnValue('key'));
        $this->_requestMock->expects($this->at(1))->method('getPost')->with('key')->will($this->returnValue($postData));
        $this->_requestMock->expects($this->once())->method('setPost')->with('key', $convertedData);
        $this->_invocationChainMock->expects($this->once())
            ->method('proceed')->with(array($this->_requestMock))->will($this->returnValue('Expected'));
        $this->assertEquals('Expected',
            $this->_plugin->aroundDispatch(array($this->_requestMock), $this->_invocationChainMock));
    }

    public function aroundDispatchDataProvider()
    {
        return array(
            'post_data_is_array' => array(
                array('key'),  array('key')
            ),
            'post_data_is_string' => array(
                'key, key_two', array('key', ' key_two')
            )
        );
    }

    /**
     * @covers \Magento\Backend\App\Action\Plugin\MassactionKey::aroundDispatch
     */
    public function testAroundDispatchWhenMassactionPrepareKeyRequestNotExists()
    {

        $this->_requestMock ->expects($this->once())
            ->method('getPost')->with('massaction_prepare_key')->will($this->returnValue(false));
        $this->_requestMock->expects($this->never())->method('setPost');
        $this->_invocationChainMock->expects($this->once())
            ->method('proceed')->with(array($this->_requestMock))->will($this->returnValue('Expected'));
        $this->assertEquals('Expected',
            $this->_plugin->aroundDispatch(array($this->_requestMock), $this->_invocationChainMock));
    }
}
