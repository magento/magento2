<?php
/**
 * \Magento\Outbound\Transport\Http
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

namespace Magento\Outbound\Transport;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockVrnHttpAdptrCrl;
    
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_mockMessage;
        
    protected function setUp()
    {
        $this->_mockVrnHttpAdptrCrl = $this->getMockBuilder('Magento\HTTP\Adapter\Curl')
            ->disableOriginalConstructor()->getMock();
        $this->_mockMessage = $this->getMockBuilder('Magento\Outbound\Message')
            ->disableOriginalConstructor()->getMock();
        $this->_mockMessage->expects($this->any())
            ->method('getHeaders')
            ->will($this->returnValue(array('header'=>'value')));        
    }

    /**
     * Test case for when http adapter returns null
     * 
     * @expectedException \Zend_Http_Exception
     */   
    public function testNullResponse() 
    {
        $uut = new \Magento\Outbound\Transport\Http($this->_mockVrnHttpAdptrCrl);
        $this->_mockVrnHttpAdptrCrl->expects($this->any())
            ->method('read')
            ->will($this->returnValue(null));
        $uut->dispatch($this->_mockMessage);
    }
    
    public function testPositive()
    {
        $uut = new \Magento\Outbound\Transport\Http($this->_mockVrnHttpAdptrCrl);
        $this->_mockVrnHttpAdptrCrl->expects($this->any())
            ->method('read')
            ->will($this->returnValue("HTTP/2.0 200 OK\nHdrkey: Hdrval\n\nMessage Body"));
        $response = $uut->dispatch($this->_mockMessage);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("OK", $response->getMessage());
        $this->assertSame(array("Hdrkey" => "Hdrval"), $response->getHeaders());
        $this->assertSame("Message Body", $response->getBody());
    }

    /**
     * @dataProvider timeoutDataProvider
     *
     * @param $timeout
     */
    public function testMessageTimeout($timeout, $expectedTimeout)
    {
        $uut = new \Magento\Outbound\Transport\Http($this->_mockVrnHttpAdptrCrl);
        $this->_mockMessage->expects($this->any())
            ->method('getTimeout')
            ->will($this->returnValue($timeout));
        $config = array(
            'verifypeer' => true,
            'verifyhost' => 2,
            'timeout' => $expectedTimeout
        );
        $this->_mockVrnHttpAdptrCrl->expects($this->once())
            ->method('setConfig')
            ->with($config);
        $this->_mockVrnHttpAdptrCrl->expects($this->any())
            ->method('read')
            ->will($this->returnValue("HTTP/2.0 200 OK\nHdrkey: Hdrval\n\nMessage Body"));
        $response = $uut->dispatch($this->_mockMessage);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame("OK", $response->getMessage());
        $this->assertSame(array("Hdrkey" => "Hdrval"), $response->getHeaders());
        $this->assertSame("Message Body", $response->getBody());
    }

    /**
     * DataProvider for testing dispatch with different message timeout.
     *
     * @return array
     */
    public function timeoutDataProvider()
    {
        return array(
            array(0, \Magento\Outbound\Message::DEFAULT_TIMEOUT),
            array(null, \Magento\Outbound\Message::DEFAULT_TIMEOUT),
            array(5, 5),
            array(\Magento\Outbound\Message::DEFAULT_TIMEOUT, \Magento\Outbound\Message::DEFAULT_TIMEOUT)
        );
    }
}
