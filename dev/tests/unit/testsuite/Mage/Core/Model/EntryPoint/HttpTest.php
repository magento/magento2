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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_EntryPoint_HttpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var Mage_Core_Model_EntryPoint_Http
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->getMock('Magento_ObjectManager');
        $this->_model = new Mage_Core_Model_EntryPoint_Http(__DIR__, array(), $this->_objectManagerMock);
    }

    public function testHttpHandlerProcessesRequest()
    {
        $request = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false);
        $response = $this->getMock('Mage_Core_Controller_Response_Http', array(), array(), '', false);
        $requestHandlerMock = $this->getMock('Magento_Http_HandlerInterface');
        $requestHandlerMock->expects($this->once())->method('handle')->with($request, $response);
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnValueMap(array(
            array('Mage_Core_Controller_Request_Http', array(), $request),
            array('Mage_Core_Controller_Response_Http', array(), $response),
            array('Magento_Http_Handler_Composite', array(), $requestHandlerMock),
        )));
        $this->_model->processRequest();
    }
}
