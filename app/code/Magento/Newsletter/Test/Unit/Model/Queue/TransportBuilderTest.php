<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Model\Queue;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Mail\MessageInterface;

/**
 * Tests \Magento\Newsletter\Model\Queue\TransportBuilder.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransportBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $builderClassName = \Magento\Newsletter\Model\Queue\TransportBuilder::class;

    /**
     * @var \Magento\Newsletter\Model\Queue\TransportBuilder
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
     * @var \Magento\Framework\Mail\MessageInterfaceFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $messageFactoryMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->templateFactoryMock = $this->createMock(\Magento\Framework\Mail\Template\FactoryInterface::class);
        $this->messageMock = $this->getMockBuilder(\Magento\Framework\Mail\MessageInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setMessageType', 'setSubject', 'setBody'])
            ->getMockForAbstractClass();
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->senderResolverMock = $this->createMock(\Magento\Framework\Mail\Template\SenderResolverInterface::class);
        $this->mailTransportFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Mail\TransportInterfaceFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
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
     * @param int $templateType
     * @param string $messageType
     * @param string $bodyText
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetTransport(
        $templateType = TemplateTypesInterface::TYPE_HTML,
        $messageType = MessageInterface::TYPE_HTML,
        $bodyText = '<h1>Html message</h1>'
    ) {
        $filter = $this->createMock(\Magento\Email\Model\Template\Filter::class);
        $data = [
            'template_subject' => 'Email Subject',
            'template_text' => $bodyText,
            'template_styles' => 'Styles',
            'template_type' => $templateType,
            'template_filter' => $filter,
        ];
        $vars = ['reason' => 'Reason', 'customer' => 'Customer'];
        $options = ['area' => 'frontend', 'store' => 1];
        $template = $this->createMock(\Magento\Email\Model\Template::class);
        $template->expects($this->once())->method('setVars')->with($vars)->willReturnSelf();
        $template->expects($this->once())->method('setOptions')->with($options)->willReturnSelf();
        $template->expects($this->once())->method('getSubject')->willReturn('Email Subject');
        $template->expects($this->once())->method('setData')->with($data)->willReturnSelf();
        $template->expects($this->once())
            ->method('getProcessedTemplate')
            ->with($vars)
            ->willReturn($bodyText);
        $template->expects($this->once())
            ->method('setTemplateFilter')
            ->with($filter);

        $this->templateFactoryMock->expects($this->once())->method('get')->with('identifier')->willReturn($template);

        $this->messageMock->expects($this->once())->method('setSubject')->with('Email Subject')->willReturnSelf();
        $this->messageMock->expects($this->once())->method('setMessageType')->with($messageType)->willReturnSelf();
        $this->messageMock->expects($this->once())->method('setBody')->with($bodyText)->willReturnSelf();

        $transport = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);

        $this->mailTransportFactoryMock->expects($this->at(0))->method('create')
            ->with(['message' => $this->messageMock])->willReturn($transport);

        $this->builder->setTemplateIdentifier('identifier')
            ->setTemplateVars($vars)
            ->setTemplateOptions($options)
            ->setTemplateData($data);

        $result = $this->builder->getTransport();

        $this->assertInstanceOf(\Magento\Framework\Mail\TransportInterface::class, $result);
    }
}
