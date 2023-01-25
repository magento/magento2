<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Test\Unit\Block;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\SendFriend\Block\Send;
use Magento\SendFriend\Model\SendFriend;
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

        $this->sendfriendMock = $this->getMockBuilder(SendFriend::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
     *
     * @dataProvider dataProviderCanSend
     */
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
    public function dataProviderCanSend()
    {
        return [
            [true, false],
            [false, true],
        ];
    }
}
