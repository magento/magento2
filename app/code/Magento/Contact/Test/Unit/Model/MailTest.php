<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Test\Unit\Model;

use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\Mail;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

class MailTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $urlMock;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    private $transportBuilderMock;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $inlineTranslationMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var Mail
     */
    private $mail;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();
        $this->urlMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->transportBuilderMock = $this->getMockBuilder(
            \Magento\Framework\Mail\Template\TransportBuilder::class
        )->disableOriginalConstructor(
        )->getMock();
        $this->inlineTranslationMock = $this->getMockBuilder(
            \Magento\Framework\Translate\Inline\StateInterface::class
        )->disableOriginalConstructor(
        )->getMock();

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->mail = new Mail(
            $this->configMock,
            $this->transportBuilderMock,
            $this->inlineTranslationMock,
            $this->storeManagerMock
        );
    }

    public function testSendMail()
    {
        $email = 'reply-to@example.com';
        $templateVars = ['comment' => 'Comment'];

        $transport = $this->createMock(\Magento\Framework\Mail\TransportInterface::class);

        $store = $this->getMockForAbstractClass(StoreInterface::class);
        $store->expects($this->once())->method('getId')->willReturn(555);

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($store);

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')
            ->willReturnSelf();

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->with([
                'area' => 'frontend',
                'store' => 555,
            ])
            ->willReturnSelf();

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->with($templateVars)
            ->willReturnSelf();

        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->willReturnSelf();

        $this->transportBuilderMock->expects($this->once())
            ->method('addTo')
            ->willReturnSelf();

        $this->transportBuilderMock->expects($this->once())
            ->method('setReplyTo')
            ->with($email)
            ->willReturnSelf();

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
