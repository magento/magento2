<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Model\Message;

use Magento\Framework\Message\MessageInterface;
use Magento\UrlRewrite\Model\Message\UrlRewriteExceptionMessageFactory;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;

class UrlRewriteExceptionMessageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\Factory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageFactoryMock;

    /**
     * @var \Magento\Framework\UrlInterface| \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var \Magento\UrlRewrite\Model\Message\UrlRewriteExceptionMessageFactory
     */
    private $urlRewriteExceptionMessageFactory;

    protected function setUp()
    {
        $this->urlMock = $this->getMock(\Magento\Framework\UrlInterface::class);

        $this->messageFactoryMock = $this->getMock(
            \Magento\Framework\Message\Factory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->urlRewriteExceptionMessageFactory = new UrlRewriteExceptionMessageFactory(
            $this->messageFactoryMock,
            $this->urlMock
        );
    }

    public function testCreateMessage()
    {
        $exception = new \Exception('exception');
        $urlAlreadyExistsException = new UrlAlreadyExistsException(
            __('message'),
            $exception,
            0,
            [['request_path' => 'url']]
        );

        $this->urlMock->expects($this->once())
            ->method('getUrl')
            ->willReturn('htmlUrl');

        $message = $this->getMock(MessageInterface::class);

        $message->expects($this->once())
            ->method('setText')
            ->with($urlAlreadyExistsException->getMessage())
            ->willReturn($message);

        $message->expects($this->once())
            ->method('setIdentifier')
            ->with(UrlRewriteExceptionMessageFactory::ADD_URL_DUPLICATE_MESSAGE)
            ->willReturnSelf();

        $message->expects($this->once())
            ->method('setData')
            ->with(['urls' => ['htmlUrl' => 'url']])
            ->willReturn($message);

        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->with(MessageInterface::TYPE_ERROR)
            ->willReturn($message);

        $this->assertEquals(
            $message,
            $this->urlRewriteExceptionMessageFactory->createMessage($urlAlreadyExistsException)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     */
    public function testCreateMessageNotFound()
    {
        $exception = new \Exception('message');
        $this->urlRewriteExceptionMessageFactory->createMessage($exception);
    }
}
