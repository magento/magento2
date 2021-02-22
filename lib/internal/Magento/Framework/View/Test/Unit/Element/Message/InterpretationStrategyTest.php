<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Message;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategy;
use Magento\Framework\View\Element\Message\MessageConfigurationsPool;
use Magento\Framework\View\Element\Message\Renderer\RendererInterface;
use Magento\Framework\View\Element\Message\Renderer\RenderersPool;

class InterpretationStrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RenderersPool | \PHPUnit\Framework\MockObject\MockObject
     */
    private $renderersPool;

    /**
     * @var MessageConfigurationsPool | \PHPUnit\Framework\MockObject\MockObject
     */
    private $messageConfigurationsPool;

    /**
     * @var RendererInterface  | \PHPUnit\Framework\MockObject\MockObject
     */
    private $renderer;

    /**
     * @var MessageInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $messageMock;

    /**
     * @var InterpretationStrategy
     */
    private $interpretationStrategy;

    protected function setUp(): void
    {
        $this->renderersPool = $this->getMockBuilder(
            \Magento\Framework\View\Element\Message\Renderer\RenderersPool::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageConfigurationsPool = $this->getMockBuilder(
            \Magento\Framework\View\Element\Message\MessageConfigurationsPool::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageMock = $this->createMock(
            \Magento\Framework\Message\MessageInterface::class
        );
        $this->renderer = $this->createMock(
            \Magento\Framework\View\Element\Message\Renderer\RendererInterface::class
        );

        $this->interpretationStrategy = new InterpretationStrategy(
            $this->renderersPool,
            $this->messageConfigurationsPool
        );
    }

    public function testInterpret()
    {
        $identifier = 'Prophesy';
        $messageConfiguration = [
            'renderer' => 'Third apostle from the left',
            'data' => [
                'Blessed are the birds...'
            ]
        ];
        $renderedMessage = 'Script';

        $renderer = $this->createMock(
            \Magento\Framework\View\Element\Message\Renderer\RendererInterface::class
        );

        $this->messageMock->expects(static::once())
            ->method('getIdentifier')
            ->willReturn($identifier);
        $this->messageConfigurationsPool->expects(static::once())
            ->method('getMessageConfiguration')
            ->with($identifier)
            ->willReturn(
                $messageConfiguration
            );
        $this->renderersPool->expects(static::once())
            ->method('get')
            ->with($messageConfiguration['renderer'])
            ->willReturn($renderer);
        $renderer->expects(static::once())
            ->method('render')
            ->with($this->messageMock, $messageConfiguration['data'])
            ->willReturn($renderedMessage);

        static::assertSame(
            $renderedMessage,
            $this->interpretationStrategy->interpret($this->messageMock)
        );
    }

    public function testInterpretNoConfigurationException()
    {
        $identifier = 'Prophesy';

        $this->messageMock->expects(static::once())
            ->method('getIdentifier')
            ->willReturn($identifier);
        $this->messageConfigurationsPool->expects(static::once())
            ->method('getMessageConfiguration')
            ->with($identifier)
            ->willReturn(
                null
            );

        $this->expectException('LogicException');

        $this->renderersPool->expects(static::never())
            ->method('get');

        $this->interpretationStrategy->interpret($this->messageMock);
    }

    public function testInterpretNoInterpreterException()
    {
        $identifier = 'Prophesy';
        $messageConfiguration = [
            'renderer' => 'Third apostle from the left',
            'data' => [
                'Blessed are the birds...'
            ]
        ];

        $this->messageMock->expects(static::once())
            ->method('getIdentifier')
            ->willReturn($identifier);
        $this->messageConfigurationsPool->expects(static::once())
            ->method('getMessageConfiguration')
            ->with($identifier)
            ->willReturn(
                $messageConfiguration
            );
        $this->renderersPool->expects(static::once())
            ->method('get')
            ->with($messageConfiguration['renderer'])
            ->willReturn(null);

        $this->expectException('LogicException');

        $this->interpretationStrategy->interpret($this->messageMock);
    }
}
