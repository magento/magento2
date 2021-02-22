<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Test\Unit\Model\Message;

use Magento\Framework\Message\MessageInterface;
use Magento\UrlRewrite\Model\Message\UrlRewriteExceptionMessageFactory;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;

class UrlRewriteExceptionMessageFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Message\Factory | \PHPUnit\Framework\MockObject\MockObject
     */
    private $messageFactoryMock;

    /**
     * @var \Magento\Framework\UrlInterface| \PHPUnit\Framework\MockObject\MockObject
     */
    private $urlMock;

    /**
     * @var \Magento\UrlRewrite\Model\Message\UrlRewriteExceptionMessageFactory
     */
    private $urlRewriteExceptionMessageFactory;

    protected function setUp(): void
    {
        $this->urlMock = $this->createMock(\Magento\Framework\UrlInterface::class);

        $this->messageFactoryMock = $this->createPartialMock(
            \Magento\Framework\Message\Factory::class,
            ['create']
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

        $message = $this->getMockForAbstractClass(MessageInterface::class);

        $message->expects($this->once())
            ->method('setText')
            ->with($urlAlreadyExistsException->getMessage())
            ->willReturn($message);

        $message->expects($this->once())
            ->method('setIdentifier')
            ->with(UrlRewriteExceptionMessageFactory::URL_DUPLICATE_MESSAGE_MAP_ID)
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
     */
    public function testCreateMessageNotFound()
    {
        $this->expectException(\Magento\Framework\Exception\RuntimeException::class);

        $exception = new \Exception('message');
        $this->urlRewriteExceptionMessageFactory->createMessage($exception);
    }
}
