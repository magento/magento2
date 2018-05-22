<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mail\Test\Unit\Template;

use Magento\Framework\Mail\Template\TransportBuilderByStore;

class TransportBuilderByStoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilderByStore
     */
    protected $model;

    /**
     * @var \Magento\Framework\Mail\Message | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageMock;

    /**
     * @var \Magento\Framework\Mail\Template\SenderResolverInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $senderResolverMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->messageMock = $this->createMock(\Magento\Framework\Mail\Message::class);
        $this->senderResolverMock = $this->createMock(\Magento\Framework\Mail\Template\SenderResolverInterface::class);

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
            ->method('setFrom')
            ->with('from@example.com', 'name')
            ->willReturnSelf();
        $this->messageMock->expects($this->once())
            ->method('clearFrom')
            ->willReturnSelf();

        $this->model->setFromByStore($sender, $store);
    }
}
