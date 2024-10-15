<?php /** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUndefinedClassInspection */
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Test\Unit\Model\Queue;

use Magento\Email\Model\Template;
use Magento\Email\Model\Template\Filter;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\Message;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterface;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Newsletter\Model\Queue\TransportBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Newsletter\Model\Queue\TransportBuilder
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransportBuilderTest extends TestCase
{
    /**
     * @var string
     */
    private $builderClassName = TransportBuilder::class;

    /**
     * @var TransportBuilder
     */
    private $builder;

    /**
     * @var FactoryInterface|MockObject
     */
    private $templateFactoryMock;

    /**
     * @var Message|MockObject
     */
    private $messageMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var SenderResolverInterface|MockObject
     */
    private $senderResolverMock;

    /**
     * @var MockObject
     */
    private $mailTransportFactoryMock;

    /**
     * @var MessageInterfaceFactory|MockObject
     */
    private $messageFactoryMock;

    /**
     * @var MockObject
     */
    private $emailMessageInterfaceFactoryMock;

    /**
     * @var MockObject
     */
    private $mimePartFactoryMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->templateFactoryMock = $this->getMockForAbstractClass(FactoryInterface::class);
        $this->messageMock = $this->getMockBuilder(MessageInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['setBodyHtml'])
            ->onlyMethods(['setSubject'])
            ->getMockForAbstractClass();

        $this->emailMessageInterfaceFactoryMock = $this->createMock(EmailMessageInterfaceFactory::class);
        $this->mimePartFactoryMock = $this->createMock(MimePartInterfaceFactory::class);

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->senderResolverMock = $this->getMockForAbstractClass(SenderResolverInterface::class);
        $this->mailTransportFactoryMock = $this->getMockBuilder(TransportInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->builder = $objectManagerHelper->getObject(
            $this->builderClassName,
            [
                'templateFactory' => $this->templateFactoryMock,
                'message' => $this->messageMock,
                'objectManager' => $this->objectManagerMock,
                'senderResolver' => $this->senderResolverMock,
                'mailTransportFactory' => $this->mailTransportFactoryMock,
                'messageFactory' => $this->messageFactoryMock,
                'emailMessageInterfaceFactory' => $this->emailMessageInterfaceFactoryMock,
                'mimePartInterfaceFactory' => $this->mimePartFactoryMock,
            ]
        );
    }

    /**
     * @param int $templateType
     * @param string $bodyText
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws LocalizedException
     */
    public function testGetTransport(
        $templateType = TemplateTypesInterface::TYPE_HTML,
        $bodyText = '<h1>Html message</h1>'
    ): void {
        $filter = $this->createMock(Filter::class);
        $data = [
            'template_subject' => 'Email Subject',
            'template_text' => $bodyText,
            'template_styles' => 'Styles',
            'template_type' => $templateType,
            'template_filter' => $filter,
        ];
        $vars = ['reason' => 'Reason', 'customer' => 'Customer'];
        $options = ['area' => 'frontend', 'store' => 1];

        /** @var MimePartInterface|MockObject $mimePartMock */
        $mimePartMock = $this->getMockForAbstractClass(MimePartInterface::class);

        $this->mimePartFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($mimePartMock);

        /** @var EmailMessageInterface|MockObject $emailMessage */
        $emailMessage = $this->getMockForAbstractClass(EmailMessageInterface::class);

        $this->emailMessageInterfaceFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($emailMessage);

        $template = $this->createMock(Template::class);
        $template->expects($this->once())->method('setVars')
            ->with($vars)->willReturnSelf();
        $template->expects($this->once())->method('setOptions')
            ->with($options)->willReturnSelf();
        $template->expects($this->once())->method('getSubject')
            ->willReturn('Email Subject');
        $template->expects($this->once())->method('setData')
            ->with($data)->willReturnSelf();
        $template->expects($this->once())->method('getProcessedTemplate')
            ->with($vars)->willReturn($bodyText);
        $template->expects($this->once())->method('setTemplateFilter')
            ->with($filter);

        $this->templateFactoryMock->expects($this->once())
            ->method('get')
            ->with('identifier')
            ->willReturn($template);

        $this->builder->setTemplateIdentifier(
            'identifier'
        )->setTemplateVars(
            $vars
        )->setTemplateOptions(
            $options
        )->setTemplateData(
            $data
        );

        $this->builder->getTransport();
    }
}
