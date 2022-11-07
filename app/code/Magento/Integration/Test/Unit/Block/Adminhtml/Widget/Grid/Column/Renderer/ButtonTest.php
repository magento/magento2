<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Integration\Block\Adminhtml\Widget\Grid\Column\Renderer\Button;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class ButtonTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var Button
     */
    protected $buttonRenderer;

    protected function setUp(): void
    {
        $this->escaperMock = $this->createMock(Escaper::class);
        $this->escaperMock->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $this->escaperMock->expects($this->any())->method('escapeHtmlAttr')->willReturnArgument(0);
        $this->contextMock = $this->createPartialMock(Context::class, ['getEscaper']);
        $this->contextMock->expects($this->any())->method('getEscaper')->willReturn($this->escaperMock);
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

        $this->objectManagerHelper = new ObjectManager($this);
        $this->buttonRenderer = $this->objectManagerHelper->getObject(
            Button::class,
            ['context' => $this->contextMock, 'random' => $randomMock, 'secureRenderer' => $secureRendererMock]
        );
    }

    /**
     * Test the basic render action.
     */
    public function testRender()
    {
        $column = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getId', 'getIndex', 'getStyle', 'getOnclick'])
            ->getMock();
        $column->expects($this->any())
            ->method('getType')
            ->willReturn('bigButton');
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
