<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
