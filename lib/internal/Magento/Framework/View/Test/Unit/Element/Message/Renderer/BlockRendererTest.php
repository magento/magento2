<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
use Magento\Framework\View\Element\Message\Renderer\BlockRenderer;

class BlockRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockRenderer
     */
    private $renderer;

    /**
     * @var BlockRenderer\Template | \PHPUnit_Framework_MockObject_MockObject
     */
    private $blockTemplate;

    protected function setUp()
    {
        $this->blockTemplate = $this->getMockBuilder(
            'Magento\Framework\View\Element\Message\Renderer\BlockRenderer\Template'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->renderer = new BlockRenderer($this->blockTemplate);
    }

    public function testRender()
    {
        /** @var MessageInterface | \PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->getMock('Magento\Framework\Message\MessageInterface');
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
        /** @var MessageInterface | \PHPUnit_Framework_MockObject_MockObject $message */
        $message = $this->getMock('Magento\Framework\Message\MessageInterface');
        $messageData = [
            'who' => 'Brian',
            'is' => 'a Very Naughty Boy'
        ];

        $message->expects(static::once())
            ->method('getData')
            ->willReturn($messageData);

        $this->setExpectedException(
            'InvalidArgumentException',
            'Template should be provided for the renderer.'
        );

        $this->blockTemplate->expects(static::never())
            ->method('toHtml');

        $this->renderer->render($message, []);
    }
}
