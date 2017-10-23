<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Model\Session;
use Magento\Customer\Controller\RegistryConstants;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewsletterTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendSessionMock;

    public function setUp()
    {
        $this->contextMock = $this->createMock(\Magento\Backend\Block\Template\Context::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->formFactoryMock = $this->createMock(\Magento\Framework\Data\FormFactory::class);
        $this->subscriberFactoryMock = $this->createPartialMock(\Magento\Newsletter\Model\SubscriberFactory::class, ['create']);
        $this->accountManagementMock = $this->createMock(\Magento\Customer\Api\AccountManagementInterface::class);
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->backendSessionMock = $this->getMockBuilder(\Magento\Backend\Model\Session::class)
            ->setMethods(['getCustomerFormData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $this->contextMock->expects($this->once())->method('getBackendSession')->willReturn($this->backendSessionMock);

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
        $customerId = 1;

        $subscriberMock = $this->createMock(\Magento\Newsletter\Model\Subscriber::class);
        $fieldsetMock = $this->createMock(\Magento\Framework\Data\Form\Element\Fieldset::class);
        $elementMock = $this->createPartialMock(\Magento\Framework\Data\Form\Element\Checkbox::class, ['setIsChecked']);
        $formMock = $this->createPartialMock(\Magento\Framework\Data\Form::class, ['setHtmlIdPrefix', 'addFieldset', 'setValues', 'getElement', 'setForm', 'setParent', 'setBaseUrl']);
        $this->registryMock->expects($this->exactly(3))
            ->method('registry')
            ->willReturnMap(
                [
                    [RegistryConstants::CURRENT_CUSTOMER_ID, $customerId],
                    ['subscriber', $subscriberMock],
                ]
            );
        $this->formFactoryMock->expects($this->once())->method('create')->willReturn($formMock);
        $formMock->expects($this->once())->method('setHtmlIdPrefix')->with('_newsletter');
        $this->subscriberFactoryMock->expects($this->once())->method('create')->willReturn($subscriberMock);
        $subscriberMock->expects($this->once())->method('loadByCustomerId')->with($customerId)->willReturnSelf();
        $this->registryMock->expects($this->once())->method('register')->with('subscriber', $subscriberMock);
        $formMock->expects($this->once())->method('addFieldset')->willReturn($fieldsetMock);
        $fieldsetMock->expects($this->once())->method('addField')->willReturn($elementMock);
        $this->accountManagementMock->expects($this->once())->method('isReadOnly')->with($customerId)
            ->willReturn(false);
        $subscriberMock->expects($this->once())->method('isSubscribed')->willReturn(true);
        $this->urlBuilderMock->expects($this->once())->method('getBaseUrl')->willReturn('domain.com');

        $this->backendSessionMock->expects($this->once())->method('getCustomerFormData')->willReturn(null);

        $formMock->expects($this->once())
            ->method('getElement')
            ->willReturnMap(
                [
                    ['subscription', $elementMock],
                ]
            );

        $elementMock->expects($this->once())
            ->method('setIsChecked')
            ->with(true);

        $this->assertSame($this->model, $this->model->initForm());
    }

    public function testInitFormWithCustomerFormData()
    {
        $customerId = 1;

        $subscriberMock = $this->createMock(\Magento\Newsletter\Model\Subscriber::class);
        $fieldsetMock = $this->createMock(\Magento\Framework\Data\Form\Element\Fieldset::class);
        $elementMock = $this->createPartialMock(\Magento\Framework\Data\Form\Element\Checkbox::class, ['setIsChecked']);
        $formMock = $this->createPartialMock(\Magento\Framework\Data\Form::class, ['setHtmlIdPrefix', 'addFieldset', 'setValues', 'getElement', 'setForm', 'setParent', 'setBaseUrl']);
        $this->registryMock->expects($this->exactly(3))
            ->method('registry')
            ->willReturnMap(
                [
                    [RegistryConstants::CURRENT_CUSTOMER_ID, $customerId],
                    ['subscriber', $subscriberMock],
                ]
            );
        $this->formFactoryMock->expects($this->once())->method('create')->willReturn($formMock);
        $formMock->expects($this->once())->method('setHtmlIdPrefix')->with('_newsletter');
        $this->subscriberFactoryMock->expects($this->once())->method('create')->willReturn($subscriberMock);
        $subscriberMock->expects($this->once())->method('loadByCustomerId')->with($customerId)->willReturnSelf();
        $formMock->expects($this->once())->method('addFieldset')->willReturn($fieldsetMock);
        $fieldsetMock->expects($this->once())->method('addField')->willReturn($elementMock);
        $this->accountManagementMock->expects($this->once())->method('isReadOnly')->with($customerId)
            ->willReturn(false);
        $subscriberMock->expects($this->once())->method('isSubscribed')->willReturn(false);
        $this->urlBuilderMock->expects($this->once())->method('getBaseUrl')->willReturn('domain.com');

        $this->backendSessionMock->expects($this->once())
            ->method('getCustomerFormData')
            ->willReturn([
                'customer' => [
                    'entity_id' => $customerId,
                ],
                'subscription' => true,
            ]);

        $formMock->expects($this->exactly(2))
            ->method('getElement')
            ->willReturnMap(
                [
                    ['subscription', $elementMock],
                ]
            );

        $elementMock->expects($this->exactly(2))
            ->method('setIsChecked')
            ->willReturnMap(
                [
                    [false],
                    [true],
                ]
            );

        $this->assertSame($this->model, $this->model->initForm());
    }
}
