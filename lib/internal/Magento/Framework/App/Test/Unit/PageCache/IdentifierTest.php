<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\PageCache;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class IdentifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }
    /**
     * @param bool $isSecure
     * @param string $uri
     * @param string|null $vary
     * @return \Magento\Framework\App\Request\Http
     */
    protected function getRequestMock($isSecure, $uri, $vary = null)
    {
        $requestMock = $this->getMock('\Magento\Framework\App\Request\Http', [], [], '', false);
        $requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn($isSecure);
        $requestMock->expects($this->once())
            ->method('getRequestUri')
            ->willReturn($uri);
        $requestMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING))
            ->willReturn($vary);
        return $requestMock;
    }
    /**
     * @param int $getVeryStringCalledTimes
     * @param string|null $vary
     * @return \Magento\Framework\App\Http\Context
     */
    protected function getContextMock($getVeryStringCalledTimes, $vary)
    {
        $contextMock = $this->getMock('\Magento\Framework\App\Http\Context', [], [], '', false);
        $contextMock->expects($this->exactly($getVeryStringCalledTimes))
            ->method('getVaryString')
            ->willReturn($vary);
        return $contextMock;
    }
    /**
     * @param bool $isSecure
     * @param string $uri
     * @param string|null $varyStringCookie
     * @param string|null $varyStringContext
     * @param string $expected
     * @dataProvider dataProvider
     */
    public function testGetValue($isSecure, $uri, $varyStringCookie, $varyStringContext, $expected)
    {
        $request = $this->getRequestMock($isSecure, $uri, $varyStringCookie);
        $context = $this->getContextMock($varyStringCookie ? 0 : 1, $varyStringContext);
        $model = $this->objectManager->getObject(
            '\Magento\Framework\App\PageCache\Identifier',
            [
                'request' => $request,
                'context' => $context,
            ]
        );
        $this->assertEquals($expected, $model->getValue());
    }
    /**
     * @return array
     */
    public function dataProvider()
    {
        $uri = 'index.php/customer';
        $isSecure = 0;
        $vary = 1;
        $data = [$isSecure, $uri, $vary];
        ksort($data);
        $expected = md5(serialize($data));
        return [
            [$isSecure, $uri, $vary, null, $expected],
            [$isSecure, $uri, null, $vary, $expected]
        ];
    }
}
