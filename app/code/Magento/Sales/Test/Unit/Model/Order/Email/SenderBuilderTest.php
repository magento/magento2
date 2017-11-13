<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Email;

use Magento\Framework\Mail\Template\TransportBuilderByStore;
use Magento\Sales\Model\Order\Email\SenderBuilder;

class SenderBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SenderBuilder
     */
    protected $senderBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateContainerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $identityContainerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transportBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $transportBuilderByStore;

    protected function setUp()
    {
        $templateId = 'test_template_id';
        $templateOptions = ['option1', 'option2'];
        $templateVars = ['var1', 'var2'];
        $emailIdentity = 'email_identity_test';
        $emailCopyTo = ['example@mail.com'];

        $this->templateContainerMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Email\Container\Template::class,
            ['getTemplateVars', 'getTemplateOptions', 'getTemplateId']
        );

        $this->storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, [
            'getStoreId',
            '__wakeup',
            'getId',
        ]);

        $this->identityContainerMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity::class,
            [
                'getEmailIdentity',
                'getCustomerEmail',
                'getCustomerName',
                'getTemplateOptions',
                'getEmailCopyTo',
                'getCopyMethod',
                'getStore',
            ]
        );

        $this->transportBuilder = $this->createPartialMock(
            \Magento\Framework\Mail\Template\TransportBuilder::class,
            [
                'addTo',
                'addBcc',
                'getTransport',
                'setTemplateIdentifier',
                'setTemplateOptions',
                'setTemplateVars',
            ]
        );

        $this->transportBuilderByStore = $this->createMock(TransportBuilderByStore::class);

        $this->templateContainerMock->expects($this->once())
            ->method('getTemplateId')
            ->will($this->returnValue($templateId));
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateIdentifier')
            ->with($this->equalTo($templateId));
        $this->templateContainerMock->expects($this->once())
            ->method('getTemplateOptions')
            ->will($this->returnValue($templateOptions));
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateOptions')
            ->with($this->equalTo($templateOptions));
        $this->templateContainerMock->expects($this->once())
            ->method('getTemplateVars')
            ->will($this->returnValue($templateVars));
        $this->transportBuilder->expects($this->once())
            ->method('setTemplateVars')
            ->with($this->equalTo($templateVars));

        $this->identityContainerMock->expects($this->once())
            ->method('getEmailIdentity')
            ->will($this->returnValue($emailIdentity));
        $this->transportBuilderByStore->expects($this->once())
            ->method('setFromByStore')
            ->with($this->equalTo($emailIdentity));

        $this->identityContainerMock->expects($this->once())
            ->method('getEmailCopyTo')
            ->will($this->returnValue($emailCopyTo));

        $this->senderBuilder = new SenderBuilder(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->transportBuilder,
            $this->transportBuilderByStore
        );
    }

    public function testSend()
    {
        $customerName = 'test_name';
        $customerEmail = 'test_email';
        $transportMock = $this->createMock(
            \Magento\Sales\Test\Unit\Model\Order\Email\Stub\TransportInterfaceMock::class
        );

        $this->identityContainerMock->expects($this->once())
            ->method('getEmailCopyTo')
            ->will($this->returnValue(['example@mail.com']));
        $this->identityContainerMock->expects($this->once())
            ->method('getCopyMethod')
            ->will($this->returnValue('bcc'));
        $this->identityContainerMock->expects($this->once())
            ->method('getCustomerEmail')
            ->will($this->returnValue($customerEmail));
        $this->identityContainerMock->expects($this->once())
            ->method('getCustomerName')
            ->will($this->returnValue($customerName));
        $this->identityContainerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->transportBuilder->expects($this->once())
            ->method('addTo')
            ->with($this->equalTo($customerEmail), $this->equalTo($customerName));

        $this->transportBuilder->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transportMock));

        $this->senderBuilder->send();
    }

    public function testSendCopyTo()
    {
        $transportMock = $this->createMock(
            \Magento\Sales\Test\Unit\Model\Order\Email\Stub\TransportInterfaceMock::class
        );
        $this->identityContainerMock->expects($this->once())
            ->method('getCopyMethod')
            ->will($this->returnValue('copy'));
        $this->identityContainerMock->expects($this->never())
            ->method('getCustomerEmail');
        $this->identityContainerMock->expects($this->never())
            ->method('getCustomerName');
        $this->transportBuilder->expects($this->once())
            ->method('addTo')
            ->with($this->equalTo('example@mail.com'));
        $this->identityContainerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->transportBuilder->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transportMock));

        $this->senderBuilder->sendCopyTo();
    }
}
