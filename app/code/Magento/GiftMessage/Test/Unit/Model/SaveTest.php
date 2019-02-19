<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Model;

class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageFactoryMock;

    /**
     * @var \Magento\GiftMessage\Model\Save
     */
    protected $model;

    protected function setUp()
    {
        $productRepositoryMock = $this->createMock(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->messageFactoryMock = $this->getMockBuilder(\Magento\GiftMessage\Model\MessageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $sessionMock = $this->createMock(\Magento\Backend\Model\Session\Quote::class);
        $giftMessageHelperMock = $this->createMock(\Magento\GiftMessage\Helper\Message::class);
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

        $messageMock = $this->createMock(\Magento\GiftMessage\Model\Message::class);
        $entityModelMock = $this->createMock(\Magento\Sales\Model\Order::class);

        $this->messageFactoryMock->expects($this->once())->method('create')->willReturn($messageMock);
        $messageMock->expects($this->once())->method('getEntityModelByType')->with('order')->willReturnSelf();
        $messageMock->expects($this->once())->method('load')->with(1)->willReturn($entityModelMock);
        $messageMock->expects($this->atLeastOnce())->method('isMessageEmpty')->willReturn(false);
        $messageMock->expects($this->once())->method('save');
        $entityModelMock->expects($this->once())->method('save');
        $this->assertEquals($this->model, $this->model->saveAllInOrder());
    }
}
