<?php
/**
 * Test JSON Renderer for REST.
 *
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
class Mage_Webapi_Controller_Response_Rest_Renderer_JsonTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Webapi_Controller_Response_Rest_Renderer_Json */
    protected $_restJsonRenderer;

    /** @var Mage_Core_Helper_Data */
    protected $_helperMock;

    protected function setUp()
    {
        /** Prepare mocks and objects for SUT constructor. */
        $this->_helperMock = $this->getMockBuilder('Mage_Core_Helper_Data')->disableOriginalConstructor()->getMock();
        $helperFactoryMock = $this->getMock('Mage_Core_Model_Factory_Helper');
        $helperFactoryMock->expects($this->any())->method('get')->will($this->returnValue($this->_helperMock));
        /** Initialize SUT. */
        $this->_restJsonRenderer = new Mage_Webapi_Controller_Response_Rest_Renderer_Json($helperFactoryMock);
        parent::setUp();
    }

    protected function tearDown()
    {
        unset($this->_helperMock);
        unset($this->_restJsonRenderer);
        parent::tearDown();
    }

    /**
     * Test render method.
     */
    public function testRender()
    {
        $arrayToRender = array('key' => 'value');
        /** Assert that jsonEncode method in mocked helper will run once */
        $this->_helperMock->expects($this->once())->method('jsonEncode');
        $this->_restJsonRenderer->render($arrayToRender);
    }

    /**
     * Test GetMimeType method.
     */
    public function testGetMimeType()
    {
        $expectedMimeType = 'application/json';
        $this->assertEquals($expectedMimeType, $this->_restJsonRenderer->getMimeType(), 'Unexpected mime type.');
    }
}
