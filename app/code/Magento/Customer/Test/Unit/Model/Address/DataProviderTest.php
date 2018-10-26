<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Address;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Address\Collection as AddressCollection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Customer\Model\Attribute as AttributeModel;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Model\Address as AddressModel;
use Magento\Customer\Model\FileProcessorFactory;

class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressCollectionFactory;

    /**
     * @var AddressCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collection;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customer;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * @var EavValidationRules|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavValidationRules;

    /*
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Customer\Model\Config\Share|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shareConfig;

    /**
     * @var FileProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileProcessorFactory;

    /**
     * @var Type|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityType;

    /**
     * @var AddressModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $address;

    /**
     * @var AttributeModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    /**
     * @var \Magento\Customer\Model\Address\DataProvider
     */
    private $model;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->addressCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collection = $this->getMockBuilder(AddressCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->eavValidationRules = $this->createMock(EavValidationRules::class);
        $this->context = $this->getMockForAbstractClass(ContextInterface::class);
        $this->fileProcessorFactory = $this->getMockBuilder(FileProcessorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->shareConfig = $this->createMock(\Magento\Customer\Model\Config\Share::class);
        $this->addressCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->collection);
        $this->eavConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityType->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn([]);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->willReturn($this->entityType);
        $this->customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->address = $this->getMockBuilder(AddressModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attribute = $this->getMockBuilder(AttributeModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManagerHelper->getObject(
            \Magento\Customer\Model\Address\DataProvider::class,
            [
                '',
                '',
                '',
                'addressCollectionFactory' => $this->addressCollectionFactory,
                'customerRepository' => $this->customerRepository,
                'eavConfig' => $this->eavConfig,
                'eavValidationRules' => $this->eavValidationRules,
                'context' => $this->context,
                'fileProcessorFactory' => $this->fileProcessorFactory,
                'shareConfig' => $this->shareConfig,
                [],
                [],
                true
            ]
        );
    }

    public function testGetDefaultData()
    {
        $expectedData = [
            '' => [
                'parent_id' => 1,
                'firstname' => 'John',
                'lastname' => 'Doe'
            ]
        ];

        $this->collection->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->context->expects($this->once())
            ->method('getRequestParam')
            ->willReturn(1);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->willReturn($this->customer);
        $this->customer->expects($this->once())
            ->method('getFirstname')
            ->willReturn('John');
        $this->customer->expects($this->once())
            ->method('getLastname')
            ->willReturn('Doe');

        $this->assertEquals($expectedData, $this->model->getData());
    }

    public function testGetData()
    {
        $expectedData = [
            '3' => [
                'parent_id' => "1",
                'firstname' => 'John',
                'lastname' => 'Doe',
                'street' => [
                    '42000 Ave W 55 Cedar City',
                    'Apt. 33'
                ]
            ]
        ];

        $this->collection->expects($this->once())
            ->method('getItems')
            ->willReturn([
                $this->address
            ]);

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->willReturn($this->customer);
        $this->customer->expects($this->once())
            ->method('getDefaultBilling')
            ->willReturn('1');
        $this->customer->expects($this->once())
            ->method('getDefaultShipping')
            ->willReturn('1');

        $this->address->expects($this->once())
            ->method('getEntityId')
            ->willReturn('3');
        $this->address->expects($this->once())
            ->method('load')
            ->with("3")
            ->willReturnSelf();
        $this->address->expects($this->once())
            ->method('getData')
            ->willReturn([
                'parent_id' => "1",
                'firstname' => "John",
                'lastname' => 'Doe',
                'street' => "42000 Ave W 55 Cedar City\nApt. 33"
            ]);
        $this->address->expects($this->once())
            ->method('getAttributes')
            ->willReturn([$this->attribute]);
        $this->attribute->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn(null);

        $this->assertEquals($expectedData, $this->model->getData());
    }
}
