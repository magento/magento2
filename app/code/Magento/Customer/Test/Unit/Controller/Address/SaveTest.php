<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\Adminhtml\Address\Save;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends TestCase
{
    /**
     * @var Save
     */
    private $model;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var FormFactory|MockObject
     */
    private $formFactoryMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var AddressInterfaceFactory|MockObject
     */
    private $addressDataFactoryMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var AddressInterface|MockObject
     */
    private $address;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactory;

    /**
     * @var Json|MockObject
     */
    private $json;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->addressRepositoryMock = $this->getMockForAbstractClass(AddressRepositoryInterface::class);
        $this->formFactoryMock = $this->createMock(FormFactory::class);
        $this->customerRepositoryMock = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper ::class);
        $this->addressDataFactoryMock = $this->createMock(AddressInterfaceFactory::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
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
            Save::class,
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

        $customerMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->customerRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerMock);

        $customerAddressFormMock = $this->createMock(Form::class);
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
            ->getMockForAbstractClass();

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
                    'messages' => __('Customer address has been updated.'),
                    'error' => false,
                    'data' => [
                        'entity_id' => $addressId
                    ]
                ]
            )->willReturnSelf();

        $this->assertEquals($this->json, $this->model->execute());
    }
}
