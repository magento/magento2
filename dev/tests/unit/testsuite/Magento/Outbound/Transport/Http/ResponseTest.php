<?php 
/**
 * \Magento\Outbound\Transport\Http\Response
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Outbound\Transport\Http;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testIsSuccessfulTrue() 
    {
        $uut = new \Magento\Outbound\Transport\Http\Response("HTTP/2.0 299 OK");
        $this->assertTrue($uut->isSuccessful());
    }

    public function testIsSuccessfulFalse()
    {
        $uut = new \Magento\Outbound\Transport\Http\Response("HTTP/2.0 301 Moved Permanently");
        $this->assertFalse($uut->isSuccessful());
    }
    
    public function testGetStatusCode() 
    {
        $uut = new \Magento\Outbound\Transport\Http\Response("HTTP/2.0 299 OK");
        $this->assertSame(299, $uut->getStatusCode());
    }
    
    public function testGetMessage()
    {
        $uut = new \Magento\Outbound\Transport\Http\Response("HTTP/2.0 299 A-OK");
        $this->assertSame("A-OK", $uut->getMessage());
    }

    public function testGetBody()
    {
        $uut = new \Magento\Outbound\Transport\Http\Response("HTTP/2.0 200 OK\nHdrkey: Hdrval\n\nRaw Body");
        $this->assertSame("Raw Body", $uut->getBody());
    }
    
    public function testGetHeaders()
    {
        $hdrs = array('Key1' => 'val1', 'Key2' => 'val2');
        $uut = new \Magento\Outbound\Transport\Http\Response("HTTP/2.0 200 OK\nkey1: val1\nkey2: val2\n\nMessage Body");
        $this->assertEquals($hdrs, $uut->getHeaders());
        $this->assertEquals($hdrs['Key1'], $uut->getHeader('Key1'));
    }
}
