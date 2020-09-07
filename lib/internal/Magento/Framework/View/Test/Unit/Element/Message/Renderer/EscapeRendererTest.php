<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Message\Renderer;

use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\Renderer\EscapeRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
        $message = $this->getMockForAbstractClass(MessageInterface::class);

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
