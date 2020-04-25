<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Message\Renderer;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\Renderer\EscapeRenderer;

class EscapeRendererTest extends TestCase
{
    public function testInterpret()
    {
        $messageText = 'Unescaped content';
        $escapedMessageText = 'Escaped content';

        /** @var Escaper|MockObject $escaper */
        $escaper = $this->getMockBuilder(
            Escaper::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MessageInterface|MockObject $message */
        $message = $this->createMock(MessageInterface::class);

        $message->expects(static::once())
            ->method('getText')
            ->willReturn($messageText);
        $escaper->expects(static::once())
            ->method('escapeHtml')
            ->with($messageText)
            ->willReturn($escapedMessageText);

        $renderer = new EscapeRenderer($escaper);
        static::assertSame($escapedMessageText, $renderer->render($message, []));
    }
}
