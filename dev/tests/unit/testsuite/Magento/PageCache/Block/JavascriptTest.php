<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Block;

/**
 * @covers \Magento\PageCache\Block\Javascript
 */
class JavascriptTest extends \PHPUnit_Framework_TestCase
{
    const COOKIE_NAME = 'private_content_version';

    /**
     * @var \Magento\PageCache\Block\Javascript|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockJavascript;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutUpdateMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'isSecure',
                    'getModuleName',
                    'setModuleName',
                    'getActionName',
                    'setActionName',
                    'getParam',
                    'getCookie'
                ]
            )
            ->getMock();
        $this->layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutUpdateMock = $this->getMockBuilder('Magento\Framework\View\Layout\ProcessorInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        $this->contextMock->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);
        $this->layoutMock->expects($this->any())
            ->method('getUpdate')
            ->willReturn($this->layoutUpdateMock);
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->blockJavascript = $objectManager->getObject(
            'Magento\PageCache\Block\Javascript',
            [
                'context' => $this->contextMock
            ]
        );
    }

    /**
     * @covers \Magento\PageCache\Block\Javascript::getScriptOptions
     * @param bool $isSecure
     * @param string $url
     * @param string $expectedResult
     * @dataProvider getScriptOptionsDataProvider
     */
    public function testGetScriptOptions($isSecure, $url, $expectedResult)
    {
        $handles = [
            'some',
            'handles',
            'here'
        ];
        $this->requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn($isSecure);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($url);
        $this->layoutUpdateMock->expects($this->once())
            ->method('getHandles')
            ->willReturn($handles);
        $this->assertRegExp($expectedResult, $this->blockJavascript->getScriptOptions());
    }

    public function getScriptOptionsDataProvider()
    {
        return [
            'http' => [
                'isSecure' => false,
                'url' => 'http://some-name.com/page_cache/block/render',
                'expectedResult' => '~http:\\\\/\\\\/some-name\\.com.+\\["some","handles","here"\\]~'
            ],
            'https' => [
                'isSecure' => true,
                'url' => 'https://some-name.com/page_cache/block/render',
                'expectedResult' => '~https:\\\\/\\\\/some-name\\.com.+\\["some","handles","here"\\]~'
            ]
        ];
    }
}
