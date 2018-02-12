<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\Renderer\DefaultRenderer;

class DefaultRendererTest extends \PHPUnit\Framework\TestCase
{
    public function testInterpret()
    {
        $messageText = 'Unescaped content';

        /** @var MessageInterface | \PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->createMock(\Magento\Framework\Message\MessageInterface::class);

        $message->expects(static::once())
            ->method('getText')
            ->willReturn($messageText);

        $renderer = new DefaultRenderer();
        static::assertSame($messageText, $renderer->render($message, []));
    }
}
