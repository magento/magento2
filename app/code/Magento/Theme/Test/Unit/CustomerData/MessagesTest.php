<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\CustomerData;

use Magento\Theme\CustomerData\Messages;

class MessagesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var Messages
     */
    protected $object;

    public function setUp()
    {
        $this->messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')->getMock();
        $this->object = new Messages($this->messageManager);
    }

    public function testGetSectionData()
    {
        $msgType = 'error';
        $msgText = 'All is lost';
        $msg = $this->getMockBuilder('Magento\Framework\Message\MessageInterface')->getMock();
        $messages = [$msg];
        $msg->expects($this->once())
            ->method('getType')
            ->willReturn($msgType);
        $msg->expects($this->once())
            ->method('getText')
            ->willReturn($msgText);
        $msgCollection = $this->getMockBuilder('Magento\Framework\Message\Collection')
            ->getMock();
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
