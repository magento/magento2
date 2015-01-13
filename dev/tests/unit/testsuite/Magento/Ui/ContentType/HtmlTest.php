<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\ContentType;

/**
 * Class HtmlTest
 */
class HtmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Html
     */
    protected $html;

    /**
     * @var \Magento\Framework\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Framework\View\TemplateEnginePool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateEnginePoolMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewInterfaceMock;

    public function setUp()
    {
        $this->filesystemMock = $this->getMock(
            'Magento\Framework\View\FileSystem',
            ['getTemplateFileName'],
            [],
            '',
            false
        );
        $this->templateEnginePoolMock = $this->getMock(
            'Magento\Framework\View\TemplateEnginePool',
            ['get'],
            [],
            '',
            false
        );
        $this->html = new Html($this->filesystemMock, $this->templateEnginePoolMock);
        $this->viewInterfaceMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponentInterface'
        );
    }

    public function testRender()
    {
        $template = 'test_template';
        $result = 'result';
        $path = 'path';
        $this->viewInterfaceMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponentInterface'
        );
        $templateEngineMock = $this->getMockForAbstractClass('Magento\Framework\View\TemplateEngineInterface');

        $this->templateEnginePoolMock->expects($this->once())
            ->method('get')
            ->willReturn($templateEngineMock);
        $this->filesystemMock->expects($this->once())
            ->method('getTemplateFileName')
            ->with($template)
            ->willReturn($path);
        $templateEngineMock->expects($this->once())
            ->method('render')
            ->with($this->viewInterfaceMock, $path)
            ->willReturn($result);

        $this->assertEquals($result, $this->html->render($this->viewInterfaceMock, $template));
    }

    public function testRenderEmpty()
    {
        $this->assertEquals('', $this->html->render($this->viewInterfaceMock, ''));
    }
}
