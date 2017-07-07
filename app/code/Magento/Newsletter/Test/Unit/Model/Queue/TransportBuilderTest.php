<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Test\Unit\Model\Queue;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Mail\MessageInterface;

class TransportBuilderTest extends \PHPUnit_Framework_TestCase
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
     * @return void
     */
    public function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->templateFactoryMock = $this->getMock(\Magento\Framework\Mail\Template\FactoryInterface::class);
        $this->messageMock = $this->getMock(\Magento\Framework\Mail\Message::class);
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->senderResolverMock = $this->getMock(\Magento\Framework\Mail\Template\SenderResolverInterface::class);
        $this->mailTransportFactoryMock = $this->getMockBuilder(
            \Magento\Framework\Mail\TransportInterfaceFactory::class
        )->disableOriginalConstructor()
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
        $filter = $this->getMock(\Magento\Email\Model\Template\Filter::class, [], [], '', false);
        $data = [
            'template_subject' => 'Email Subject',
            'template_text' => $bodyText,
            'template_styles' => 'Styles',
            'template_type' => $templateType,
            'template_filter' => $filter,
        ];
        $vars = ['reason' => 'Reason', 'customer' => 'Customer'];
        $options = ['area' => 'frontend', 'store' => 1];
        $template = $this->getMock(\Magento\Email\Model\Template::class, [], [], '', false);
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
        $template->expects($this->once())->method('setData')->with($this->equalTo($data))->will($this->returnSelf());
        $template->expects($this->once())
            ->method('getProcessedTemplate')
            ->with($vars)
            ->will($this->returnValue($bodyText));
        $template->expects($this->once())
            ->method('setTemplateFilter')
            ->with($filter);

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
            'setBodyHtml'
        )->with(
            $this->equalTo($bodyText)
        )->will(
            $this->returnSelf()
        );

        $transport = $this->getMock(\Magento\Framework\Mail\TransportInterface::class);

        $this->mailTransportFactoryMock->expects(
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
            $this->equalTo(\Magento\Framework\Mail\Message::class)
        )->will(
            $this->returnValue($transport)
        );

        $this->builder->setTemplateIdentifier(
            'identifier'
        )->setTemplateVars(
            $vars
        )->setTemplateOptions(
            $options
        )->setTemplateData(
            $data
        );

        $result = $this->builder->getTransport();

        $this->assertInstanceOf(\Magento\Framework\Mail\TransportInterface::class, $result);
    }
}
