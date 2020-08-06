<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Test\Unit\Model;

use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\Mail;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Contact\Model\Mail
 */
class MailTest extends TestCase
{
    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var TransportBuilder|MockObject
     */
    private $transportBuilderMock;

    /**
     * @var StateInterface|MockObject
     */
    private $inlineTranslationMock;

    /**
     * @var MockObject
     */
    private $storeManagerMock;

    /**
     * @var Mail
     */
    private $mail;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->transportBuilderMock = $this->getMockBuilder(TransportBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inlineTranslationMock = $this->getMockBuilder(StateInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->mail = $objectManager->getObject(
            Mail::class,
            [
                'contactsConfig' => $this->configMock,
                'transportBuilder' => $this->transportBuilderMock,
                'inlineTranslation' => $this->inlineTranslationMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    /**
     * Test SendMail
     */
    public function testSendMail(): void
    {
        $email = 'reply-to@example.com';
        $templateVars = ['comment' => 'Comment'];

        $transport = $this->getMockForAbstractClass(TransportInterface::class);

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->once())->method('getId')->willReturn(555);

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->with(
                [
                    'area' => 'frontend',
                    'store' => 555,
                ]
            )->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->with($templateVars)->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('addTo')->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('setReplyTo')
            ->with($email)->willReturnSelf();
        $this->transportBuilderMock->expects($this->once())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects($this->once())
            ->method('sendMessage');

        $this->inlineTranslationMock->expects($this->once())
            ->method('resume');
        $this->inlineTranslationMock->expects($this->once())
            ->method('suspend');

        $this->mail->send($email, $templateVars);
    }
}
