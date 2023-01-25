<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Address;

use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Customer\Controller\Adminhtml\Address\Validate;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends TestCase
{
    /**
     * @var Validate
     */
    private $model;

    /**
     * @var FormFactory|MockObject
     */
    private $formFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->formFactoryMock = $this->createMock(FormFactory::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultJsonFactoryMock = $this->createMock(JsonFactory::class);
        $this->resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            Validate::class,
            [
                'formFactory'           => $this->formFactoryMock,
                'request'               => $this->requestMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'resultJsonFactory'     => $this->resultJsonFactoryMock,
            ]
        );
    }

    /**
     * Test method \Magento\Customer\Controller\Adminhtml\Address\Save::execute
     *
     * @throws NoSuchEntityException
     */
    public function testExecute()
    {
        $addressId = 11;
        $errors = ['Error Message 1', 'Error Message 2'];

        $addressExtractedData = [
            'entity_id'        => $addressId,
            'default_billing'  => true,
            'default_shipping' => true,
            'code'             => 'value',
            'region'           => [
                'region'    => 'region',
                'region_id' => 'region_id',
            ],
            'region_id'        => 'region_id',
            'id'               => $addressId,
        ];

        $customerAddressFormMock = $this->createMock(Form::class);

        $customerAddressFormMock->expects($this->atLeastOnce())
            ->method('extractData')
            ->with($this->requestMock)
            ->willReturn($addressExtractedData);
        $customerAddressFormMock->expects($this->once())
            ->method('validateData')
            ->with($addressExtractedData)
            ->willReturn($errors);

        $this->formFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->willReturn($customerAddressFormMock);

        $resultJson = $this->createMock(Json::class);
        $this->resultJsonFactoryMock->method('create')
            ->willReturn($resultJson);

        $validateResponseMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getError', 'setMessages'])
            ->disableOriginalConstructor()
            ->getMock();
        $validateResponseMock->method('setMessages')->willReturnSelf();
        $validateResponseMock->method('getError')->willReturn(1);

        $resultJson->method('setData')->willReturnSelf();

        $this->assertEquals($resultJson, $this->model->execute());
    }
}
