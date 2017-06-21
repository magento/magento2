<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\MessageInterface;

class ExceptionMessageLookupFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\ExceptionMessageFactoryPool | \PHPUnit_Framework_MockObject_MockObject
     */
    private $exceptionMessageFactoryPool;

    /**
     * @var \Magento\Framework\Message\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageFactory;

    /**
     * @var \Magento\Framework\Message\ExceptionMessageLookupFactory
     */
    private $exceptionMessageLookupFactory;

    protected function setUp()
    {
        $this->exceptionMessageFactoryPool = $this->getMock(
            \Magento\Framework\Message\ExceptionMessageFactoryPool::class,
            ['getMessageFactory'],
            [],
            '',
            false
        );

        $this->messageFactory = $this->getMockBuilder(
            \Magento\Framework\Message\Factory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->exceptionMessageLookupFactory = new \Magento\Framework\Message\ExceptionMessageLookupFactory(
            $this->exceptionMessageFactoryPool
        );
    }

    public function test()
    {
        $exceptionMessage = 'exception message';
        $exception = new \Exception($exceptionMessage);

        $exceptionMessageFactory = $this->getMock(
            \Magento\Framework\Message\ExceptionMessageFactoryInterface::class
        );

        $this->exceptionMessageFactoryPool->expects(
            $this->once()
        )->method(
            'getMessageFactory'
        )->with(
            $exception
        )->will(
            $this->returnValue($exceptionMessageFactory)
        );

        $messageError = $this->getMockBuilder(
            \Magento\Framework\Message\Error::class
        )->getMock();

        $this->messageFactory->expects($this->never())
            ->method('create');

        $exceptionMessageFactory->expects($this->once())
            ->method('createMessage')
            ->with($exception, MessageInterface::TYPE_ERROR)
            ->will($this->returnValue($messageError));

        $this->assertEquals($messageError, $this->exceptionMessageLookupFactory->createMessage($exception));
    }
}
