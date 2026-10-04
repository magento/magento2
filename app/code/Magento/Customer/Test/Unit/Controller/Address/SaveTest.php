<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Controller\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Controller\Adminhtml\Address\Save;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class SaveTest extends TestCase
{
    use MockCreationTrait;

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
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var CustomerRegistry|MockObject
     */
    private $customerRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->addressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);
        $this->formFactoryMock = $this->createMock(FormFactory::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper ::class);
        $this->addressDataFactoryMock = $this->createMock(AddressInterfaceFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->address = $this->createMock(AddressInterface::class);
        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->json = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRegistry = $this->getMockBuilder(CustomerRegistry::class)
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
                'logger'                => $this->loggerMock,
                'request'               => $this->requestMock,
                'resultJsonFactory'     => $this->resultJsonFactory,
                'storeManager'          => $this->storeManager,
                'customerRegistry'      => $this->customerRegistry,
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
            ->willReturnCallback(
                function ($arg) {
                    if ($arg == 'parent_id') {
                        return 22;
                    } elseif ($arg == 'entity_id') {
                        return 1;
                    }
                }
            );

        $customerMock = $this->createMock(CustomerInterface::class);

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

        $addressMock = $this->createMock(AddressInterface::class);

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

        $customerModel = $this->createPartialMockWithReflection(
            Customer::class,
            ['getStoreId']
        );
        $customerModel->method('getStoreId')
            ->willReturn(2);
        $this->customerRegistry->expects($this->once())
            ->method('retrieve')
            ->with(22)
            ->willReturn($customerModel);

        $this->storeManager->expects($this->once())
            ->method('setCurrentStore')
            ->with(2);

        $this->assertEquals($this->json, $this->model->execute());
    }
}
