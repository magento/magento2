<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Html\Link;

class CurrentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_urlBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
    }

    public function testGetUrl()
    {
        $path = 'test/path';
        $url = 'http://example.com/asdasd';

        $this->_urlBuilderMock->expects($this->once())->method('getUrl')->with($path)->willReturn($url);

        /** @var \Magento\Framework\View\Element\Html\Link\Current $link */
        $link = $this->_objectManager->getObject(
            \Magento\Framework\View\Element\Html\Link\Current::class,
            ['urlBuilder' => $this->_urlBuilderMock]
        );

        $link->setPath($path);
        $this->assertEquals($url, $link->getHref());
    }

    public function testIsCurrentIfIsset()
    {
        /** @var \Magento\Framework\View\Element\Html\Link\Current $link */
        $link = $this->_objectManager->getObject(\Magento\Framework\View\Element\Html\Link\Current::class);
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
            ->willReturn('/test/index/');
        $this->_requestMock->expects($this->once())
            ->method('getModuleName')
            ->willReturn('test');
        $this->_requestMock->expects($this->once())
            ->method('getControllerName')
            ->willReturn('index');
        $this->_requestMock->expects($this->once())
            ->method('getActionName')
            ->willReturn('index');
        $this->_urlBuilderMock->expects($this->at(0))
            ->method('getUrl')
            ->with($path)
            ->willReturn($url);
        $this->_urlBuilderMock->expects($this->at(1))
            ->method('getUrl')
            ->with('test/index')
            ->willReturn($url);

        /** @var \Magento\Framework\View\Element\Html\Link\Current $link */
        $link = $this->_objectManager->getObject(
            \Magento\Framework\View\Element\Html\Link\Current::class,
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
        $this->_urlBuilderMock->expects($this->at(0))->method('getUrl')->willReturn('1');
        $this->_urlBuilderMock->expects($this->at(1))->method('getUrl')->willReturn('2');

        /** @var \Magento\Framework\View\Element\Html\Link\Current $link */
        $link = $this->_objectManager->getObject(
            \Magento\Framework\View\Element\Html\Link\Current::class,
            ['urlBuilder' => $this->_urlBuilderMock, 'request' => $this->_requestMock]
        );
        $this->assertFalse($link->isCurrent());
    }
}
