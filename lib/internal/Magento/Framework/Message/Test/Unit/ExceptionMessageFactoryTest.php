<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\ExceptionMessageFactory;
use Magento\Framework\Message\Factory;
use Magento\Framework\Message\MessageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExceptionMessageFactoryTest extends TestCase
{
    /**
     * @var Factory|MockObject
     */
    private $messageFactoryMock;

    /**
     * @var ExceptionMessageFactory
     */
    private $exceptionMessageFactory;

    protected function setUp(): void
    {
        $this->messageFactoryMock = $this->createPartialMock(
            Factory::class,
            ['create']
        );

        $this->exceptionMessageFactory = new ExceptionMessageFactory(
            $this->messageFactoryMock
        );
    }

    public function testCreateMessageDefaultType()
    {
        $exception = new \Exception('message');
        $message = $this->getMockForAbstractClass(MessageInterface::class);

        $message->expects($this->once())
            ->method('setText')
            ->with($exception->getMessage())
            ->willReturn($message);

        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->with(MessageInterface::TYPE_ERROR)
            ->willReturn($message);

        $this->assertEquals(
            $message,
            $this->exceptionMessageFactory->createMessage($exception)
        );
    }
}
