<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Test\Unit;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject
     */
    protected $_messageMock;

    public function setUp()
    {
        $this->_messageMock = $this->getMock(
            '\Magento\Framework\Mail\Message',
            ['getBodyText', 'getBodyHtml', 'setBodyText', 'setBodyHtml']
        );
    }

    /**
     * @param string $messageType
     * @param string $method
     *
     * @covers \Magento\Framework\Mail\Message::setBody
     * @covers \Magento\Framework\Mail\Message::setMessageType
     * @dataProvider setBodyDataProvider
     */
    public function testSetBody($messageType, $method)
    {
        $this->_messageMock->setMessageType($messageType);

        $this->_messageMock->expects($this->once())
            ->method($method)
            ->with('body');

        $this->_messageMock->setBody('body');
    }

    /**
     * @return array
     */
    public function setBodyDataProvider()
    {
        return [
            [
                'messageType' => 'text/plain',
                'method' => 'setBodyText',
            ],
            [
                'messageType' => 'text/html',
                'method' => 'setBodyHtml'
            ]
        ];
    }

    /**
     * @param string $messageType
     * @param string $method
     *
     * @covers \Magento\Framework\Mail\Message::getBody
     * @covers \Magento\Framework\Mail\Message::setMessageType
     * @dataProvider getBodyDataProvider
     */
    public function testGetBody($messageType, $method)
    {
        $this->_messageMock->setMessageType($messageType);

        $this->_messageMock->expects($this->once())
            ->method($method);

        $this->_messageMock->getBody('body');
    }

    /**
     * @return array
     */
    public function getBodyDataProvider()
    {
        return [
            [
                'messageType' => 'text/plain',
                'method' => 'getBodyText',
            ],
            [
                'messageType' => 'text/html',
                'method' => 'getBodyHtml'
            ]
        ];
    }
}
