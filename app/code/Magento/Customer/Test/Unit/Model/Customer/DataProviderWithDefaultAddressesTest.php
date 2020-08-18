<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Customer;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\AttributeMetadataResolver;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses;
use Magento\Customer\Model\FileUploaderDataResolver;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\Component\Form\Field;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderWithDefaultAddressesTest extends TestCase
{
    private const ATTRIBUTE_CODE = 'test-code';

    /**
     * @var Config|MockObject
     */
    private $eavConfigMock;

    /**
     * @var CustomerCollectionFactory|MockObject
     */
    private $customerCollectionFactoryMock;

    /**
     * @var SessionManagerInterface|MockObject
     */
    private $sessionMock;

    /**
     * @var CountryFactory|MockObject
     */
    private $countryFactoryMock;

    /**
     * @var Customer|MockObject
     */
    private $customerMock;

    /**
     * @var CustomerCollection|MockObject
     */
    private $customerCollectionMock;

    /**
     * @var FileUploaderDataResolver|MockObject
     */
    private $fileUploaderDataResolver;

    /**
     * @var AttributeMetadataResolver|MockObject
     */
    private $attributeMetadataResolver;

    /**
     * @var DataProviderWithDefaultAddresses
     */
    private $dataProvider;

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->eavConfigMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->customerCollectionFactoryMock = $this->createPartialMock(CustomerCollectionFactory::class, ['create']);
        $this->sessionMock = $this->getMockBuilder(SessionManagerInterface::class)
            ->setMethods(['getCustomerFormData', 'unsCustomerFormData'])
            ->getMockForAbstractClass();
        $this->countryFactoryMock = $this->getMockBuilder(CountryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'loadByCode', 'getName'])
            ->getMock();
        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerCollectionMock = $this->getMockBuilder(CustomerCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerCollectionMock->expects($this->once())->method('addAttributeToSelect')->with('*');
        $this->customerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->customerCollectionMock);
        $this->eavConfigMock->expects($this->atLeastOnce())
            ->method('getEntityType')
            ->with('customer')
            ->willReturn($this->getTypeCustomerMock([]));
        $this->fileUploaderDataResolver = $this->getMockBuilder(FileUploaderDataResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMetadataResolver = $this->getMockBuilder(AttributeMetadataResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributesMeta'])
            ->getMock();
        $this->attributeMetadataResolver->expects($this->at(0))
            ->method('getAttributesMeta')
            ->willReturn(
                [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataType' => 'frontend_input',
                                'formElement' => 'frontend_input',
                                'options' => 'test-options',
                                'visible' => null,
                                'required' => 'is_required',
                                'label' => __('frontend_label'),
                                'sortOrder' => 'sort_order',
                                'notice' => 'note',
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
                                'notice' => 'note',
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

        $helper = new ObjectManager($this);
        $this->dataProvider = $helper->getObject(
            DataProviderWithDefaultAddresses::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'customerCollectionFactory' => $this->customerCollectionFactoryMock,
                'eavConfig' => $this->eavConfigMock,
                'countryFactory' => $this->countryFactoryMock,
                'session' => $this->sessionMock,
                'fileUploaderDataResolver' => $this->fileUploaderDataResolver,
                'attributeMetadataResolver' => $this->attributeMetadataResolver,
                'allowToShowHiddenAttributes' => true,
            ]
        );
    }

    /**
     * Run test getAttributesMeta method
     *
     * @param array $expected
     * @return void
     *
     * @dataProvider getAttributesMetaDataProvider
     */
    public function testGetAttributesMetaWithOptions(array $expected): void
    {
        $meta = $this->dataProvider->getMeta();
        $this->assertNotEmpty($meta);
        $this->assertEquals($expected, $meta);
    }

    /**
     * Data provider for testGetAttributesMeta
     *
     * @return array
     */
    public function getAttributesMetaDataProvider(): array
    {
        return [
            [
                'expected' => [
                    'customer' => [
                        'children' => [
                            self::ATTRIBUTE_CODE => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => 'frontend_input',
                                            'formElement' => 'frontend_input',
                                            'options' => 'test-options',
                                            'visible' => null,
                                            'required' => 'is_required',
                                            'label' => __('frontend_label'),
                                            'sortOrder' => 'sort_order',
                                            'notice' => 'note',
                                            'default' => 'default_value',
                                            'size' => 'multiline_count',
                                            'componentType' => Field::NAME,
                                        ],
                                    ],
                                ],
                            ],
                            'test-code-boolean' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => 'frontend_input',
                                            'formElement' => 'frontend_input',
                                            'visible' => null,
                                            'required' => 'is_required',
                                            'label' => __('frontend_label'),
                                            'sortOrder' => 'sort_order',
                                            'notice' => 'note',
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
                            ],
                        ],
                    ],
                ]
            ]
        ];
    }

    /**
     * @param array $customerAttributes
     * @return Type|MockObject
     */
    protected function getTypeCustomerMock($customerAttributes = [])
    {
        $typeCustomerMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributesCollection = !empty($customerAttributes) ? $customerAttributes : $this->getAttributeMock();
        foreach ($attributesCollection as $attribute) {
            $attribute->expects($this->any())
                ->method('getEntityType')
                ->willReturn($typeCustomerMock);
        }

        $typeCustomerMock->expects($this->atLeastOnce())
            ->method('getAttributeCollection')
            ->willReturn($attributesCollection);

        return $typeCustomerMock;
    }

    /**
     * @param array $options
     * @return AbstractAttribute[]|MockObject[]
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

        $attributeMock->expects($this->once())
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

        $attributeBooleanMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($booleanAttributeCode);

        $mocks = [$attributeMock, $attributeBooleanMock];
        return $mocks;
    }

    /**
     * @return void
     */
    public function testGetData(): void
    {
        $customerId = 1;
        $customerData = [
            'email' => 'test@test.ua',
            'default_billing' => 2,
            'default_shipping' => 2,
            'password_hash' => 'password_hash',
            'rp_token' => 'rp_token',
        ];
        $addressData = [
            'country_id' => 'code',
            'entity_id' => 2,
            'parent_id' => $customerId,
            'street' => "line 1\nline 2",
            'region' => 'Region Name',
        ];
        $localeRegionName = 'Locale Region Name';

        $address = $this->createMock(Address::class);
        $address->method('getData')->willReturn($addressData);
        $address->method('getRegion')->willReturn($localeRegionName);

        $this->customerCollectionMock->method('getItems')->willReturn([$this->customerMock]);
        $this->customerMock->method('getDefaultBillingAddress')->willReturn($address);
        $this->customerMock->method('getDefaultShippingAddress')->willReturn(false);
        $this->customerMock->method('getData')->willReturn($customerData);
        $this->customerMock->method('getId')->willReturn($customerId);

        $this->countryFactoryMock->expects($this->once())
            ->method('create')
            ->willReturnSelf();
        $this->countryFactoryMock->expects($this->once())
            ->method('loadByCode')
            ->with('code')
            ->willReturnSelf();
        $this->countryFactoryMock->expects($this->once())
            ->method('getName')
            ->willReturn('Ukraine');

        $this->sessionMock->expects($this->once())
            ->method('getCustomerFormData')
            ->willReturn(null);

        $this->assertEquals(
            [
                $customerId => [
                    'customer' => [
                        'email' => 'test@test.ua',
                        'default_billing' => 2,
                        'default_shipping' => 2,
                    ],
                    'default_billing_address' => [
                        'country' => 'Ukraine',
                        'country_id' => 'code',
                        'entity_id' => 2,
                        'parent_id' => $customerId,
                        'street' => ['line 1', 'line 2'],
                        'region' => $localeRegionName,
                    ],
                    'default_shipping_address' => [],
                    'customer_id' => $customerId
                ]
            ],
            $this->dataProvider->getData()
        );
    }

    /**
     * @return void
     */
    public function testGetDataWithCustomerFormData(): void
    {
        $customerId = 11;
        $customerFormData = [
            'customer' => [
                'email' => 'test1@test1.ua',
                'default_billing' => 3,
                'default_shipping' => 3,
                'entity_id' => $customerId,
            ],
            'address' => [
                3 => [
                    'firstname' => 'firstname1',
                    'lastname' => 'lastname1',
                    'street' => [
                        'street1',
                        'street2',
                    ],
                    'default_billing' => 3,
                    'default_shipping' => 3,
                ],
            ],
        ];

        $this->customerCollectionMock->expects($this->once())->method('getItems')->willReturn([$this->customerMock]);
        $this->customerMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'email' => 'test@test.ua',
                'default_billing' => 2,
                'default_shipping' => 2,
            ]);
        $this->customerMock->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);

        $this->sessionMock->expects($this->once())->method('getCustomerFormData')->willReturn($customerFormData);
        $this->sessionMock->expects($this->once())->method('unsCustomerFormData');

        $this->assertEquals([$customerId => $customerFormData], $this->dataProvider->getData());
    }
}
