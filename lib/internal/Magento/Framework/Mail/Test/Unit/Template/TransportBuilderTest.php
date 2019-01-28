<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mail\Test\Unit\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\MessageInterface;

/**
 * Tests \Magento\Framework\Mail\Template\TransportBuilder.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransportBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $builderClassName = \Magento\Framework\Mail\Template\TransportBuilder::class;

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
     * @var \Magento\Framework\Mail\MessageInterfaceFactory| \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageFactoryMock;

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
        $this->templateFactoryMock = $this->createMock(\Magento\Framework\Mail\Template\FactoryInterface::class);
        $this->messageMock = $this->createMock(\Magento\Framework\Mail\Message::class);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->senderResolverMock = $this->createMock(\Magento\Framework\Mail\Template\SenderResolverInterface::class);
        $this->mailTransportFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Mail\TransportInterfaceFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->messageFactoryMock = $this->getMockBuilder(\Magento\Framework\Mail\MessageInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->messageFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($this->messageMock);
        $this->builder = $objectManagerHelper->getObject(
            $this->builderClassName,
            [
                'templateFactory' => $this->templateFactoryMock,
                'message' => $this->messageMock,
                'objectManager' => $this->objectManagerMock,
                'senderResolver' => $this->senderResolverMock,
                'mailTransportFactory' => $this->mailTransportFactoryMock,
                'messageFactory' => $this->messageFactoryMock,
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
        $vars = ['reason' => 'Reason', 'customer' => 'Customer'];
        $options = ['area' => 'frontend', 'store' => 1];
        $from = 'email_from';
        $sender = ['email' => 'from@example.com', 'name' => 'name'];

        $this->builder->setTemplateModel($templateNamespace);
        $this->builder->setFrom($from);

        $template = $this->createPartialMock(
            \Magento\Framework\Mail\TemplateInterface::class,
            [
                'setVars',
                'isPlain',
                'setOptions',
                'getSubject',
                'getType',
                'processTemplate',
                'getDesignConfig',
            ]
        );
        $template->expects($this->once())->method('setVars')->with($this->equalTo($vars))->willReturnSelf();
        $template->expects($this->once())->method('setOptions')->with($this->equalTo($options))->willReturnSelf();
        $template->expects($this->once())->method('getSubject')->willReturn('Email Subject');
        $template->expects($this->once())->method('getType')->willReturn($templateType);
        $template->expects($this->once())->method('processTemplate')->willReturn($bodyText);
        $template->method('getDesignConfig')->willReturn(new DataObject($options));

        $this->senderResolverMock->expects($this->once())
            ->method('resolve')
            ->with($from, 1)
            ->willReturn($sender);

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
        $this->messageMock->method('setFrom')
            ->with($sender['email'], $sender['name'])
            ->willReturnSelf();

        $transport = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);

        $this->mailTransportFactoryMock->expects($this->at(0))
            ->method('create')
            ->with($this->equalTo(['message' => $this->messageMock]))
            ->willReturn($transport);

        $this->builder->setTemplateIdentifier('identifier')->setTemplateVars($vars)->setTemplateOptions($options);
        $this->assertInstanceOf(\Magento\Framework\Mail\TransportInterface::class, $this->builder->getTransport());
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
