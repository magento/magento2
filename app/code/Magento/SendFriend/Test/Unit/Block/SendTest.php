<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SendFriend\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SendFriend\Block\Send
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\SendFriend\Model\SendFriend
     */
    protected $sendfriendMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\UrlInterface
     */
    protected $urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $requestMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->sendfriendMock = $this->getMockBuilder(\Magento\SendFriend\Model\SendFriend::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->model = $objectManager->getObject(
            \Magento\SendFriend\Block\Send::class,
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
