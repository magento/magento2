<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Controller\Adminhtml\Address\Validate
     */
    private $model;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $formFactoryMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->formFactoryMock = $this->createMock(\Magento\Customer\Model\Metadata\FormFactory::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->resultJsonFactoryMock = $this->createMock(\Magento\Framework\Controller\Result\JsonFactory::class);
        $this->resultRedirectFactoryMock = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\Address\Validate::class,
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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

        $customerAddressFormMock = $this->createMock(\Magento\Customer\Model\Metadata\Form::class);

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

        $resultJson = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->resultJsonFactoryMock->method('create')
            ->willReturn($resultJson);

        $validateResponseMock = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['getError', 'setMessages']
        );
        $validateResponseMock->method('setMessages')->willReturnSelf();
        $validateResponseMock->method('getError')->willReturn(1);

        $resultJson->method('setData')->willReturnSelf();

        $this->assertEquals($resultJson, $this->model->execute());
    }
}
