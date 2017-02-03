<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

class ValidateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Api\Data\CustomerInterface
     */
    protected $customer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Model\Metadata\Form
     */
    protected $form;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Controller\Result\JsonFactory */
    protected $resultJsonFactory;

    /** @var  \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Controller\Result\Json */
    protected $resultJson;

    /** @var \Magento\Customer\Controller\Adminhtml\Index\Validate */
    protected $controller;

    public function setUp()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->customer = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\CustomerInterface',
            [],
            '',
            false,
            true,
            true
        );
        $this->customer->expects($this->once())->method('getWebsiteId')->willReturn(2);
        $this->customerDataFactory = $this->getMock(
            'Magento\Customer\Api\Data\CustomerInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->customerDataFactory->expects($this->once())->method('create')->willReturn($this->customer);
        $this->form = $this->getMock(
            'Magento\Customer\Model\Metadata\Form',
            [],
            [],
            '',
            false
        );
        $this->request = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            true,
            true,
            ['getPost']
        );
        $this->response = $this->getMockForAbstractClass(
            'Magento\Framework\App\ResponseInterface',
            [],
            '',
            false
        );
        $this->formFactory = $this->getMock('Magento\Customer\Model\Metadata\FormFactory', ['create'], [], '', false);
        $this->formFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->form);
        $this->extensibleDataObjectConverter = $this->getMock(
            'Magento\Framework\Api\ExtensibleDataObjectConverter',
            [],
            [],
            '',
            false
        );
        $this->dataObjectHelper = $this->getMock('Magento\Framework\Api\DataObjectHelper', [], [], '', false);
        $this->dataObjectHelper->expects($this->once())->method('populateWithArray');
        $this->customerAccountManagement = $this->getMockForAbstractClass(
            'Magento\Customer\Api\AccountManagementInterface',
            [],
            '',
            false,
            true,
            true
        );
        $this->resultJson = $this->getMock('Magento\Framework\Controller\Result\Json', [], [], '', false);
        $this->resultJson->expects($this->once())->method('setData');
        $this->resultJsonFactory = $this->getMock(
            'Magento\Framework\Controller\Result\JsonFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($this->resultJson);

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectHelper->getObject(
            'Magento\Customer\Controller\Adminhtml\Index\Validate',
            [
                'request' => $this->request,
                'response' => $this->response,
                'customerDataFactory' => $this->customerDataFactory,
                'formFactory' => $this->formFactory,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverter,
                'customerAccountManagement' => $this->customerAccountManagement,
                'resultJsonFactory' => $this->resultJsonFactory,
                'dataObjectHelper' => $this->dataObjectHelper,
            ]
        );
    }

    public function testExecute()
    {
        $this->request->expects($this->once())
            ->method('getPost')
            ->willReturn([
                '_template_' => null,
                'address_index' => null
            ]);

        $this->form->expects($this->once())->method('setInvisibleIgnored');
        $this->form->expects($this->atLeastOnce())->method('extractData')->willReturn([]);

        $error = $this->getMock('Magento\Framework\Message\Error', [], [], '', false);
        $this->form->expects($this->once())
            ->method('validateData')
            ->willReturn([$error]);

        $this->extensibleDataObjectConverter->expects($this->once())
            ->method('toFlatArray')
            ->willReturn([]);

        $validationResult = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\ValidationResultsInterface',
            [],
            '',
            false,
            true,
            true
        );
        $validationResult->expects($this->once())
            ->method('getMessages')
            ->willReturn(['Error message']);

        $this->customerAccountManagement->expects($this->once())
            ->method('validate')
            ->willReturn($validationResult);

        $this->controller->execute();
    }

    public function testExecuteWithoutAddresses()
    {
        $this->request->expects($this->once())
            ->method('getPost')
            ->willReturn(null);
        $this->form->expects($this->once())
            ->method('setInvisibleIgnored');
        $this->form->expects($this->atLeastOnce())
            ->method('extractData')
            ->willReturn([]);

        $error = $this->getMock('Magento\Framework\Message\Error', [], [], '', false);
        $this->form->expects($this->never())
            ->method('validateData')
            ->willReturn([$error]);

        $this->extensibleDataObjectConverter->expects($this->once())
            ->method('toFlatArray')
            ->willReturn([]);

        $validationResult = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\ValidationResultsInterface',
            [],
            '',
            false,
            true,
            true
        );
        $validationResult->expects($this->once())
            ->method('getMessages')
            ->willReturn(['Error message']);

        $this->customerAccountManagement->expects($this->once())
            ->method('validate')
            ->willReturn($validationResult);

        $this->controller->execute();
    }

    public function testExecuteWithException()
    {
        $this->request->expects($this->once())
            ->method('getPost')
            ->willReturn(null);
        $this->form->expects($this->once())
            ->method('setInvisibleIgnored');
        $this->form->expects($this->atLeastOnce())
            ->method('extractData')
            ->willReturn([]);

        $this->form->expects($this->never())
            ->method('validateData');

        $this->extensibleDataObjectConverter->expects($this->once())
            ->method('toFlatArray')
            ->willReturn([]);

        $validationResult = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\ValidationResultsInterface',
            [],
            '',
            false,
            true,
            true
        );
        $error = $this->getMock('Magento\Framework\Message\Error', [], [], '', false);
        $error->expects($this->once())
            ->method('getText')
            ->willReturn('Error text');

        $exception = $this->getMock('Magento\Framework\Validator\Exception', [], [], '', false);
        $exception->expects($this->once())
            ->method('getMessages')
            ->willReturn([$error]);
        $validationResult->expects($this->once())
            ->method('getMessages')
            ->willThrowException($exception);

        $this->customerAccountManagement->expects($this->once())
            ->method('validate')
            ->willReturn($validationResult);

        $this->controller->execute();
    }
}
