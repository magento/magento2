<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\PageCache;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class IdentifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test value for cache vary string
     */
    const VARY = '123';

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Identifier
     */
    private $model;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @return \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->setMethods(['serialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );

        $this->model = $this->objectManager->getObject(
            Identifier::class,
            [
                'request'    => $this->requestMock,
                'context'    => $this->contextMock,
                'serializer' => $this->serializerMock,
            ]
        );
        return parent::setUp();
    }

    public function testSecureDifferentiator()
    {
        $this->requestMock->expects($this->at(0))
            ->method('isSecure')
            ->willReturn(true);
        $this->requestMock->expects($this->at(3))
            ->method('isSecure')
            ->willReturn(false);
        $this->requestMock->method('getUriString')
            ->willReturn('http://example.com/path/');
        $this->contextMock->method('getVaryString')->willReturn(self::VARY);

        $valueWithSecureRequest = $this->model->getValue();
        $valueWithInsecureRequest = $this->model->getValue();
        $this->assertNotEquals($valueWithSecureRequest, $valueWithInsecureRequest);
    }

    public function testDomainDifferentiator()
    {
        $this->requestMock->method('isSecure')->willReturn(true);
        $this->requestMock->expects($this->at(1))
            ->method('getUriString')
            ->willReturn('http://example.com/path/');
        $this->requestMock->expects($this->at(4))
            ->method('getUriString')
            ->willReturn('http://example.net/path/');
        $this->contextMock->method('getVaryString')->willReturn(self::VARY);

        $valueDomain1 = $this->model->getValue();
        $valueDomain2 = $this->model->getValue();
        $this->assertNotEquals($valueDomain1, $valueDomain2);
    }

    public function testPathDifferentiator()
    {
        $this->requestMock->method('isSecure')->willReturn(true);
        $this->requestMock->expects($this->at(1))
            ->method('getUriString')
            ->willReturn('http://example.com/path/');
        $this->requestMock->expects($this->at(4))
            ->method('getUriString')
            ->willReturn('http://example.com/path1/');
        $this->contextMock->method('getVaryString')->willReturn(self::VARY);

        $valuePath1 = $this->model->getValue();
        $valuePath2 = $this->model->getValue();
        $this->assertNotEquals($valuePath1, $valuePath2);
    }

    /**
     * @param $cookieExists
     *
     * @dataProvider trueFalseDataProvider
     */
    public function testVaryStringSource($cookieExists)
    {
        $this->requestMock->method('get')->willReturn($cookieExists ? 'vary-string-from-cookie' : null);
        $this->contextMock->expects($cookieExists ? $this->never() : $this->once())->method('getVaryString');
        $this->model->getValue();
    }

    /**
     * @return array
     */
    public function trueFalseDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * Test get identifier value
     */
    public function testGetValue()
    {
        $this->requestMock->expects($this->any())
            ->method('isSecure')
            ->will($this->returnValue(true));

        $this->requestMock->expects($this->any())
            ->method('getUriString')
            ->willReturn('http://example.com/path1/');

        $this->contextMock->expects($this->any())
            ->method('getVaryString')
            ->will($this->returnValue(self::VARY));

        $this->assertEquals(
            sha1(
                json_encode(
                    [
                        true,
                        'http://example.com/path1/',
                        self::VARY
                    ]
                )
            ),
            $this->model->getValue()
        );
    }
}
