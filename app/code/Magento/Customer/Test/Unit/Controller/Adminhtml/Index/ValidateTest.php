<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Adminhtml\Index;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\ValidationResultsInterface;
use Magento\Customer\Controller\Adminhtml\Index\Validate;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\Error;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends TestCase
{
    /**
     * @var MockObject|RequestInterface
     */
    protected $request;

    /**
     * @var MockObject|ResponseInterface
     */
    protected $response;

    /**
     * @var MockObject|CustomerInterface
     */
    protected $customer;

    /**
     * @var MockObject|CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var MockObject|FormFactory
     */
    protected $formFactory;

    /**
     * @var MockObject|Form
     */
    protected $form;

    /**
     * @var MockObject|ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var MockObject|DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var MockObject|AccountManagementInterface
     */
    protected $customerAccountManagement;

    /** @var  MockObject|JsonFactory */
    protected $resultJsonFactory;

    /** @var  MockObject|Json */
    protected $resultJson;

    /** @var Validate */
    protected $controller;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $this->customer = $this->getMockForAbstractClass(
            CustomerInterface::class,
            [],
            '',
            false,
            true,
            true
        );
        $this->customer->expects($this->once())->method('getWebsiteId')->willReturn(2);
        $this->customerDataFactory = $this->createPartialMock(
            CustomerInterfaceFactory::class,
            ['create']
        );
        $this->customerDataFactory->expects($this->once())->method('create')->willReturn($this->customer);
        $this->form = $this->createMock(Form::class);
        $this->request = $this->getMockForAbstractClass(
            RequestInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getPost', 'getParam']
        );
        $this->response = $this->getMockForAbstractClass(
            ResponseInterface::class,
            [],
            '',
            false
        );
        $this->formFactory = $this->createPartialMock(FormFactory::class, ['create']);
        $this->formFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->form);
        $this->extensibleDataObjectConverter = $this->createMock(
            ExtensibleDataObjectConverter::class
        );
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $this->dataObjectHelper->expects($this->once())->method('populateWithArray');
        $this->customerAccountManagement = $this->getMockForAbstractClass(
            AccountManagementInterface::class,
            [],
            '',
            false,
            true,
            true
        );
        $this->resultJson = $this->createMock(Json::class);
        $this->resultJson->expects($this->once())->method('setData');
        $this->resultJsonFactory = $this->createPartialMock(
            JsonFactory::class,
            ['create']
        );
        $this->resultJsonFactory->expects($this->once())->method('create')->willReturn($this->resultJson);

        $objectHelper = new ObjectManager($this);
        $this->controller = $objectHelper->getObject(
            Validate::class,
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
            ValidationResultsInterface::class,
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

        $error = $this->createMock(Error::class);
        $this->form->expects($this->never())
            ->method('validateData')
            ->willReturn([$error]);

        $validationResult = $this->getMockForAbstractClass(
            ValidationResultsInterface::class,
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
            ValidationResultsInterface::class,
            [],
            '',
            false,
            true,
            true
        );
        $error = $this->createMock(Error::class);
        $error->expects($this->once())
            ->method('getText')
            ->willReturn('Error text');

        $exception = $this->createMock(Exception::class);
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
            ValidationResultsInterface::class,
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
