<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

/**
 * Unit test for MessageController class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessageControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\LockInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $lockFactory;

    /**
     * @var \Magento\Framework\MessageQueue\MessageController
     */
    private $messageController;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->lockFactory = $this->getMockBuilder(\Magento\Framework\MessageQueue\LockInterfaceFactory::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->messageController = $objectManager->getObject(
            \Magento\Framework\MessageQueue\MessageController::class,
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
        $this->setExpectedException(
            \Magento\Framework\Exception\NotFoundException::class,
            "Property 'message_id' not found in properties."
        );
        $this->lockFactory->expects($this->once())->method('create');
        $envelope = $this->getMockBuilder(\Magento\Framework\MessageQueue\EnvelopeInterface::class)
            ->disableArgumentCloning()->getMock();
        $envelope->expects($this->once())->method('getProperties')->willReturn($properties);

        $this->messageController->lock($envelope, $consumerName);
    }
}
