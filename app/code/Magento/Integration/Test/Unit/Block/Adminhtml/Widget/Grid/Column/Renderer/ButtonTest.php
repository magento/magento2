<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class ButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button
     */
    protected $buttonRenderer;

    protected function setUp()
    {
        $this->escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->escaperMock->expects($this->any())->method('escapeHtmlAttr')->willReturnArgument(0);
        $this->contextMock = $this->createPartialMock(\Magento\Backend\Block\Context::class, ['getEscaper']);
        $this->contextMock->expects($this->any())->method('getEscaper')->will($this->returnValue($this->escaperMock));
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('random');
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderTag')
            ->willReturnCallback(
                function (string $tag, array $attributes, string $content): string {
                    $attributes = new DataObject($attributes);

                    return "<$tag {$attributes->serialize()}>$content</$tag>";
                }
            );
        $secureRendererMock->method('renderEventListenerAsTag')
            ->willReturnCallback(
                function (string $event, string $js, string $selector): string {
                    return "<script>document.querySelector('$selector').$event = function () { $js };</script>";
                }
            );
        $secureRendererMock->method('renderStyleAsTag')
            ->willReturnCallback(
                function (string $style, string $selector): string {
                    return "<style>$selector { $style }</style>";
                }
            );

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->buttonRenderer = $this->objectManagerHelper->getObject(
            \Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button::class,
            ['context' => $this->contextMock, 'random' => $randomMock, 'secureRenderer' => $secureRendererMock]
        );
    }

    /**
     * Test the basic render action.
     */
    public function testRender()
    {
        $column = $this->getMockBuilder(\Magento\Backend\Block\Widget\Grid\Column::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getId', 'getIndex', 'getStyle', 'getOnclick'])
            ->getMock();
        $column->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('bigButton'));
        $column->expects($this->any())
            ->method('getId')
            ->willReturn('1');
        $column->expects($this->any())
            ->method('getIndex')
            ->willReturn('name');
        $column->expects($this->any())
            ->method('getStyle')
            ->willReturn('display: block;');
        $column->expects($this->any())
            ->method('getOnclick')
            ->willReturn('alert(1);');
        $this->buttonRenderer->setColumn($column);

        $object = new DataObject(['name' => 'my button']);
        $actualResult = $this->buttonRenderer->render($object);
        $this->assertEquals(
            '<button id="1" type="bigButton" button-renderer-hook-id="hookrandom">my button</button>'
            .'<style>[button-renderer-hook-id=\'hookrandom\'] { display: block; }</style>'
            .'<script>document.querySelector(\'*[button-renderer-hook-id=\'hookrandom\']\').onclick = '
            .'function () { alert(1); };</script>',
            $actualResult
        );
    }
}
