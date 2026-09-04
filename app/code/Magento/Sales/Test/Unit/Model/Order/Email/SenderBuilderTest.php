<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Email;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\SenderBuilder;
use Magento\Sales\Test\Unit\Model\Order\Email\Stub\TransportInterfaceMock;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class SenderBuilderTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var SenderBuilder
     */
    protected $senderBuilder;

    /**
     * @var MockObject
     */
    protected $templateContainerMock;

    /**
     * @var MockObject
     */
    protected $identityContainerMock;

    /**
     * @var MockObject
     */
    protected $transportBuilder;

    /**
     * @var MockObject
     */
    private $storeMock;

    protected function setUp(): void
    {
        $this->templateContainerMock = $this->createPartialMock(
            Template::class,
            ['getTemplateVars', 'getTemplateOptions', 'getTemplateId']
        );

        $this->storeMock = $this->createPartialMockWithReflection(
            Store::class,
            ['getStoreId', 'getId']
        );

        $this->identityContainerMock = $this->createPartialMockWithReflection(
            ShipmentIdentity::class,
            [
                'getTemplateOptions', 'getEmailIdentity', 'getCustomerEmail', 'getCustomerName',
                'getEmailCopyTo', 'getCopyMethod', 'getStore'
            ]
        );

        $this->transportBuilder = $this->createPartialMock(
            TransportBuilder::class,
            [
                'addTo',
                'addBcc',
                'getTransport',
                'setTemplateIdentifier',
                'setTemplateOptions',
                'setTemplateVars',
                'setFromByScope',
            ]
        );

        $this->senderBuilder = new SenderBuilder(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->transportBuilder
        );
    }

    public function testSend()
    {
        $this->setExpectedCount(1);
        $customerName = 'test_name';
        $customerEmail = 'test_email';
        $identity = 'email_identity_test';

        $transportMock = $this->createMock(
            TransportInterfaceMock::class
        );

        $this->identityContainerMock->expects($this->once())
            ->method('getEmailCopyTo')
            ->willReturn(['example@mail.com']);
        $this->identityContainerMock->expects($this->once())
            ->method('getCopyMethod')
            ->willReturn('bcc');
        $this->identityContainerMock->expects($this->once())
            ->method('getCustomerEmail')
            ->willReturn($customerEmail);
        $this->identityContainerMock->expects($this->once())
            ->method('getCustomerName')
            ->willReturn($customerName);
        $this->identityContainerMock->expects($this->exactly(1))
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->transportBuilder->expects($this->exactly(1))
            ->method('setFromByScope')
            ->with($identity, 1);
        $this->transportBuilder->expects($this->exactly(1))
            ->method('addTo')
            ->with($customerEmail, $customerName);

        $this->transportBuilder->expects($this->exactly(1))
            ->method('getTransport')
            ->willReturn($transportMock);

        $this->senderBuilder->send();
    }

    public function testSendCopyTo()
    {
        $this->setExpectedCount(2);
        $identity = 'email_identity_test';
        $transportMock = $this->createMock(
            TransportInterfaceMock::class
        );
        $this->identityContainerMock->expects($this->never())
            ->method('getCustomerEmail');
        $this->identityContainerMock->expects($this->never())
            ->method('getCustomerName');
        $this->transportBuilder->expects($this->exactly(2))
            ->method('addTo');
        $this->transportBuilder->expects($this->exactly(2))
            ->method('setFromByScope')
            ->with($identity, 1);
        $this->identityContainerMock->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(1);
        $this->transportBuilder->expects($this->exactly(2))
            ->method('getTransport')
            ->willReturn($transportMock);

        $this->senderBuilder->sendCopyTo();
    }

    /**
     * Sets expected count invocation.
     *
     * @param int $count
     */
    private function setExpectedCount(int $count = 1)
    {
        $templateId = 'test_template_id';
        $templateOptions = ['option1', 'option2'];
        $templateVars = ['var1', 'var2'];
        $emailIdentity = 'email_identity_test';
        $emailCopyTo = ['example@mail.com', 'example2@mail.com'];

        $this->templateContainerMock->expects($this->exactly($count))
            ->method('getTemplateId')
            ->willReturn($templateId);
        $this->transportBuilder->expects($this->exactly($count))
            ->method('setTemplateIdentifier')
            ->with($templateId);
        $this->templateContainerMock->expects($this->exactly($count))
            ->method('getTemplateOptions')
            ->willReturn($templateOptions);
        $this->transportBuilder->expects($this->exactly($count))
            ->method('setTemplateOptions')
            ->with($templateOptions);
        $this->templateContainerMock->expects($this->exactly($count))
            ->method('getTemplateVars')
            ->willReturn($templateVars);
        $this->transportBuilder->expects($this->exactly($count))
            ->method('setTemplateVars')
            ->with($templateVars);

        $this->identityContainerMock->expects($this->exactly($count))
            ->method('getEmailIdentity')
            ->willReturn($emailIdentity);
        $this->transportBuilder->expects($this->exactly($count))
            ->method('setFromByScope')
            ->with($emailIdentity, 1);

        $this->identityContainerMock->expects($this->once())
            ->method('getEmailCopyTo')
            ->willReturn($emailCopyTo);
    }
}
