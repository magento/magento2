<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\Error;
use Magento\Framework\Message\ExceptionMessageFactoryInterface;
use Magento\Framework\Message\ExceptionMessageFactoryPool;
use Magento\Framework\Message\ExceptionMessageLookupFactory;
use Magento\Framework\Message\Factory;
use Magento\Framework\Message\MessageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExceptionMessageLookupFactoryTest extends TestCase
{
    /**
     * @var ExceptionMessageFactoryPool|MockObject
     */
    private $exceptionMessageFactoryPool;

    /**
     * @var Factory|MockObject
     */
    private $messageFactory;

    /**
     * @var ExceptionMessageLookupFactory
     */
    private $exceptionMessageLookupFactory;

    protected function setUp(): void
    {
        $this->exceptionMessageFactoryPool = $this->createPartialMock(
            ExceptionMessageFactoryPool::class,
            ['getMessageFactory']
        );

        $this->messageFactory = $this->getMockBuilder(
            Factory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->exceptionMessageLookupFactory = new ExceptionMessageLookupFactory(
            $this->exceptionMessageFactoryPool
        );
    }

    public function testCreateMessage()
    {
        $exceptionMessage = 'exception message';
        $exception = new \Exception($exceptionMessage);

        $exceptionMessageFactory = $this->createMock(
            ExceptionMessageFactoryInterface::class
        );

        $this->exceptionMessageFactoryPool->expects(
            $this->once()
        )->method(
            'getMessageFactory'
        )->with(
            $exception
        )->willReturn(
            $exceptionMessageFactory
        );

        $messageError = $this->getMockBuilder(
            Error::class
        )->getMock();

        $this->messageFactory->expects($this->never())
            ->method('create');

        $exceptionMessageFactory->expects($this->once())
            ->method('createMessage')
            ->with($exception, MessageInterface::TYPE_ERROR)
            ->willReturn($messageError);

        $this->assertEquals($messageError, $this->exceptionMessageLookupFactory->createMessage($exception));
    }
}
