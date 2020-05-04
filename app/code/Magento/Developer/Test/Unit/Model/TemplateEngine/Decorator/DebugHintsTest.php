<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\TemplateEngine\Decorator;

use Magento\Framework\DataObject;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class DebugHintsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param bool $showBlockHints
     * @dataProvider renderDataProvider
     */
    public function testRender($showBlockHints)
    {
        $subject = $this->createMock(\Magento\Framework\View\TemplateEngineInterface::class);
        $block = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->setMockClassName('TestBlock')
            ->getMock();
        $subject->expects(
            $this->once()
        )->method(
            'render'
        )->with(
            $this->identicalTo($block),
            'template.phtml',
            ['var' => 'val']
        )->will(
            $this->returnValue('<div id="fixture"/>')
        );
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

        $model = new \Magento\Developer\Model\TemplateEngine\Decorator\DebugHints(
            $subject,
            $showBlockHints,
            $secureRendererMock,
            $randomMock
        );
        $actualResult = $model->render($block, 'template.phtml', ['var' => 'val']);
        $this->assertNotNull($actualResult);
    }

    /**
     * @return array
     */
    public function renderDataProvider()
    {
        return ['block hints disabled' => [false], 'block hints enabled' => [true]];
    }
}
