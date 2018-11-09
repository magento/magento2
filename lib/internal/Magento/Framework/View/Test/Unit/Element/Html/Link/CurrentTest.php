<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Html\Link;

class CurrentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->_requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
    }

    public function testGetUrl()
    {
        $path = 'test/path';
        $url = 'http://example.com/asdasd';

        $this->_urlBuilderMock->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url));

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
     * @dataProvider linkPathProvider
     * @param string $linkPath
     * @param string $currentPathInfo
     * @param bool $expected
     * @return void
     */
    public function testIsCurrent($linkPath, $currentPathInfo, $expected)
    {
        $baseUrl = 'http://example.com/';
        $trimmed = trim($currentPathInfo, '/');

        $this->_requestMock->expects($this->any())->method('getPathInfo')->willReturn($currentPathInfo);
        $this->_urlBuilderMock->expects($this->at(0))
            ->method('getUrl')
            ->with($linkPath)
            ->will($this->returnValue($baseUrl . $linkPath));
        $this->_urlBuilderMock->expects($this->at(1))
            ->method('getUrl')
            ->with($trimmed)
            ->will($this->returnValue($baseUrl . $trimmed));
        /** @var \Magento\Framework\View\Element\Html\Link\Current $link */
        $link = $this->_objectManager->getObject(
            \Magento\Framework\View\Element\Html\Link\Current::class,
            [
                'urlBuilder' => $this->_urlBuilderMock,
                'request' => $this->_requestMock
            ]
        );

        $link->setCurrent(false);
        $link->setPath($linkPath);
        $this->assertEquals($expected, $link->isCurrent());
    }

    /**
     * @return array
     */
    public function linkPathProvider()
    {
        return [
            ['test/index', '/test/index/', true],
            ['test/index/index', '/test/index/index/', true],
            ['test/route', '/test/index/', false],
            ['test/index', '/test/', false]
        ];
    }

    public function testIsCurrentFalse()
    {
        $this->_urlBuilderMock->expects($this->at(0))->method('getUrl')->will($this->returnValue('1'));
        $this->_urlBuilderMock->expects($this->at(1))->method('getUrl')->will($this->returnValue('2'));

        /** @var \Magento\Framework\View\Element\Html\Link\Current $link */
        $link = $this->_objectManager->getObject(
            \Magento\Framework\View\Element\Html\Link\Current::class,
            ['urlBuilder' => $this->_urlBuilderMock, 'request' => $this->_requestMock]
        );
        $this->assertFalse($link->isCurrent());
    }
}
