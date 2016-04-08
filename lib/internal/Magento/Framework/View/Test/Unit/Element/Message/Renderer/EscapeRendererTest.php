<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Message\Renderer;

use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\Renderer\EscapeRenderer;

class EscapeRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testInterpret()
    {
        $messageText = 'Unescaped content';
        $escapedMessageText = 'Escaped content';

        /** @var Escaper | \PHPUnit_Framework_MockObject_MockObject $escaper */
        $escaper = $this->getMockBuilder(
            'Magento\Framework\Escaper'
        )
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MessageInterface | \PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->getMock('Magento\Framework\Message\MessageInterface');

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
