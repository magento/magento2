<?php 
/**
 * Magento_Outbound_Transport_Http_Response
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
 * @category    Magento
 * @package     Magento_Outbound
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Outbound_Transport_Http_ResponseTest extends PHPUnit_Framework_TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $_mockZndHttpResp;
        
    public function setUp() 
    {
        $this->_mockZndHttpResp = $this->getMockBuilder('Zend_Http_Response')
            ->disableOriginalConstructor()->getMock();
    }
    
    public function testIsSuccessfulTrue() 
    {
        $this->_mockZndHttpResp->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(299));
        $uut = new Magento_Outbound_Transport_Http_Response($this->_mockZndHttpResp);
        $this->assertTrue($uut->isSuccessful());
    }

    public function testIsSuccessfulFalse()
    {
        $this->_mockZndHttpResp->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(301));
        $uut = new Magento_Outbound_Transport_Http_Response($this->_mockZndHttpResp);
        $this->assertFalse($uut->isSuccessful());
    }
    
    public function testGetStatusCode() 
    {
        $this->_mockZndHttpResp->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(299));
        $uut = new Magento_Outbound_Transport_Http_Response($this->_mockZndHttpResp);
        $this->assertSame(299, $uut->getStatusCode());
    }
    
    public function testGetMessage()
    {
        $this->_mockZndHttpResp->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue("A-OK"));
        $uut = new Magento_Outbound_Transport_Http_Response($this->_mockZndHttpResp);
        $this->assertSame("A-OK", $uut->getMessage());
    }

    public function testGetBody()
    {
        $this->_mockZndHttpResp->expects($this->any())
            ->method('getRawBody')
            ->will($this->returnValue("Raw Body"));
        $uut = new Magento_Outbound_Transport_Http_Response($this->_mockZndHttpResp);
        $this->assertSame("Raw Body", $uut->getBody());
        $this->assertSame("Raw Body", $uut->getRawBody());
    }
    
    public function testGetHeaders()
    {
        $hdrs = array('key1' => 'va11', 'key2' => 'val2');
        $this->_mockZndHttpResp->expects($this->any())
            ->method('getHeaders')
            ->will($this->returnValue($hdrs));
        $this->_mockZndHttpResp->expects($this->any())
            ->method('getHeader')
            ->will($this->returnValue($hdrs['key1']));
        $uut = new Magento_Outbound_Transport_Http_Response($this->_mockZndHttpResp);
        $this->assertSame($hdrs, $uut->getHeaders());
        $this->assertSame($hdrs['key1'], $uut->getHeader('key1'));
    }
}