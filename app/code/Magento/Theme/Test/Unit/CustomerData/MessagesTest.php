<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\CustomerData;

use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Theme\CustomerData\Messages;

class MessagesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var InterpretationStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageInterpretationStrategy;

    /**
     * @var Messages
     */
    protected $object;

    public function setUp()
    {
        $this->messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')->getMock();
        $this->messageInterpretationStrategy = $this->getMock(
            'Magento\Framework\View\Element\Message\InterpretationStrategyInterface'
        );
        $this->object = new Messages($this->messageManager, $this->messageInterpretationStrategy);
    }

    public function testGetSectionData()
    {
        $msgType = 'error';
        $msgText = 'All is lost';
        $msg = $this->getMockBuilder('Magento\Framework\Message\MessageInterface')->getMock();
        $messages = [$msg];
        $msgCollection = $this->getMockBuilder('Magento\Framework\Message\Collection')
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
