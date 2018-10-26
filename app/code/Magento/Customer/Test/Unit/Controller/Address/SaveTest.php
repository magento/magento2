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
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Controller\Adminhtml\Address\Save
     */
    private $model;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formFactoryMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressDataFactoryMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->addressRepositoryMock = $this->createMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $this->formFactoryMock = $this->createMock(\Magento\Customer\Model\Metadata\FormFactory::class);
        $this->customerRepositoryMock = $this->createMock(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $this->dataObjectHelperMock = $this->createMock(\Magento\Framework\Api\DataObjectHelper ::class);
        $this->addressDataFactoryMock = $this->createMock(\Magento\Customer\Api\Data\AddressInterfaceFactory::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->resultRedirectFactoryMock = $this->createMock(\Magento\Backend\Model\View\Result\RedirectFactory::class);
        $this->messageManagerMock = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\Address\Save::class,
            [
                'addressRepository'     => $this->addressRepositoryMock,
                'formFactory'           => $this->formFactoryMock,
                'customerRepository'    => $this->customerRepositoryMock,
                'dataObjectHelper'      => $this->dataObjectHelperMock,
                'addressDataFactory'    => $this->addressDataFactoryMock,
                'loggerMock'            => $this->loggerMock,
                'request'               => $this->requestMock,
                'resultRedirectFactory' => $this->resultRedirectFactoryMock,
                'messageManager'        => $this->messageManagerMock,
            ]
        );
    }

    /**
     * Test method \Magento\Customer\Controller\Adminhtml\Address\Save::execute
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testExecute()
    {
        $addressId = 11;
        $customerId = 22;

        $addressExtractedData = [
            'entity_id' => $addressId,
            'code'      => 'value',
            'coolness'  => false,
            'region'    => 'region',
            'region_id' => 'region_id',
        ];

        $addressCompactedData = [
            'entity_id'        => $addressId,
            'default_billing'  => 'true',
            'default_shipping' => 'true',
            'code'             => 'value',
            'coolness'         => false,
            'region'           => 'region',
            'region_id'        => 'region_id',
        ];

        $mergedAddressData = [
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

        $this->requestMock->method('getParam')
            ->withConsecutive(['parent_id'], ['entity_id'])
            ->willReturnOnConsecutiveCalls(22, 1);

        $customerMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerInterface::class
        )->disableOriginalConstructor()->getMock();

        $this->customerRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $customerAddressFormMock = $this->createMock(\Magento\Customer\Model\Metadata\Form::class);

        $customerAddressFormMock->expects($this->atLeastOnce())
            ->method('extractData')
            ->with($this->requestMock)
            ->willReturn($addressExtractedData);
        $customerAddressFormMock->expects($this->once())
            ->method('compactData')
            ->with($addressExtractedData)
            ->willReturn($addressCompactedData);

        $this->formFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->willReturn($customerAddressFormMock);

        $addressMock = $this->getMockBuilder(\Magento\Customer\Api\Data\AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressDataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($addressMock);

        $this->dataObjectHelperMock->expects($this->atLeastOnce())
            ->method('populateWithArray')
            ->willReturn(
                [
                    $addressMock,
                    $mergedAddressData, \Magento\Customer\Api\Data\AddressInterface::class,
                    $this->dataObjectHelperMock,
                ]
            );

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('Customer address has been updated.'))
            ->willReturnSelf();

        $resultRedirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $resultRedirect->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('customer/index/edit', ['id' => $customerId, '_current' => true])
            ->willReturnSelf();
        $this->resultRedirectFactoryMock->method('create')
            ->willReturn($resultRedirect);

        $this->assertEquals($resultRedirect, $this->model->execute());
    }
}
