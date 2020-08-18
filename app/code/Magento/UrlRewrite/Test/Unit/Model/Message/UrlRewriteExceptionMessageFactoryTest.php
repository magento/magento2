<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Model\Message;

use Magento\Framework\Message\Factory;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\UrlInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\Message\UrlRewriteExceptionMessageFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlRewriteExceptionMessageFactoryTest extends TestCase
{
    /**
     * @var Factory|MockObject
     */
    private $messageFactoryMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

    /**
     * @var UrlRewriteExceptionMessageFactory
     */
    private $urlRewriteExceptionMessageFactory;

    protected function setUp(): void
    {
        $this->urlMock = $this->getMockForAbstractClass(UrlInterface::class);

        $this->messageFactoryMock = $this->createPartialMock(
            Factory::class,
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

    public function testCreateMessageNotFound()
    {
        $this->expectException('Magento\Framework\Exception\RuntimeException');
        $exception = new \Exception('message');
        $this->urlRewriteExceptionMessageFactory->createMessage($exception);
    }
}
