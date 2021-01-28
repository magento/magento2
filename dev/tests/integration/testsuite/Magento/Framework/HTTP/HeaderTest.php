<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP;

use Zend\Stdlib\Parameters;

class HeaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $_header;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_header = $objectManager->get(\Magento\Framework\HTTP\Header::class);

        /** @var \Magento\TestFramework\Request $request */
        $request = $objectManager->get(\Magento\TestFramework\Request::class);
        $request->setServer(new Parameters(['HTTP_HOST' => 'localhost']));
    }

    public function testGetHttpHeaderMethods()
    {
        $host = 'localhost';
        $this->assertEquals($host, $this->_header->getHttpHost());
        $this->assertFalse($this->_header->getHttpUserAgent());
        $this->assertFalse($this->_header->getHttpAcceptLanguage());
        $this->assertFalse($this->_header->getHttpAcceptCharset());
        $this->assertFalse($this->_header->getHttpReferer());
    }

    public function testGetRequestUri()
    {
        $this->assertEquals('/', $this->_header->getRequestUri());
    }
}
