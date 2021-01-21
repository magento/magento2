<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\CustomerData;

use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Theme\CustomerData\Messages;

class MessagesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManager;

    /**
     * @var InterpretationStrategyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $messageInterpretationStrategy;

    /**
     * @var Messages
     */
    protected $object;

    protected function setUp(): void
    {
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->getMock();
        $this->messageInterpretationStrategy = $this->createMock(
            \Magento\Framework\View\Element\Message\InterpretationStrategyInterface::class
        );
        $this->object = new Messages($this->messageManager, $this->messageInterpretationStrategy);
    }

    public function testGetSectionData()
    {
        $msgType = 'error';
        $msgText = 'All is lost';
        $msg = $this->getMockBuilder(\Magento\Framework\Message\MessageInterface::class)->getMock();
        $messages = [$msg];
        $msgCollection = $this->getMockBuilder(\Magento\Framework\Message\Collection::class)
            ->getMock();

        $msg->expects($this->once())
            ->method('getType')
            ->willReturn($msgType);
        $this->messageInterpretationStrategy->expects(static::once())
            ->method('interpret')
            ->with($msg)
            ->willReturn($msgText);
        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true, null)
            ->willReturn($msgCollection);
        $msgCollection->expects($this->once())
            ->method('getItems')
            ->willReturn($messages);

        $this->assertEquals(
            ['messages' => [['type' => $msgType, 'text' => $msgText]]],
            $this->object->getSectionData()
        );
    }
}
