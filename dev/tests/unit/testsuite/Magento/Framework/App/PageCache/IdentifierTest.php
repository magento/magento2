<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

class IdentifierTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValue()
    {
        $uri = 'index.php/customer';
        $isSecure = 0;
        $vary = 1;
        $expected = md5(serialize([$isSecure, $uri, $vary]));

        $requestMock = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $requestMock->expects($this->once())->method('isSecure')->willReturn($isSecure);
        $requestMock->expects($this->once())->method('getRequestUri')->willReturn($uri);
        $requestMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $this->equalTo(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
        )->will(
            $this->returnValue($vary)
        );
        $model = new \Magento\Framework\App\PageCache\Identifier($requestMock);
        $result = $model->getValue();
        $this->assertEquals($expected, $result);
    }
}
