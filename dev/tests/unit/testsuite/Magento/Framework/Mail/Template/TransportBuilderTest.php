<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Template;

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
    protected $_mailTransportFactoryMock;

    public function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->templateFactoryMock = $this->getMock('Magento\Framework\Mail\Template\FactoryInterface');
        $this->messageMock = $this->getMock('Magento\Framework\Mail\Message');
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->senderResolverMock = $this->getMock('Magento\Framework\Mail\Template\SenderResolverInterface');
        $this->_mailTransportFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Mail\TransportInterfaceFactory'
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
        $this->builder = $helper->getObject(
            $this->builderClassName,
            [
                'templateFactory' => $this->templateFactoryMock,
                'message' => $this->messageMock,
                'objectManager' => $this->objectManagerMock,
                'senderResolver' => $this->senderResolverMock,
                'mailTransportFactory' => $this->_mailTransportFactoryMock
            ]
        );
    }

    /**
     * @dataProvider getTransportDataProvider
     * @param int $templateType
     * @param string $messageType
     * @param string $bodyText
     */
    public function testGetTransport($templateType, $messageType, $bodyText)
    {
        $vars = ['reason' => 'Reason', 'customer' => 'Customer'];
        $options = ['area' => 'frontend', 'store' => 1];
        $template = $this->getMock('\Magento\Framework\Mail\TemplateInterface');
        $template->expects($this->once())->method('setVars')->with($this->equalTo($vars))->will($this->returnSelf());
        $template->expects(
            $this->once()
        )->method(
            'setOptions'
        )->with(
            $this->equalTo($options)
        )->will(
            $this->returnSelf()
        );
        $template->expects($this->once())->method('getSubject')->will($this->returnValue('Email Subject'));
        $template->expects($this->once())->method('getType')->will($this->returnValue($templateType));
        $template->expects($this->once())->method('processTemplate')->will($this->returnValue($bodyText));

        $this->templateFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $this->equalTo('identifier')
        )->will(
            $this->returnValue($template)
        );

        $this->messageMock->expects(
            $this->once()
        )->method(
            'setSubject'
        )->with(
            $this->equalTo('Email Subject')
        )->will(
            $this->returnSelf()
        );
        $this->messageMock->expects(
            $this->once()
        )->method(
            'setMessageType'
        )->with(
            $this->equalTo($messageType)
        )->will(
            $this->returnSelf()
        );
        $this->messageMock->expects(
            $this->once()
        )->method(
            'setBody'
        )->with(
            $this->equalTo($bodyText)
        )->will(
            $this->returnSelf()
        );

        $transport = $this->getMock('\Magento\Framework\Mail\TransportInterface');

        $this->_mailTransportFactoryMock->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            $this->equalTo(['message' => $this->messageMock])
        )->will(
            $this->returnValue($transport)
        );

        $this->objectManagerMock->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\Framework\Mail\Message')
        )->will(
            $this->returnValue($transport)
        );

        $this->builder->setTemplateIdentifier('identifier')->setTemplateVars($vars)->setTemplateOptions($options);

        $result = $this->builder->getTransport();

        $this->assertInstanceOf('Magento\Framework\Mail\TransportInterface', $result);
    }

    public function getTransportDataProvider()
    {
        return [
            [
                \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT,
                \Magento\Framework\Mail\Message::TYPE_TEXT,
                'Plain text',
            ],
            [
                \Magento\Framework\App\TemplateTypesInterface::TYPE_HTML,
                \Magento\Framework\Mail\Message::TYPE_HTML,
                '<h1>Html message</h1>'
            ]
        ];
    }

    public function testSetFrom()
    {
        $sender = ['email' => 'from@example.com', 'name' => 'name'];
        $this->senderResolverMock->expects(
            $this->once()
        )->method(
            'resolve'
        )->with(
            $sender
        )->will(
            $this->returnValue($sender)
        );
        $this->messageMock->expects(
            $this->once()
        )->method(
            'setFrom'
        )->with(
            'from@example.com',
            'name'
        )->will(
            $this->returnSelf()
        );

        $this->builder->setFrom($sender);
    }

    public function testSetCc()
    {
        $this->messageMock->expects($this->once())->method('addCc')->with('cc@example.com')->will($this->returnSelf());

        $this->builder->addCc('cc@example.com');
    }

    /**
     * @covers \Magento\Framework\Mail\Template\TransportBuilder::addTo
     */
    public function testAddTo()
    {
        $this->messageMock->expects($this->once())
            ->method('addTo')
            ->with('to@example.com', 'recipient')
            ->will($this->returnSelf());

        $this->builder->addTo('to@example.com', 'recipient');
    }

    /**
     * @covers \Magento\Framework\Mail\Template\TransportBuilder::addBcc
     */
    public function testAddBcc()
    {
        $this->messageMock->expects($this->once())
            ->method('addBcc')
            ->with('bcc@example.com')
            ->will($this->returnSelf());

        $this->builder->addBcc('bcc@example.com');
    }

    /**
     * @covers \Magento\Framework\Mail\Template\TransportBuilder::setReplyTo
     */
    public function testSetReplyTo()
    {
        $this->messageMock->expects($this->once())
            ->method('setReplyTo')
            ->with('replyTo@example.com', 'replyName')
            ->will($this->returnSelf());

        $this->builder->setReplyTo('replyTo@example.com', 'replyName');
    }
}
