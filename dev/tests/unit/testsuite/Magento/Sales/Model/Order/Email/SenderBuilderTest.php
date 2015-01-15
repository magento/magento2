<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email;

class SenderBuilderTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $templateId = 'test_template_id';
        $templateOptions = ['option1', 'option2'];
        $templateVars = ['var1', 'var2'];
        $emailIdentity = 'email_identity_test';
        $emailCopyTo = ['example@mail.com'];

        $this->templateContainerMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Container\Template',
            ['getTemplateVars', 'getTemplateOptions', 'getTemplateId'],
            [],
            '',
            false
        );

        $this->storeMock = $this->getMock(
            '\Magento\Store\Model\Store',
            ['getStoreId', '__wakeup'],
            [],
            '',
            false
        );

        $this->identityContainerMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Container\ShipmentIdentity',
            [
                'getEmailIdentity', 'getCustomerEmail',
                'getCustomerName', 'getTemplateOptions', 'getEmailCopyTo',
                'getCopyMethod'
            ],
            [],
            '',
            false
        );

        $this->transportBuilder = $this->getMock(
            '\Magento\Framework\Mail\Template\TransportBuilder',
            [
                'addTo', 'addBcc', 'getTransport',
                'setTemplateIdentifier', 'setTemplateOptions', 'setTemplateVars',
                'setFrom',
            ],
            [],
            '',
            false
        );

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
        $this->transportBuilder->expects($this->once())
            ->method('setFrom')
            ->with($this->equalTo($emailIdentity));

        $this->identityContainerMock->expects($this->once())
            ->method('getEmailCopyTo')
            ->will($this->returnValue($emailCopyTo));

        $this->senderBuilder = new SenderBuilder(
            $this->templateContainerMock,
            $this->identityContainerMock,
            $this->transportBuilder
        );
    }

    public function testSend()
    {
        $customerName = 'test_name';
        $customerEmail = 'test_email';
        $transportMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Stub\TransportInterfaceMock',
            [],
            [],
            '',
            false
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
        $transportMock = $this->getMock(
            '\Magento\Sales\Model\Order\Email\Stub\TransportInterfaceMock',
            [],
            [],
            '',
            false
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

        $this->transportBuilder->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transportMock));

        $this->senderBuilder->sendCopyTo();
    }
}
