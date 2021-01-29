<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\Address;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\DataProvider;
use Magento\Customer\Model\AttributeMetadataResolver;
use Magento\Customer\Model\FileUploaderDataResolver;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Address\Collection as AddressCollection;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Model\Address as AddressModel;
use Magento\Ui\Component\Form\Element\Multiline;
use Magento\Ui\Component\Form\Field;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    private const ATTRIBUTE_CODE = 'street';

    /**
     * @var CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $addressCollectionFactory;

    /**
     * @var AddressCollection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collection;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerRepository;

    /**
     * @var CustomerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customer;

    /**
     * @var Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eavConfig;

    /*
     * @var ContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var AddressModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $address;

    /**
     * @var FileUploaderDataResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileUploaderDataResolver;

    /**
     * @var AttributeMetadataResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attributeMetadataResolver;

    /**
     * @var DataProvider
     */
    private $model;

    protected function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->fileUploaderDataResolver = $this->getMockBuilder(FileUploaderDataResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMetadataResolver = $this->getMockBuilder(AttributeMetadataResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collection = $this->getMockBuilder(AddressCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepository = $this->getMockForAbstractClass(CustomerRepositoryInterface::class);
        $this->context = $this->getMockForAbstractClass(ContextInterface::class);
        $this->addressCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->collection);
        $this->eavConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with('customer_address')
            ->willReturn($this->getTypeAddressMock([]));
        $this->customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->address = $this->getMockBuilder(AddressModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMetadataResolver->expects($this->at(0))
            ->method('getAttributesMeta')
            ->willReturn(
                [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => Multiline::NAME,
                                'formElement' => 'frontend_input',
                                'options' => 'test-options',
                                'visible' => null,
                                'required' => 'is_required',
                                'label' => __('Street'),
                                'sortOrder' => 'sort_order',
                                'default' => 'default_value',
                                'size' => 'multiline_count',
                                'componentType' => Field::NAME,
                            ],
                        ],
                    ],
                ]
            );
        $this->attributeMetadataResolver->expects($this->at(1))
            ->method('getAttributesMeta')
            ->willReturn(
                [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => 'frontend_input',
                                'formElement' => 'frontend_input',
                                'visible' => null,
                                'required' => 'is_required',
                                'label' => __('frontend_label'),
                                'sortOrder' => 'sort_order',
                                'default' => 'default_value',
                                'size' => 'multiline_count',
                                'componentType' => Field::NAME,
                                'prefer' => 'toggle',
                                'valueMap' => [
                                    'true' => 1,
                                    'false' => 0,
                                ],
                            ],
                        ],
                    ],
                ]
            );
        $this->model = $objectManagerHelper->getObject(
            DataProvider::class,
            [
                'name'                      => 'test-name',
                'primaryFieldName'          => 'primary-field-name',
                'requestFieldName'          => 'request-field-name',
                'addressCollectionFactory' => $this->addressCollectionFactory,
                'customerRepository' => $this->customerRepository,
                'eavConfig' => $this->eavConfig,
                'context' => $this->context,
                'fileUploaderDataResolver' => $this->fileUploaderDataResolver,
                'attributeMetadataResolver' => $this->attributeMetadataResolver,
                [],
                [],
                true
            ]
        );
    }

    public function testGetDefaultData(): void
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

    public function testGetData(): void
    {
        $expectedData = [
            '1' => [
                'parent_id' => '1',
                'default_billing' => '1',
                'default_shipping' => '1',
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
            ->willReturn('1');
        $this->address->expects($this->once())
            ->method('load')
            ->with('1')
            ->willReturnSelf();
        $this->address->expects($this->once())
            ->method('getData')
            ->willReturn([
                'parent_id' => '1',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'street' => "42000 Ave W 55 Cedar City\nApt. 33"
            ]);
        $this->fileUploaderDataResolver->expects($this->once())
            ->method('overrideFileUploaderData')
            ->willReturnSelf();

        $this->assertEquals($expectedData, $this->model->getData());
    }

    /**
     * Get customer address type mock
     *
     * @param array $customerAttributes
     * @return Type|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getTypeAddressMock($customerAttributes = [])
    {
        $typeAddressMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributesCollection = !empty($customerAttributes) ? $customerAttributes : $this->getAttributeMock();
        foreach ($attributesCollection as $attribute) {
            $attribute->expects($this->any())
                ->method('getEntityType')
                ->willReturn($typeAddressMock);
        }

        $typeAddressMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($attributesCollection);

        return $typeAddressMock;
    }

    /**
     * Get attribute mock
     *
     * @param array $options
     * @return AbstractAttribute[]|\PHPUnit\Framework\MockObject\MockObject[]
     */
    protected function getAttributeMock($options = []): array
    {
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(
                [
                    'getAttributeCode',
                    'getDataUsingMethod',
                    'getFrontendInput',
                    'getIsVisible',
                    'getSource',
                    'getIsUserDefined',
                    'getUsedInForms',
                    'getEntityType',
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attributeCode = self::ATTRIBUTE_CODE;
        if (isset($options[self::ATTRIBUTE_CODE]['specific_code_prefix'])) {
            $attributeCode .= $options[self::ATTRIBUTE_CODE]['specific_code_prefix'];
        }

        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $attributeBooleanMock = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(
                [
                    'getAttributeCode',
                    'getDataUsingMethod',
                    'getFrontendInput',
                    'getIsVisible',
                    'getIsUserDefined',
                    'getUsedInForms',
                    'getSource',
                    'getEntityType',
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $booleanAttributeCode = 'test-code-boolean';
        if (isset($options['test-code-boolean']['specific_code_prefix'])) {
            $booleanAttributeCode .= $options['test-code-boolean']['specific_code_prefix'];
        }

        $attributeBooleanMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($booleanAttributeCode);

        $mocks = [$attributeMock, $attributeBooleanMock];
        return $mocks;
    }
}
