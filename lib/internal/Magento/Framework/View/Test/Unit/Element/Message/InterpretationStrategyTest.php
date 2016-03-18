<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Message;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategy;
use Magento\Framework\View\Element\Message\MessageConfigurationsPool;
use Magento\Framework\View\Element\Message\Renderer\RendererInterface;
use Magento\Framework\View\Element\Message\Renderer\RenderersPool;

class InterpretationStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RenderersPool | \PHPUnit_Framework_MockObject_MockObject
     */
    private $renderersPool;

    /**
     * @var MessageConfigurationsPool | \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageConfigurationsPool;

    /**
     * @var RendererInterface  | \PHPUnit_Framework_MockObject_MockObject
     */
    private $renderer;

    /**
     * @var MessageInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageMock;

    /**
     * @var InterpretationStrategy
     */
    private $interpretationStrategy;

    protected function setUp()
    {
        $this->renderersPool = $this->getMockBuilder(
            'Magento\Framework\View\Element\Message\Renderer\RenderersPool'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageConfigurationsPool = $this->getMockBuilder(
            'Magento\Framework\View\Element\Message\MessageConfigurationsPool'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageMock = $this->getMock(
            'Magento\Framework\Message\MessageInterface'
        );
        $this->renderer = $this->getMock(
            'Magento\Framework\View\Element\Message\Renderer\RendererInterface'
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

        $renderer = $this->getMock(
            'Magento\Framework\View\Element\Message\Renderer\RendererInterface'
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

        $this->setExpectedException('LogicException');

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

        $this->setExpectedException('LogicException');

        $this->interpretationStrategy->interpret($this->messageMock);
    }
}
