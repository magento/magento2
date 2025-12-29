<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Test\Unit\Block;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\SendFriend\Block\Send;
use Magento\SendFriend\Model\SendFriend;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SendTest extends TestCase
{
    /**
     * @var Send
     */
    protected $model;

    /**
     * @var MockObject|SendFriend
     */
    protected $sendfriendMock;

    /**
     * @var MockObject|UrlInterface
     */
    protected $urlBuilderMock;

    /**
     * @var MockObject|RequestInterface
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->sendfriendMock = $this->createMock(SendFriend::class);
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);

        $this->model = $objectManager->getObject(
            Send::class,
            [
                'sendfriend' => $this->sendfriendMock,
                'urlBuilder' => $this->urlBuilderMock,
                'request' => $this->requestMock,
            ]
        );
    }

    public function testGetSendUrl()
    {
        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['id', null, '1'],
                    ['cat_id', null, '2'],
                ]
            );

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('sendfriend/product/sendmail', ['id' => 1, 'cat_id' => 2])
            ->willReturn('url');

        $this->assertEquals('url', $this->model->getSendUrl());
    }

    /**
     * @param bool $isExceedLimit
     * @param bool $result
     */
    #[DataProvider('dataProviderCanSend')]
    public function testCanSend($isExceedLimit, $result)
    {
        $this->sendfriendMock->expects($this->once())
            ->method('isExceedLimit')
            ->willReturn($isExceedLimit);

        $this->assertEquals($result, $this->model->canSend());
    }

    /**
     * @return array
     */
    public static function dataProviderCanSend()
    {
        return [
            [true, false],
            [false, true],
        ];
    }
}
