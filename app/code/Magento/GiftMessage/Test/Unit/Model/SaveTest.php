<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model;

use Magento\Backend\Model\Session\Quote;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\GiftMessage\Helper\Message;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\GiftMessage\Model\Save;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $messageFactoryMock;

    /**
     * @var Save
     */
    protected $model;

    protected function setUp(): void
    {
        $productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->messageFactoryMock = $this->getMockBuilder(MessageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $sessionMock = $this->createMock(Quote::class);
        $giftMessageHelperMock = $this->createMock(Message::class);
        $this->model = new Save(
            $productRepositoryMock,
            $this->messageFactoryMock,
            $sessionMock,
            $giftMessageHelperMock
        );
    }

    public function testSaveAllInOrder()
    {
        $message = [1 => [
            'from' => 'John Doe',
            'to' => 'Jane Doe',
            'message' => 'I love Magento',
            'type' => 'order'
        ]
        ];
        $this->model->setGiftmessages($message);

        $messageMock = $this->createMock(\Magento\GiftMessage\Model\Message::class);
        $entityModelMock = $this->createMock(Order::class);

        $this->messageFactoryMock->expects($this->once())->method('create')->willReturn($messageMock);
        $messageMock->expects($this->once())->method('getEntityModelByType')->with('order')->willReturnSelf();
        $messageMock->expects($this->once())->method('load')->with(1)->willReturn($entityModelMock);
        $messageMock->expects($this->atLeastOnce())->method('isMessageEmpty')->willReturn(false);
        $messageMock->expects($this->once())->method('save');
        $entityModelMock->expects($this->once())->method('save');
        $this->assertEquals($this->model, $this->model->saveAllInOrder());
    }
}
