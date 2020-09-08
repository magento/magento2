<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use Magento\Framework\View\Element\Message\Renderer\BlockRenderer;
use Magento\Framework\View\Element\Message\Renderer\BlockRenderer\Template;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BlockRendererTest extends TestCase
{
    /**
     * @var BlockRenderer
     */
    private $renderer;

    /**
     * @var BlockRenderer\Template|MockObject
     */
    private $blockTemplate;

    protected function setUp(): void
    {
        $this->blockTemplate = $this->getMockBuilder(
            Template::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->renderer = new BlockRenderer($this->blockTemplate);
    }

    public function testRender()
    {
        /** @var MessageInterface|MockObject $message */
        $message = $this->getMockForAbstractClass(MessageInterface::class);
        $messageData = [
            'painting' => 'The Last Supper',
            'apostles_cnt' => 28,
            'kangaroos_cnt' => 1
        ];
        $initializationData = ['template' => 'canvas.phtml'];
        $messagePresentation = 'The Last Supper, Michelangelo.';

        $message->expects(static::once())
            ->method('getData')
            ->willReturn($messageData);
        $this->blockTemplate->expects(new MethodInvokedAtIndex(0))
            ->method('setTemplate')
            ->with($initializationData['template']);
        $this->blockTemplate->expects(static::once())
            ->method('setData')
            ->with($messageData);

        $this->blockTemplate->expects(static::once())
            ->method('toHtml')
            ->willReturn($messagePresentation);

        $this->blockTemplate->expects(new MethodInvokedAtIndex(0))
            ->method('unsetData')
            ->with('painting');
        $this->blockTemplate->expects(new MethodInvokedAtIndex(1))
            ->method('unsetData')
            ->with('apostles_cnt');
        $this->blockTemplate->expects(new MethodInvokedAtIndex(2))
            ->method('unsetData')
            ->with('kangaroos_cnt');
        $this->blockTemplate->expects(new MethodInvokedAtIndex(1))
            ->method('setTemplate')
            ->with('');

        $this->renderer->render($message, $initializationData);
    }

    public function testRenderNoTemplate()
    {
        /** @var MessageInterface|MockObject $message */
        $message = $this->getMockForAbstractClass(MessageInterface::class);
        $messageData = [
            'who' => 'Brian',
            'is' => 'a Very Naughty Boy'
        ];

        $message->expects(static::once())
            ->method('getData')
            ->willReturn($messageData);

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Template should be provided for the renderer.');

        $this->blockTemplate->expects(static::never())
            ->method('toHtml');

        $this->renderer->render($message, []);
    }
}
