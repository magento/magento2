<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail\Test\Unit\Template;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\Message;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterface;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TemplateInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransportBuilderTest extends TestCase
{
    /**
     * @var string
     */
    protected $builderClassName = TransportBuilder::class;

    /**
     * @var TransportBuilder
     */
    protected $builder;

    /**
     * @var FactoryInterface|MockObject
     */
    protected $templateFactoryMock;

    /**
     * @var Message|MockObject
     */
    protected $messageMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var SenderResolverInterface|MockObject
     */
    protected $senderResolverMock;

    /**
     * @var MessageInterfaceFactory|MockObject
     */
    private $messageFactoryMock;

    /**
     * @var MockObject
     */
    protected $mailTransportFactoryMock;

    /**
     * @var MimePartInterfaceFactory|MockObject
     */
    private $mimePartFactoryMock;

    /**
     * @var EmailMessageInterfaceFactory|MockObject
     */
    private $emailMessageInterfaceFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->templateFactoryMock = $this->getMockForAbstractClass(FactoryInterface::class);
        $this->messageMock = $this->createMock(Message::class);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->senderResolverMock = $this->getMockForAbstractClass(SenderResolverInterface::class);
        $this->mailTransportFactoryMock = $this->getMockBuilder(TransportInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();
        $this->messageFactoryMock = $this->getMockBuilder(MessageInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();

        $this->emailMessageInterfaceFactoryMock = $this->createMock(EmailMessageInterfaceFactory::class);
        $this->mimePartFactoryMock = $this->createMock(MimePartInterfaceFactory::class);

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
                'mimePartInterfaceFactory' => $this->mimePartFactoryMock
            ]
        );
    }

    /**
     * @param int $templateType
     * @param string $bodyText
     * @param string $templateNamespace
     *
     * @return void
     * @dataProvider getTransportDataProvider
     */
    public function testGetTransport($templateType, $bodyText, $templateNamespace): void
    {
        $this->builder->setTemplateModel($templateNamespace);

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

        $template = $this->getMockForAbstractClass(TemplateInterface::class);
        $template->expects($this->once())->method('setVars')->with($vars)->willReturnSelf();
        $template->expects($this->once())->method('setOptions')->with($options)->willReturnSelf();
        $template->expects($this->once())->method('getSubject')->willReturn('Email Subject');
        $template->expects($this->once())->method('getType')->willReturn($templateType);
        $template->expects($this->once())->method('processTemplate')->willReturn($bodyText);

        $this->templateFactoryMock->expects($this->once())
            ->method('get')
            ->with('identifier', $templateNamespace)
            ->willReturn($template);

        $transport = $this->getMockForAbstractClass(TransportInterface::class);

        $this->mailTransportFactoryMock
            ->method('create')
            ->willReturn($transport);

        $this->builder->setTemplateIdentifier('identifier')->setTemplateVars($vars)->setTemplateOptions($options);

        $result = $this->builder->getTransport();
        $this->assertInstanceOf(TransportInterface::class, $result);
    }

    /**
     * Test get transport with exception.
     *
     * @return void
     */
    public function testGetTransportWithException(): void
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Unknown template type');
        $this->builder->setTemplateModel('Test\Namespace\Template');

        $vars = ['reason' => 'Reason', 'customer' => 'Customer'];
        $options = ['area' => 'frontend', 'store' => 1];

        $template = $this->getMockForAbstractClass(TemplateInterface::class);
        $template->expects($this->once())->method('setVars')->with($vars)->willReturnSelf();
        $template->expects($this->once())->method('setOptions')->with($options)->willReturnSelf();
        $template->expects($this->once())->method('getType')->willReturn('Unknown');
        $this->templateFactoryMock->expects($this->once())
            ->method('get')
            ->with('identifier', 'Test\Namespace\Template')
            ->willReturn($template);

        $this->builder->setTemplateIdentifier('identifier')->setTemplateVars($vars)->setTemplateOptions($options);

        $this->assertInstanceOf(TransportInterface::class, $this->builder->getTransport());
    }

    /**
     * @return array
     */
    public function getTransportDataProvider(): array
    {
        return [
            [
                TemplateTypesInterface::TYPE_TEXT,
                'Plain text',
                null
            ],
            [
                TemplateTypesInterface::TYPE_HTML,
                '<h1>Html message</h1>',
                'Test\Namespace\Template'
            ]
        ];
    }

    /**
     * @return void
     */
    public function testSetFromByScope(): void
    {
        $sender = ['email' => 'from@example.com', 'name' => 'name'];
        $scopeId = 1;
        $this->senderResolverMock->expects($this->once())
            ->method('resolve')
            ->with($sender, $scopeId)
            ->willReturn($sender);

        $this->builder->setFromByScope($sender, $scopeId);
    }
}
