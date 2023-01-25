<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Encryption\Test\Unit;

use Magento\Framework\Encryption\UrlCoder;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlCoderTest extends TestCase
{
    /**
     * @var UrlCoder
     */
    protected $_urlCoder;

    /**
     * @var MockObject
     */
    protected $_urlMock;

    /**
     * @var string
     */
    protected $_url = 'http://example.com';

    /**
     * @var string
     */
    protected $_encodeUrl = 'aHR0cDovL2V4YW1wbGUuY29t';

    protected function setUp(): void
    {
        $this->_urlMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->_urlCoder = new UrlCoder($this->_urlMock);
    }

    public function testDecode()
    {
        $this->_urlMock->expects(
            $this->once()
        )->method(
            'sessionUrlVar'
        )->with(
            $this->_url
        )->willReturn(
            'expected'
        );
        $this->assertEquals('expected', $this->_urlCoder->decode($this->_encodeUrl));
    }

    public function testEncode()
    {
        $this->assertEquals($this->_encodeUrl, $this->_urlCoder->encode($this->_url));
    }
}
