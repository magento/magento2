<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Test\Unit\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Mail\MessageInterface;

class TransportBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $builderClassName = '\Magento\Framework\Mail\Template\TransportBuilder';

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $builder;

    /**
     * @var \Magento\Framework\Mail\Template\FactoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateFactoryMock;

    /**
     * @var \Magento\Framework\Mail\Message | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Mail\Template\SenderResolverInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $senderResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mailTransportFactoryMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->templateFactoryMock = $this->getMock('Magento\Framework\Mail\Template\FactoryInterface');
        $this->messageMock = $this->getMock('Magento\Framework\Mail\Message');
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->senderResolverMock = $this->getMock('Magento\Framework\Mail\Template\SenderResolverInterface');
        $this->mailTransportFactoryMock = $this->getMockBuilder('Magento\Framework\Mail\TransportInterfaceFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->builder = $objectManagerHelper->getObject(
            $this->builderClassName,
            [
                'templateFactory' => $this->templateFactoryMock,
                'message' => $this->messageMock,
                'objectManager' => $this->objectManagerMock,
                'senderResolver' => $this->senderResolverMock,
                'mailTransportFactory' => $this->mailTransportFactoryMock
            ]
        );
    }

    /**
     * @dataProvider getTransportDataProvider
     * @param int $templateType
     * @param string $messageType
     * @param string $bodyText
     * @param string $templateNamespace
     * @return void
     */
    public function testGetTransport($templateType, $messageType, $bodyText, $templateNamespace)
    {
        $this->builder->setTemplateModel($templateNamespace);

        $vars = ['reason' => 'Reason', 'customer' => 'Customer'];
        $options = ['area' => 'frontend', 'store' => 1];

        $template = $this->getMock('\Magento\Framework\Mail\TemplateInterface');
        $template->expects($this->once())->method('setVars')->with($this->equalTo($vars))->willReturnSelf();
        $template->expects($this->once())->method('setOptions')->with($this->equalTo($options))->willReturnSelf();
        $template->expects($this->once())->method('getSubject')->willReturn('Email Subject');
        $template->expects($this->once())->method('getType')->willReturn($templateType);
        $template->expects($this->once())->method('processTemplate')->willReturn($bodyText);

        $this->templateFactoryMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('identifier'), $this->equalTo($templateNamespace))
            ->willReturn($template);

        $this->messageMock->expects($this->once())
            ->method('setSubject')
            ->with($this->equalTo('Email Subject'))
            ->willReturnSelf();
        $this->messageMock->expects($this->once())
            ->method('setMessageType')
            ->with($this->equalTo($messageType))
            ->willReturnSelf();
        $this->messageMock->expects($this->once())
            ->method('setBody')
            ->with($this->equalTo($bodyText))
            ->willReturnSelf();

        $transport = $this->getMock('\Magento\Framework\Mail\TransportInterface');

        $this->mailTransportFactoryMock->expects($this->at(0))
            ->method('create')
            ->with($this->equalTo(['message' => $this->messageMock]))
            ->willReturn($transport);

        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with($this->equalTo('Magento\Framework\Mail\Message'))
            ->willReturn($transport);

        $this->builder->setTemplateIdentifier('identifier')->setTemplateVars($vars)->setTemplateOptions($options);
        $this->assertInstanceOf('Magento\Framework\Mail\TransportInterface', $this->builder->getTransport());
    }

    /**
     * @return array
     */
    public function getTransportDataProvider()
    {
        return [
            [
                TemplateTypesInterface::TYPE_TEXT,
                MessageInterface::TYPE_TEXT,
                'Plain text',
                null
            ],
            [
                TemplateTypesInterface::TYPE_HTML,
                MessageInterface::TYPE_HTML,
                '<h1>Html message</h1>',
                'Test\Namespace\Template'
            ]
        ];
    }

    /**
     * @return void
     */
    public function testSetFrom()
    {
        $sender = ['email' => 'from@example.com', 'name' => 'name'];
        $this->senderResolverMock->expects($this->once())
            ->method('resolve')
            ->with($sender)
            ->willReturn($sender);
        $this->messageMock->expects($this->once())
            ->method('setFrom')
            ->with('from@example.com', 'name')
            ->willReturnSelf();

        $this->builder->setFrom($sender);
    }

    /**
     * @return void
     */
    public function testSetCc()
    {
        $this->messageMock->expects($this->once())->method('addCc')->with('cc@example.com')->willReturnSelf();

        $this->builder->addCc('cc@example.com');
    }

    /**
     * @return void
     */
    public function testAddTo()
    {
        $this->messageMock->expects($this->once())
            ->method('addTo')
            ->with('to@example.com', 'recipient')
            ->willReturnSelf();

        $this->builder->addTo('to@example.com', 'recipient');
    }

    /**
     * @return void
     */
    public function testAddBcc()
    {
        $this->messageMock->expects($this->once())
            ->method('addBcc')
            ->with('bcc@example.com')
            ->willReturnSelf();

        $this->builder->addBcc('bcc@example.com');
    }

    /**
     * @return void
     */
    public function testSetReplyTo()
    {
        $this->messageMock->expects($this->once())
            ->method('setReplyTo')
            ->with('replyTo@example.com', 'replyName')
            ->willReturnSelf();

        $this->builder->setReplyTo('replyTo@example.com', 'replyName');
    }
}
