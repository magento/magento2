<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\LockInterfaceFactory;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for MessageController class.
 *
 */
class MessageControllerTest extends TestCase
{
    /**
     * @var LockInterfaceFactory|MockObject
     */
    private $lockFactory;

    /**
     * @var MessageController
     */
    private $messageController;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->lockFactory = $this->getMockBuilder(LockInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();

        $objectManager = new ObjectManager($this);
        $this->messageController = $objectManager->getObject(
            MessageController::class,
            [
                'lockFactory' => $this->lockFactory
            ]
        );
    }

    /**
     * Test for lock method with NotFoundException.
     *
     * @return void
     */
    public function testLockWithNotFoundException()
    {
        $properties = [];
        $consumerName = '';
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Property 'message_id' not found in properties.");
        $this->lockFactory->expects($this->once())->method('create');
        $envelope = $this->getMockBuilder(EnvelopeInterface::class)
            ->disableArgumentCloning()->getMockForAbstractClass();
        $envelope->expects($this->once())->method('getProperties')->willReturn($properties);

        $this->messageController->lock($envelope, $consumerName);
    }
}
