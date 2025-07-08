<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\HTTP\Test\Unit;

use Magento\Framework\App\Request\Http;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class HeaderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Http
     */
    protected $_request;

    /**
     * @var StringUtils
     */
    protected $_converter;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);

        $this->_request =
            $this->createPartialMock(Http::class, ['getServer', 'getRequestUri']);

        $this->_converter = $this->createPartialMock(StringUtils::class, ['cleanString']);
    }

    /**
     * @param string $method
     * @param boolean $clean
     * @param string $expectedValue
     *
     * @dataProvider methodsDataProvider
     *
     * @covers \Magento\Framework\HTTP\Header::getHttpHost
     * @covers \Magento\Framework\HTTP\Header::getHttpUserAgent
     * @covers \Magento\Framework\HTTP\Header::getHttpAcceptLanguage
     * @covers \Magento\Framework\HTTP\Header::getHttpAcceptCharset
     * @covers \Magento\Framework\HTTP\Header::getHttpReferer
     */
    public function testHttpMethods($method, $clean, $expectedValue)
    {
        $this->_request->expects($this->once())->method('getServer')->willReturn('value');

        $this->_prepareCleanString($clean);

        $headerObject = $this->_objectManager->getObject(
            Header::class,
            ['httpRequest' => $this->_request, 'converter' => $this->_converter]
        );

        $method = new \ReflectionMethod(Header::class, $method);
        $result = $method->invokeArgs($headerObject, ['clean' => $clean]);

        $this->assertEquals($expectedValue, $result);
    }

    /**
     * @return array
     */
    public static function methodsDataProvider()
    {
        return [
            'getHttpHost clean true' => [
                'method' => 'getHttpHost',
                'clean' => true,
                'expectedValue' => 'converted value',
            ],
            'getHttpHost clean false' => [
                'method' => 'getHttpHost',
                'clean' => false,
                'expectedValue' => 'value',
            ],
            'getHttpUserAgent clean true' => [
                'method' => 'getHttpUserAgent',
                'clean' => true,
                'expectedValue' => 'converted value',
            ],
            'getHttpUserAgent clean false' => [
                'method' => 'getHttpUserAgent',
                'clean' => false,
                'expectedValue' => 'value',
            ],
            'getHttpAcceptLanguage clean true' => [
                'method' => 'getHttpAcceptLanguage',
                'clean' => true,
                'expectedValue' => 'converted value',
            ],
            'getHttpAcceptLanguage clean false' => [
                'method' => 'getHttpAcceptLanguage',
                'clean' => false,
                'expectedValue' => 'value',
            ],
            'getHttpAcceptCharset clean true' => [
                'method' => 'getHttpAcceptCharset',
                'clean' => true,
                'expectedValue' => 'converted value',
            ],
            'getHttpAcceptCharset clean false' => [
                'method' => 'getHttpAcceptCharset',
                'clean' => false,
                'expectedValue' => 'value',
            ],
            'getHttpReferer clean true' => [
                'method' => 'getHttpReferer',
                'clean' => true,
                'expectedValue' => 'converted value',
            ],
            'getHttpReferer clean false' => [
                'method' => 'getHttpReferer',
                'clean' => false,
                'expectedValue' => 'value',
            ]
        ];
    }

    /**
     * @param boolean $clean
     * @param string $expectedValue
     *
     * @dataProvider getRequestUriDataProvider
     */
    public function testGetRequestUri($clean, $expectedValue)
    {
        $this->_request->expects($this->once())->method('getRequestUri')->willReturn('value');

        $this->_prepareCleanString($clean);

        $headerObject = $this->_objectManager->getObject(
            Header::class,
            ['httpRequest' => $this->_request, 'converter' => $this->_converter]
        );

        $result = $headerObject->getRequestUri($clean);

        $this->assertEquals($expectedValue, $result);
    }

    /**
     * @return array
     */
    public static function getRequestUriDataProvider()
    {
        return [
            'getRequestUri clean true' => ['clean' => true, 'expectedValue' => 'converted value'],
            'getRequestUri clean false' => ['clean' => false, 'expectedValue' => 'value']
        ];
    }

    /**
     * @param boolean $clean
     * @return $this
     */
    protected function _prepareCleanString($clean)
    {
        $cleanStringExpects = $clean ? $this->once() : $this->never();

        $this->_converter->expects(
            $cleanStringExpects
        )->method(
            'cleanString'
        )->willReturn(
            'converted value'
        );
        return $this;
    }
}
