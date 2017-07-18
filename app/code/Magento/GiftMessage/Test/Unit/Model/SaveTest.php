<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Model;

class SaveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_FrameWork_MockObject_MockObject
     */
    protected $messageFactoryMock;

    /**
     * @var \Magento\GiftMessage\Model\Save
     */
    protected $model;

    protected function setUp()
    {
        $productRepositoryMock = $this->getMock(
            \Magento\Catalog\Api\ProductRepositoryInterface::class,
            [],
            [],
            '',
            false
        );
        $this->messageFactoryMock = $this->getMockBuilder(\Magento\GiftMessage\Model\MessageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $sessionMock = $this->getMock(\Magento\Backend\Model\Session\Quote::class, [], [], '', false);
        $giftMessageHelperMock = $this->getMock(\Magento\GiftMessage\Helper\Message::class, [], [], '', false);
        $this->model = new \Magento\GiftMessage\Model\Save(
            $productRepositoryMock,
            $this->messageFactoryMock,
            $sessionMock,
            $giftMessageHelperMock
        );
    }

    public function testSaveAllInOrder()
    {
        $message = [1 =>
            [
                'from' => 'John Doe',
                'to' => 'Jane Doe',
                'message' => 'I love Magento',
                'type' => 'order'
            ]
        ];
        $this->model->setGiftmessages($message);

        $messageMock = $this->getMock(\Magento\GiftMessage\Model\Message::class, [], [], '', false);
        $entityModelMock = $this->getMock(\Magento\Sales\Model\Order::class, [], [], '', false);

        $this->messageFactoryMock->expects($this->once())->method('create')->willReturn($messageMock);
        $messageMock->expects($this->once())->method('getEntityModelByType')->with('order')->willReturnSelf();
        $messageMock->expects($this->once())->method('load')->with(1)->willReturn($entityModelMock);
        $messageMock->expects($this->atLeastOnce())->method('isMessageEmpty')->willReturn(false);
        $messageMock->expects($this->once())->method('save');
        $entityModelMock->expects($this->once())->method('save');
        $this->assertEquals($this->model, $this->model->saveAllInOrder());
    }
}
