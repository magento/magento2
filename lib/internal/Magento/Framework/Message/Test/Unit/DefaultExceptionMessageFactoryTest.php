<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\MessageInterface;

class DefaultExceptionMessageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\Factory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageFactoryMock;

    /**
     * @var \Magento\Framework\Message\DefaultExceptionMessageFactory
     */
    private $defaultExceptionMessageFactory;


    protected function setUp()
    {
        $this->messageFactoryMock = $this->getMock(
            \Magento\Framework\Message\Factory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->defaultExceptionMessageFactory = new \Magento\Framework\Message\DefaultExceptionMessageFactory(
            $this->messageFactoryMock
        );
    }

    public function testCreateMessageDefaultType()
    {
        $exception = new \Exception('message');
        $message = $this->getMock(MessageInterface::class);

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
            $this->defaultExceptionMessageFactory->createMessage($exception)
        );
    }
}
