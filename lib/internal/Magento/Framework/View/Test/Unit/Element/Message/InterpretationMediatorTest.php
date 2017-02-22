<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Message;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\InterpretationMediator;
use Magento\Framework\View\Element\Message\InterpretationStrategy;

class InterpretationMediatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InterpretationStrategy | \PHPUnit_Framework_MockObject_MockObject
     */
    private $interpretationStrategy;

    /**
     * @var MessageInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageMock;

    /**
     * @var InterpretationMediator
     */
    private $interpretationMediator;

    public function setUp()
    {
        $this->interpretationStrategy = $this->getMockBuilder(
            'Magento\Framework\View\Element\Message\InterpretationStrategy'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageMock = $this->getMock(
            'Magento\Framework\Message\MessageInterface'
        );

        $this->interpretationMediator = new InterpretationMediator(
            $this->interpretationStrategy
        );
    }

    public function testInterpretNotIdentifiedMessage()
    {
        $messageText = 'Very awful message. Shame.';
        $this->messageMock->expects(static::once())
            ->method('getIdentifier')
            ->willReturn(null);
        $this->messageMock->expects(static::once())
            ->method('getText')
            ->willReturn($messageText);

        static::assertSame(
            $messageText,
            $this->interpretationMediator->interpret($this->messageMock)
        );
    }

    public function testInterpretIdentifiedMessage()
    {
        $messageInterpreted = 'Awesome message. An identified message is the one we appreciate.';

        $this->messageMock->expects(static::once())
            ->method('getIdentifier')
            ->willReturn('Great identifier');
        $this->interpretationStrategy->expects(static::once())
            ->method('interpret')
            ->with($this->messageMock)
            ->willReturn($messageInterpreted);

        static::assertSame(
            $messageInterpreted,
            $this->interpretationMediator->interpret($this->messageMock)
        );
    }

    public function testInterpretIdentifiedMessageNotConfigured()
    {
        $messageStillNotInterpreted = 'One step left to call it an awesome message.';

        $this->messageMock->expects(static::once())
            ->method('getIdentifier')
            ->willReturn('Great identifier');
        $this->messageMock->expects(static::once())
            ->method('getText')
            ->willReturn($messageStillNotInterpreted);
        $this->interpretationStrategy->expects(static::once())
            ->method('interpret')
            ->with($this->messageMock)
            ->willThrowException(new \LogicException());

        static::assertSame(
            $messageStillNotInterpreted,
            $this->interpretationMediator->interpret($this->messageMock)
        );
    }
}
