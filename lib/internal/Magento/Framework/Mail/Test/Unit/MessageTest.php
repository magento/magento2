<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Test\Unit;

class MessageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Mail\Message
     */
    protected $_messageMock;

    protected function setUp()
    {
        $this->_messageMock = $this->createPartialMock(
            \Magento\Framework\Mail\Message::class,
            ['setBody', 'setMessageType']
        );
    }

    public function testSetBodyHtml()
    {
        $this->_messageMock->expects($this->once())
            ->method('setMessageType')
            ->with('text/html');

        $this->_messageMock->expects($this->once())
            ->method('setBody')
            ->with('body');

        $this->_messageMock->setBodyHtml('body');
    }

    public function testSetBodyText()
    {
        $this->_messageMock->expects($this->once())
            ->method('setMessageType')
            ->with('text/plain');

        $this->_messageMock->expects($this->once())
            ->method('setBody')
            ->with('body');

        $this->_messageMock->setBodyText('body');
    }
}
