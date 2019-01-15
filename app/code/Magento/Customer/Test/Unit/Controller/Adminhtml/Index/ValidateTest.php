<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->customer = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            [],
            '',
            false,
            true,
            true
        );
        $this->customer->expects($this->once())->method('getWebsiteId')->willReturn(2);
        $this->customerDataFactory = $this->createPartialMock(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class,
            ['create']
        );
        $this->customerDataFactory->expects($this->once())->method('create')->willReturn($this->customer);
        $this->form = $this->createMock(\Magento\Customer\Model\Metadata\Form::class);
        $this->request = $this->getMockForAbstractClass(
            \Magento\Framework\App\RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getPost', 'getParam']
        );
        $this->response = $this->getMockForAbstractClass(
            \Magento\Framework\App\ResponseInterface::class,
            [],
            '',
            false
        );
        $this->formFactory = $this->createPartialMock(\Magento\Customer\Model\Metadata\FormFactory::class, ['create']);
        $this->formFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->form);
        $this->extensibleDataObjectConverter = $this->createMock(
            \Magento\Framework\Api\ExtensibleDataObjectConverter::class
        );
        $this->dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->dataObjectHelper->expects($this->once())->method('populateWithArray');
        $this->customerAccountManagement = $this->getMockForAbstractClass(
            \Magento\Customer\Api\AccountManagementInterface::class,
            [],
            '',
            false,
            true,
            true
        );
        $this->resultJson = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->resultJson->expects($this->once())->method('setData');
        $this->resultJsonFactory = $this->createPartialMock(
            \Magento\Framework\Controller\Result\JsonFactory::class,
            ['create']
        );
        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($this->resultJson);

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectHelper->getObject(
            \Magento\Customer\Controller\Adminhtml\Index\Validate::class,
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
        $customerEntityId = 2;
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('customer')
            ->willReturn([
                'entity_id' => $customerEntityId
            ]);

        $this->customer->expects($this->once())
            ->method('setId')
            ->with($customerEntityId);

        $this->form->expects($this->once())->method('setInvisibleIgnored');
        $this->form->expects($this->atLeastOnce())->method('extractData')->willReturn([]);

        $validationResult = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\ValidationResultsInterface::class,
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
        $this->form->expects($this->once())
            ->method('setInvisibleIgnored');
        $this->form->expects($this->atLeastOnce())
            ->method('extractData')
            ->willReturn([]);

        $error = $this->createMock(\Magento\Framework\Message\Error::class);
        $this->form->expects($this->never())
            ->method('validateData')
            ->willReturn([$error]);

        $validationResult = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\ValidationResultsInterface::class,
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
        $this->form->expects($this->once())
            ->method('setInvisibleIgnored');
        $this->form->expects($this->atLeastOnce())
            ->method('extractData')
            ->willReturn([]);

        $this->form->expects($this->never())
            ->method('validateData');

        $validationResult = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\ValidationResultsInterface::class,
            [],
            '',
            false,
            true,
            true
        );
        $error = $this->createMock(\Magento\Framework\Message\Error::class);
        $error->expects($this->once())
            ->method('getText')
            ->willReturn('Error text');

        $exception = $this->createMock(\Magento\Framework\Validator\Exception::class);
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

    public function testExecuteWithNewCustomerAndNoEntityId()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('customer')
            ->willReturn([]);

        $this->customer->expects($this->never())
            ->method('setId');

        $this->form->expects($this->once())->method('setInvisibleIgnored');
        $this->form->expects($this->atLeastOnce())->method('extractData')->willReturn([]);

        $validationResult = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\ValidationResultsInterface::class,
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
}
