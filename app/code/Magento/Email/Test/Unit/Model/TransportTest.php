<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model;

use Magento\Email\Model\Transport;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Message;
use Magento\Store\Model\ScopeInterface;

/**
 * Covers \Magento\Email\Model\Transport
 */
class TransportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Zend_Mail_Transport_Sendmail|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transportMock;

    /**
     * @var Message|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Transport
     */
    private $model;

    protected function setUp()
    {
        $this->transportMock = $this->createMock(\Zend_Mail_Transport_Sendmail::class);

        $this->messageMock = $this->createMock(Message::class);

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->model = new Transport($this->transportMock, $this->messageMock, $this->scopeConfigMock);
    }

    /**
     * Tests that if any exception was caught, \Magento\Framework\Exception\MailException will thrown
     *
     * @expectedException \Magento\Framework\Exception\MailException
     */
    public function testSendMessageException()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->willThrowException(new \Exception('some exception'));
        $this->model->sendMessage();
    }

    /**
     * Tests that if sending emails is disabled in System Configuration, send nothing
     */
    public function testSendMessageSmtpDisabled()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Transport::XML_PATH_SYSTEM_SMTP_DISABLE, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->transportMock->expects($this->never())
            ->method('send');
        $this->model->sendMessage();
    }

    /**
     * Tests that if sending Return-Path was disabled or email was not provided, - this header won't be set
     *
     * @param string|int|null $returnPathSet
     * @param string|null $returnPathEmail
     *
     * @dataProvider sendMessageWithoutReturnPathDataProvider
     */
    public function testSendMessageWithoutReturnPath($returnPathSet, $returnPathEmail = null)
    {
        $this->prepareSendingMessage($returnPathSet, $returnPathEmail);

        $this->messageMock->expects($this->never())
            ->method('setReturnPath');
        $this->transportMock->expects($this->once())
            ->method('send');
        $this->model->sendMessage();
    }

    /**
     * Tests that if sending Return-Path was disabled, this header won't be set
     *
     * @param string|int|null $returnPathSet
     * @param string|null $emailFrom
     *
     * @dataProvider sendMessageWithDefaultReturnPathDataProvider
     */
    public function testSendMessageWithDefaultReturnPath($returnPathSet, $emailFrom)
    {
        $this->prepareSendingMessage($returnPathSet, null);

        $this->messageMock->expects($this->once())
            ->method('setReturnPath')
            ->with($emailFrom);
        $this->messageMock->expects($this->once())
            ->method('getFrom')
            ->willReturn($emailFrom);
        $this->transportMock->expects($this->once())
            ->method('send');
        $this->model->sendMessage();
    }

    /**
     * Tests that if sending Return-Path was disabled, this header won't be set
     *
     * @param string|int|null $returnPathSet
     * @param string|null $emailFrom
     *
     * @dataProvider sendMessageWithCustomReturnPathDataProvider
     */
    public function testSendMessageWithCustomReturnPath($returnPathSet, $emailFrom)
    {
        $this->prepareSendingMessage($returnPathSet, $emailFrom);

        $this->messageMock->expects($this->once())
            ->method('setReturnPath')
            ->with($emailFrom);
        $this->messageMock->expects($this->never())
            ->method('getFrom')
            ->willReturn($emailFrom);
        $this->transportMock->expects($this->once())
            ->method('send');
        $this->model->sendMessage();
    }

    /**
     * Tests retrieving message object
     */
    public function testGetMessage()
    {
        $this->assertEquals($this->messageMock, $this->model->getMessage());
    }

    /**
     * Executes all main sets for sending message
     *
     * @param string|int|null $returnPathSet
     * @param string|null $returnPathEmail
     */
    private function prepareSendingMessage($returnPathSet, $returnPathEmail)
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(Transport::XML_PATH_SYSTEM_SMTP_DISABLE, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $map = [
            [Transport::XML_PATH_SENDING_SET_RETURN_PATH, ScopeInterface::SCOPE_STORE, null, $returnPathSet],
            [Transport::XML_PATH_SENDING_RETURN_PATH_EMAIL, ScopeInterface::SCOPE_STORE, null, $returnPathEmail]
        ];
        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap($map);
    }

    /**
     * Data provider for testSendMessageWithoutReturnPath
     * @return array
     */
    public function sendMessageWithoutReturnPathDataProvider()
    {
        return [
            [0],
            ['0'],
            [3],
            ['2', null],
            [2, null],
        ];
    }

    /**
     * Data provider for testSendMessageWithDefaultReturnPath
     * @return array
     */
    public function sendMessageWithDefaultReturnPathDataProvider()
    {
        return [
            [1, 'test@exemple.com'],
            ['1', 'test@exemple.com'],
            ['1', '']
        ];
    }

    /**
     * Data provider for testSendMessageWithCustomReturnPath
     * @return array
     */
    public function sendMessageWithCustomReturnPathDataProvider()
    {
        return [
            [2, 'test@exemple.com'],
            ['2', 'test@exemple.com'],
            ['2', '']
        ];
    }
}
