<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;

class NewsletterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subscriberFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    public function setUp()
    {
        $this->contextMock = $this->getMock('\Magento\Backend\Block\Template\Context', [], [], '', false);
        $this->registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $this->formFactoryMock = $this->getMock('\Magento\Framework\Data\FormFactory', [], [], '', false);
        $this->subscriberFactoryMock = $this->getMock(
            '\Magento\Newsletter\Model\SubscriberFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->accountManagementMock = $this->getMock(
            '\Magento\Customer\Api\AccountManagementInterface',
            [],
            [],
            '',
            false
        );
        $this->urlBuilderMock = $this->getMock('\Magento\Framework\UrlInterface', [], [], '', false);
        $this->contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = new \Magento\Customer\Block\Adminhtml\Edit\Tab\Newsletter(
            $this->contextMock,
            $this->registryMock,
            $this->formFactoryMock,
            $this->subscriberFactoryMock,
            $this->accountManagementMock
        );
    }

    public function testInitFormCanNotShowTab()
    {
        $this->registryMock->expects($this->once())->method('registry')->with(RegistryConstants::CURRENT_CUSTOMER_ID)
            ->willReturn(false);
        $this->assertSame($this->model, $this->model->initForm());
    }

    public function testInitForm()
    {
        $subscriberMock = $this->getMock('\Magento\Newsletter\Model\Subscriber', [], [], '', false);
        $fieldsetMock = $this->getMock('\Magento\Framework\Data\Form\Element\Fieldset', [], [], '', false);
        $elementMock = $this->getMock('\Magento\Framework\Data\Form\Element\AbstractElement', [], [], '', false);
        $formMock = $this->getMock(
            '\Magento\Framework\Data\Form',
            ['setHtmlIdPrefix', 'addFieldset', 'setValues', 'getElement', 'setForm', 'setParent', 'setBaseUrl'],
            [],
            '',
            false
        );
        $this->registryMock->expects($this->atLeastOnce())->method('registry')->willReturn($subscriberMock);
        $this->formFactoryMock->expects($this->once())->method('create')->willReturn($formMock);
        $formMock->expects($this->once())->method('setHtmlIdPrefix')->with('_newsletter');
        $this->subscriberFactoryMock->expects($this->once())->method('create')->willReturn($subscriberMock);
        $subscriberMock->expects($this->once())->method('loadByCustomerId')->with($subscriberMock)->willReturnSelf();
        $this->registryMock->expects($this->once())->method('register')->with('subscriber', $subscriberMock);
        $formMock->expects($this->once())->method('addFieldset')->willReturn($fieldsetMock);
        $this->accountManagementMock->expects($this->once())->method('isReadOnly')->with($subscriberMock)
            ->willReturn(false);
        $subscriberMock->expects($this->once())->method('isSubscribed')->willReturn(true);
        $formMock->expects($this->once())->method('getElement')->willReturn($elementMock);
        $this->urlBuilderMock->expects($this->once())->method('getBaseUrl')->willReturn('domain.com');
        $this->assertSame($this->model, $this->model->initForm());
    }
}
