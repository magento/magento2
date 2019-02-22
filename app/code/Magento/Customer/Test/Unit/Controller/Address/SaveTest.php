<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Controller\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
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
     * @var AddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $address;

    /**
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactory;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $json;

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
        $this->address = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->json = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);

        $this->model = $objectManager->getObject(
            \Magento\Customer\Controller\Adminhtml\Address\Save::class,
            [
                'addressRepository'     => $this->addressRepositoryMock,
                'formFactory'           => $this->formFactoryMock,
                'customerRepository'    => $this->customerRepositoryMock,
                'dataObjectHelper'      => $this->dataObjectHelperMock,
                'addressDataFactory'    => $this->addressDataFactoryMock,
                'logger'            => $this->loggerMock,
                'request'               => $this->requestMock,
                'resultJsonFactory' => $this->resultJsonFactory
            ]
        );
    }

    public function testExecute(): void
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

        $addressMock = $this->getMockBuilder(AddressInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressDataFactoryMock->expects($this->once())->method('create')->willReturn($addressMock);

        $this->dataObjectHelperMock->expects($this->atLeastOnce())
            ->method('populateWithArray')
            ->willReturn(
                [
                    $addressMock,
                    $mergedAddressData, AddressInterface::class,
                    $this->dataObjectHelperMock,
                ]
            );
        $this->addressRepositoryMock->expects($this->once())->method('save')->willReturn($this->address);
        $this->address->expects($this->once())->method('getId')->willReturn($addressId);

        $this->resultJsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->json);
        $this->json->expects($this->once())
            ->method('setData')
            ->with(
                [
                    'message' => __('Customer address has been updated.'),
                    'error' => false,
                    'data' => [
                        'entity_id' => $addressId
                    ]
                ]
            )->willReturnSelf();

        $this->assertEquals($this->json, $this->model->execute());
    }
}
