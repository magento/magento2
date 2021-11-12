<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\CustomerData;

use Magento\Framework\Message\Collection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Theme\CustomerData\Messages;
use Magento\Theme\CustomerData\MessageServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessagesTest extends TestCase
{
    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManager;

    /**
     * @var MessageServiceInterface|MockObject
     */
    private $messageService;

    /**
     * @var InterpretationStrategyInterface|MockObject
     */
    private $messageInterpretationStrategy;

    /**
     * @var Messages
     */
    protected $object;

    protected function setUp(): void
    {
        $this->messageManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        $this->messageService = $this->getMockBuilder(MessageServiceInterface::class)
            ->getMock();
        $this->messageInterpretationStrategy = $this->createMock(
            InterpretationStrategyInterface::class
        );
        $this->object = new Messages($this->messageManager, $this->messageInterpretationStrategy, $this->messageService);
    }

    public function testGetSectionData()
    {
        $msgType = 'error';
        $msgText = 'All is lost';
        $msg = $this->getMockBuilder(MessageInterface::class)
            ->getMock();
        $messages = [$msg];
        $msgCollection = $this->getMockBuilder(Collection::class)
            ->getMock();

        $msg->expects($this->once())
            ->method('getType')
            ->willReturn($msgType);
        $this->messageInterpretationStrategy->expects(static::once())
            ->method('interpret')
            ->with($msg)
            ->willReturn($msgText);
        $this->messageService->expects($this->once())
            ->method('getMessages')
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
