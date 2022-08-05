<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail\Test\Unit\Template;

use Magento\Framework\Mail\Message;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransportBuilderByStoreTest extends TestCase
{
    /**
     * @var TransportBuilderByStore
     */
    protected $model;

    /**
     * @var Message|MockObject
     */
    protected $messageMock;

    /**
     * @var SenderResolverInterface|MockObject
     */
    protected $senderResolverMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->messageMock = $this->createMock(Message::class);
        $this->senderResolverMock = $this->getMockForAbstractClass(SenderResolverInterface::class);

        $this->model = $objectManagerHelper->getObject(
            TransportBuilderByStore::class,
            [
                'message' => $this->messageMock,
                'senderResolver' => $this->senderResolverMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testSetFromByStore()
    {
        $sender = ['email' => 'from@example.com', 'name' => 'name'];
        $store = 1;
        $this->senderResolverMock->expects($this->once())
            ->method('resolve')
            ->with($sender, $store)
            ->willReturn($sender);
        $this->messageMock->expects($this->once())
            ->method('setFromAddress')
            ->with($sender['email'], $sender['name'])
            ->willReturnSelf();

        $this->model->setFromByStore($sender, $store);
    }
}
