<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP;

class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $_header;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_header = $objectManager->get('Magento\Framework\HTTP\Header');

        /** @var \Magento\TestFramework\Request $request */
        $request = $objectManager->get('Magento\TestFramework\Request');
        $request->setServer(['HTTP_HOST' => 'localhost']);
    }

    public function testGetHttpHeaderMethods()
    {
        $host = 'localhost';
        $this->assertEquals($host, $this->_header->getHttpHost());
        $this->assertEquals(false, $this->_header->getHttpUserAgent());
        $this->assertEquals(false, $this->_header->getHttpAcceptLanguage());
        $this->assertEquals(false, $this->_header->getHttpAcceptCharset());
        $this->assertEquals(false, $this->_header->getHttpReferer());
    }

    public function testGetRequestUri()
    {
        $this->assertNull($this->_header->getRequestUri());
    }
}
