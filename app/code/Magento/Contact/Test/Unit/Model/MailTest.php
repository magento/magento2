<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Test\Unit\Model;

use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\Mail;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MailTest extends TestCase
{

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlMock;

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

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)->getMockForAbstractClass();
        $this->urlMock = $this->createMock(UrlInterface::class);
        $this->transportBuilderMock = $this->getMockBuilder(
            TransportBuilder::class
        )->disableOriginalConstructor(
        )->getMock();
        $this->inlineTranslationMock = $this->getMockBuilder(
            StateInterface::class
        )->disableOriginalConstructor(
        )->getMock();

        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

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

        $transport = $this->createMock(TransportInterface::class);

        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getId')->willReturn(555);

        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($store);

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateIdentifier')
            ->will($this->returnSelf());

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateOptions')
            ->with([
                'area' => 'frontend',
                'store' => 555,
            ])
            ->will($this->returnSelf());

        $this->transportBuilderMock->expects($this->once())
            ->method('setTemplateVars')
            ->with($templateVars)
            ->will($this->returnSelf());

        $this->transportBuilderMock->expects($this->once())
            ->method('setFrom')
            ->will($this->returnSelf());

        $this->transportBuilderMock->expects($this->once())
            ->method('addTo')
            ->will($this->returnSelf());

        $this->transportBuilderMock->expects($this->once())
            ->method('setReplyTo')
            ->with($email)
            ->will($this->returnSelf());

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
