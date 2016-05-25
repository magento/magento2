<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\PageCache;

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class IdentifierTest extends \PHPUnit_Framework_TestCase
{
    /** Test value for cache vary string */
    const VARY = '123';

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @return \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param bool $expectToBeCalled
     *
     * @return Context | \PHPUnit_Framework_MockObject_MockObject
     */
    private function getContextMock($expectToBeCalled)
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->exactly((int)$expectToBeCalled))
            ->method('getVaryString')
            ->willReturn(self::VARY);
        return $contextMock;
    }

    /**
     * @param string $uri
     * @param bool $getVaryFromCookie
     * @param string $expected
     *
     * @dataProvider getValueDataProvider
     */
    public function testGetValue($uri, $getVaryFromCookie, $expected)
    {
        /** @var Http | \PHPUnit_Framework_MockObject_MockObject$requestMock */
        $requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestMock->method('getUriString')
            ->willReturn($uri);
        $requestMock->method('get')
            ->with($this->equalTo(Http::COOKIE_VARY_STRING))
            ->willReturn($getVaryFromCookie ? self::VARY : null);

        $context = $this->getContextMock(!$getVaryFromCookie);

        $model = $this->getModel($requestMock, $context);
        $this->assertEquals($expected, $model->getValue());
    }

    /**
     * @param Http $request
     * @param Context $context
     *
     * @return Identifier
     */
    private function getModel($request, $context)
    {
        return $this->objectManager->getObject(
            Identifier::class,
            [
                'request' => $request,
                'context' => $context,
            ]
        );
    }

    public function getValueDataProvider()
    {
        $uri = 'http://example.com/customer';
        $vary = self::VARY;
        $data = [$uri, $vary];
        ksort($data);
        $expectedWithVaryString = md5(serialize($data));

        return [
            'Vary string retrieved from cookie' => [$uri, true, $expectedWithVaryString],
            'Vary string retrieved from context' => [$uri, false, $expectedWithVaryString],
        ];
    }

    public function testCacheDifferentiators()
    {
        // Test that HTTP security status, path, and domain cause the identifier to be unique
        $secureValues = [true, false];
        $requestPaths = ['/request/path/1', '/request/2', '/third/request'];
        $domains = ['example.net', 'example.com'];
        $contextMock = $this->getContextMock(true);
        $varyStrings = [];
        $uris = [];
        foreach ($secureValues as $secure) {
            foreach ($requestPaths as $path) {
                foreach ($domains as $domain) {
                    $uris = ($secure ? 'https' : 'http') . '://' . $domain . $path;
                }
            }
        }
        $requestMock = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        for ($i = 0; $i < count($uris); $i++) {
            $requestMock->expects($this->at($i))->method('getUriString')->willReturn($uris[$i]);
        }
        $model = $this->getModel($requestMock, $contextMock);
        for ($i = 0; $i < count($uris); $i++) {
            $varyStrings[] = $model->getValue();
        }
        $this->assertEquals(
            count($varyStrings),
            count(array_unique($varyStrings)),
            'Vary strings for different URIs are the same.'
        );
    }
}
