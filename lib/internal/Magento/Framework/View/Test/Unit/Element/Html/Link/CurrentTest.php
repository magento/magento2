<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Html\Link;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Html\Link\Current;

class CurrentTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_urlBuilderMock;

    /**
     * @var MockObject
     */
    protected $_requestMock;

    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);
        $this->_urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->_requestMock = $this->createMock(Http::class);
    }

    public function testGetUrl()
    {
        $path = 'test/path';
        $url = 'http://example.com/asdasd';

        $this->_urlBuilderMock->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));

        /** @var Current $link */
        $link = $this->_objectManager->getObject(
            Current::class,
            ['urlBuilder' => $this->_urlBuilderMock]
        );

        $link->setPath($path);
        $this->assertEquals($url, $link->getHref());
    }

    public function testIsCurrentIfIsset()
    {
        /** @var Current $link */
        $link = $this->_objectManager->getObject(Current::class);
        $link->setCurrent(true);
        $this->assertTrue($link->isCurrent());
    }

    /**
     * Test if the current url is the same as link path
     *
     * @return void
     */
    public function testIsCurrent()
    {
        $path = 'test/index';
        $url = 'http://example.com/test/index';

        $this->_requestMock->expects($this->once())
            ->method('getPathInfo')
            ->will($this->returnValue('/test/index/'));
        $this->_requestMock->expects($this->once())
            ->method('getModuleName')
            ->will($this->returnValue('test'));
        $this->_requestMock->expects($this->once())
            ->method('getControllerName')
            ->will($this->returnValue('index'));
        $this->_requestMock->expects($this->once())
            ->method('getActionName')
            ->will($this->returnValue('index'));
        $this->_urlBuilderMock->expects($this->at(0))
            ->method('getUrl')
            ->with($path)
            ->will($this->returnValue($url));
        $this->_urlBuilderMock->expects($this->at(1))
            ->method('getUrl')
            ->with('test/index')
            ->will($this->returnValue($url));

        /** @var Current $link */
        $link = $this->_objectManager->getObject(
            Current::class,
            [
                'urlBuilder' => $this->_urlBuilderMock,
                'request' => $this->_requestMock
            ]
        );

        $link->setPath($path);
        $this->assertTrue($link->isCurrent());
    }

    public function testIsCurrentFalse()
    {
        $this->_urlBuilderMock->expects($this->at(0))->method('getUrl')->will($this->returnValue('1'));
        $this->_urlBuilderMock->expects($this->at(1))->method('getUrl')->will($this->returnValue('2'));

        /** @var Current $link */
        $link = $this->_objectManager->getObject(
            Current::class,
            ['urlBuilder' => $this->_urlBuilderMock, 'request' => $this->_requestMock]
        );
        $this->assertFalse($link->isCurrent());
    }
}
