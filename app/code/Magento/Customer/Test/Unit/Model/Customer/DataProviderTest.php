<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Customer\DataProvider as CustomerDataProvider;
use Magento\Customer\Model\FileProcessor;
use Magento\Customer\Model\FileUploaderDataResolver;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Customer\Model\Customer\DataProvider class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends TestCase
{
    const ATTRIBUTE_CODE = 'test-code';
    const OPTIONS_RESULT = 'test-options';

    /**
     * @var Config|MockObject
     */
    protected $eavConfigMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $customerCollectionFactoryMock;

    /**
     * @var EavValidationRules|MockObject
     */
    protected $eavValidationRulesMock;

    /**
     * @var SessionManagerInterface|MockObject
     */
    protected $sessionMock;

    /**
     * @var FileProcessor|MockObject
     */
    protected $fileProcessor;

    /**
     * @var FileUploaderDataResolver|MockObject
     */
    private $fileUploaderDataResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->eavConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerCollectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->eavValidationRulesMock = $this
            ->getMockBuilder(EavValidationRules::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this
            ->getMockBuilder(SessionManagerInterface::class)
            ->setMethods(['getCustomerFormData', 'unsCustomerFormData'])
            ->getMockForAbstractClass();

        $this->fileProcessor = $this->getMockBuilder(FileProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileUploaderDataResolver = $this->getMockBuilder(FileUploaderDataResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['overrideFileUploaderMetadata', 'overrideFileUploaderData'])
            ->getMock();
    }

    /**
     * Run test getAttributesMeta method.
     *
     * @param array $expected
     * @return void
     *
     * @dataProvider getAttributesMetaDataProvider
     */
    public function testGetAttributesMetaWithOptions(array $expected)
    {
        $helper = new ObjectManager($this);
        /** @var CustomerDataProvider $dataProvider */
        $dataProvider = $helper->getObject(
            CustomerDataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->getCustomerCollectionFactoryMock(),
                'eavConfig' => $this->getEavConfigMock(),
                'fileUploaderDataResolver' => $this->fileUploaderDataResolver,
            ]
        );

        $meta = $dataProvider->getMeta();
        $this->assertNotEmpty($meta);
        $this->assertEquals($expected, $meta);
    }

    /**
     * Data provider for testGetAttributesMeta.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getAttributesMetaDataProvider()
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
                    'address' => [
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
                                            'label' => 'frontend_label',
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
                            'country_id' => [
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
                                            'filterBy' => [
                                                'target' => '${ $.provider }:data.customer.website_id',
                                                '__disableTmpl' => ['target' => false],
                                                'field' => 'website_ids',
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        ],
                    ],
                ]
            ]
        ];
    }

    /**
     * @return CollectionFactory|MockObject
     */
    protected function getCustomerCollectionFactoryMock()
    {
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*');

        $this->customerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        return $this->customerCollectionFactoryMock;
    }

    /**
     * @return Config|MockObject
     */
    protected function getEavConfigMock($customerAttributes = [])
    {
        $this->eavConfigMock->expects($this->at(0))
            ->method('getEntityType')
            ->with('customer')
            ->willReturn($this->getTypeCustomerMock($customerAttributes));
        $this->eavConfigMock->expects($this->at(1))
            ->method('getEntityType')
            ->with('customer_address')
            ->willReturn($this->getTypeAddressMock());

        return $this->eavConfigMock;
    }

    /**
     * @return Type|MockObject
     */
    protected function getTypeCustomerMock($customerAttributes = [])
    {
        $typeCustomerMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributesCollection = !empty($customerAttributes) ? $customerAttributes : $this->getAttributeMock();
        $typeCustomerMock->expects($this->any())
            ->method('getEntityTypeCode')
            ->willReturn('customer');
        foreach ($attributesCollection as $attribute) {
            $attribute->expects($this->any())
                ->method('getEntityType')
                ->willReturn($typeCustomerMock);
        }

        $typeCustomerMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($attributesCollection);

        return $typeCustomerMock;
    }

    /**
     * @return Type|MockObject
     */
    protected function getTypeAddressMock()
    {
        $typeAddressMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();

        $typeAddressMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($this->getAttributeMock('address'));

        return $typeAddressMock;
    }

    /**
     * @param MockObject $attributeMock
     * @param MockObject $attributeBooleanMock
     * @param array $options
     */
    private function injectVisibilityProps(
        MockObject $attributeMock,
        MockObject $attributeBooleanMock,
        array $options = []
    ) {
        if (isset($options[self::ATTRIBUTE_CODE]['visible'])) {
            $attributeMock->expects($this->any())
                ->method('getIsVisible')
                ->willReturn($options[self::ATTRIBUTE_CODE]['visible']);
        }

        if (isset($options[self::ATTRIBUTE_CODE]['user_defined'])) {
            $attributeMock->expects($this->any())
                ->method('getIsUserDefined')
                ->willReturn($options[self::ATTRIBUTE_CODE]['user_defined']);
        }

        if (isset($options[self::ATTRIBUTE_CODE]['is_used_in_forms'])) {
            $attributeMock->expects($this->any())
                ->method('getUsedInForms')
                ->willReturn($options[self::ATTRIBUTE_CODE]['is_used_in_forms']);
        }

        if (isset($options['test-code-boolean']['visible'])) {
            $attributeBooleanMock->expects($this->any())
                ->method('getIsVisible')
                ->willReturn($options['test-code-boolean']['visible']);
        }

        if (isset($options['test-code-boolean']['user_defined'])) {
            $attributeBooleanMock->expects($this->any())
                ->method('getIsUserDefined')
                ->willReturn($options['test-code-boolean']['user_defined']);
        }

        if (isset($options['test-code-boolean']['is_used_in_forms'])) {
            $attributeBooleanMock->expects($this->any())
                ->method('getUsedInForms')
                ->willReturn($options['test-code-boolean']['is_used_in_forms']);
        }
    }

    /**
     * @return AbstractAttribute[]|MockObject[]
     */
    protected function getAttributeMock($type = 'customer', $options = [])
    {
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(
                [
                    'getAttributeCode',
                    'getDataUsingMethod',
                    'usesSource',
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
        $sourceMock = $this->getMockBuilder(AbstractSource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attributeCode = self::ATTRIBUTE_CODE;
        if (isset($options[self::ATTRIBUTE_CODE]['specific_code_prefix'])) {
            $attributeCode = $attributeCode . $options[self::ATTRIBUTE_CODE]['specific_code_prefix'];
        }

        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $sourceMock->expects($this->any())
            ->method('getAllOptions')
            ->willReturn(self::OPTIONS_RESULT);

        $attributeMock->expects($this->any())
            ->method('getDataUsingMethod')
            ->willReturnCallback($this->attributeGetUsingMethodCallback());

        $attributeMock->expects($this->any())
            ->method('usesSource')
            ->willReturn(true);
        $attributeMock->expects($this->any())
            ->method('getSource')
            ->willReturn($sourceMock);

        $attributeBooleanMock = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(
                [
                    'getAttributeCode',
                    'getDataUsingMethod',
                    'usesSource',
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

        $attributeBooleanMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn('boolean');
        $attributeBooleanMock->expects($this->any())
            ->method('getDataUsingMethod')
            ->willReturnCallback($this->attributeGetUsingMethodCallback());

        $attributeBooleanMock->expects($this->once())
            ->method('usesSource')
            ->willReturn(false);
        $booleanAttributeCode = 'test-code-boolean';
        if (isset($options['test-code-boolean']['specific_code_prefix'])) {
            $booleanAttributeCode = $booleanAttributeCode . $options['test-code-boolean']['specific_code_prefix'];
        }

        $attributeBooleanMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($booleanAttributeCode);

        $this->eavValidationRulesMock->expects($this->any())
            ->method('build')
            ->willReturnMap(
                [
                    [$attributeMock, $this->logicalNot($this->isEmpty()), []],
                    [$attributeBooleanMock, $this->logicalNot($this->isEmpty()), []],
                ]
            );
        $mocks = [$attributeMock, $attributeBooleanMock];
        $this->injectVisibilityProps($attributeMock, $attributeBooleanMock, $options);
        if ($type == "address") {
            $mocks[] = $this->getCountryAttrMock();
        }
        return $mocks;
    }

    /**
     * Callback for ::getDataUsingMethod.
     *
     * @return \Closure
     */
    private function attributeGetUsingMethodCallback()
    {
        return function ($origName) {
            return $origName;
        };
    }

    /**
     * @return MockObject
     */
    private function getCountryAttrMock()
    {
        $countryByWebsiteMock = $this->getMockBuilder(CountryWithWebsites::class)
            ->disableOriginalConstructor()
            ->getMock();
        $countryByWebsiteMock->expects($this->any())
            ->method('getAllOptions')
            ->willReturn('test-options');
        $shareMock = $this->getMockBuilder(Share::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [CountryWithWebsites::class, $countryByWebsiteMock],
                    [Share::class, $shareMock],
                ]
            );
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
        $countryAttrMock = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['getAttributeCode', 'getDataUsingMethod', 'usesSource', 'getSource', 'getLabel'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $countryAttrMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('country_id');

        $countryAttrMock->expects($this->any())
            ->method('getDataUsingMethod')
            ->willReturnCallback(
                function ($origName) {
                    return $origName;
                }
            );
        $countryAttrMock->expects($this->any())
            ->method('getLabel')
            ->willReturn(__('frontend_label'));
        $countryAttrMock->expects($this->any())
            ->method('usesSource')
            ->willReturn(true);
        $countryAttrMock->expects($this->any())
            ->method('getSource')
            ->willReturn(null);

        return $countryAttrMock;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetData()
    {
        $customerData = [
            'email' => 'test@test.ua',
            'default_billing' => 2,
            'default_shipping' => 2,
            'password_hash' => 'password_hash',
            'rp_token' => 'rp_token',
            'confirmation' => 'confirmation',
        ];
        $addressData = [
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'street' => "street\nstreet",
        ];

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*');

        $this->customerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$customer]);
        $customer->expects($this->once())
            ->method('getData')
            ->willReturn($customerData);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $address->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(2);
        $address->expects($this->once())
            ->method('load')
            ->with(2)
            ->willReturnSelf();
        $address->expects($this->once())
            ->method('getData')
            ->willReturn($addressData);

        $helper = new ObjectManager($this);
        $dataProvider = $helper->getObject(
            CustomerDataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->customerCollectionFactoryMock,
                'eavConfig' => $this->getEavConfigMock(),
                'fileUploaderDataResolver' => $this->fileUploaderDataResolver,
            ]
        );

        $reflection = new \ReflectionClass(get_class($dataProvider));
        $reflectionProperty = $reflection->getProperty('session');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($dataProvider, $this->sessionMock);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerFormData')
            ->willReturn(null);

        $this->assertEquals(
            [
                '' => [
                    'customer' => [
                        'email' => 'test@test.ua',
                        'default_billing' => 2,
                        'default_shipping' => 2,
                    ],
                    'address' => [
                        2 => [
                            'firstname' => 'firstname',
                            'lastname' => 'lastname',
                            // Won't be an array because it isn't defined as a multiline field in this test
                            'street' => "street\nstreet",
                            'default_billing' => 2,
                            'default_shipping' => 2,
                        ],
                    ],
                ],
            ],
            $dataProvider->getData()
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetDataWithCustomerFormData()
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

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*');

        $this->customerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$customer]);
        $customer->expects($this->once())
            ->method('getData')
            ->willReturn(
                [
                    'email' => 'test@test.ua',
                    'default_billing' => 2,
                    'default_shipping' => 2,
                ]
            );
        $customer->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $address->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(2);
        $address->expects($this->once())
            ->method('load')
            ->with(2)
            ->willReturnSelf();
        $address->expects($this->once())
            ->method('getData')
            ->willReturn(
                [
                    'firstname' => 'firstname',
                    'lastname' => 'lastname',
                    'street' => "street\nstreet",
                ]
            );
        $helper = new ObjectManager($this);
        $dataProvider = $helper->getObject(
            CustomerDataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->customerCollectionFactoryMock,
                'eavConfig' => $this->getEavConfigMock(),
                'fileUploaderDataResolver' => $this->fileUploaderDataResolver,
            ]
        );

        $reflection = new \ReflectionClass(get_class($dataProvider));
        $reflectionProperty = $reflection->getProperty('session');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($dataProvider, $this->sessionMock);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerFormData')
            ->willReturn($customerFormData);
        $this->sessionMock->expects($this->once())
            ->method('unsCustomerFormData');

        $this->assertEquals([$customerId => $customerFormData], $dataProvider->getData());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    public function testGetDataWithCustomAttributeImage()
    {
        $customerId = 1;
        $customerEmail = 'user1@example.com';

        $filename = '/filename.ext1';

        $customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerMock->expects($this->once())
            ->method('getData')
            ->willReturn(
                [
                    'email' => $customerEmail,
                    'img1' => $filename,
                ]
            );
        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);
        $customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$customerMock]);

        $this->customerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerFormData')
            ->willReturn([]);

        $objectManager = new ObjectManager($this);
        $dataProvider = $objectManager->getObject(
            CustomerDataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->customerCollectionFactoryMock,
                'eavConfig' => $this->getEavConfigMock(),
                'fileUploaderDataResolver' => $this->fileUploaderDataResolver,
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $dataProvider,
            'session',
            $this->sessionMock
        );

        $this->fileUploaderDataResolver->expects($this->atLeastOnce())->method('overrideFileUploaderData')
            ->with(
                $customerMock,
                [
                    'email' => $customerEmail,
                    'img1' => $filename,
                ]
            );
        $dataProvider->getData();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetAttributesMetaWithCustomAttributeImage()
    {
        $maxFileSize = 1000;
        $allowedExtension = 'ext1 ext2';

        $attributeCode = 'img1';

        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*');

        $this->customerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(
                [
                    'getAttributeCode',
                    'getFrontendInput',
                    'getDataUsingMethod',
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attributeMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn('image');
        $attributeMock->expects($this->any())
            ->method('getDataUsingMethod')
            ->willReturnCallback(
                function ($origName) {
                    return $origName;
                }
            );

        $typeCustomerMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeCustomerMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn([$attributeMock]);
        $typeCustomerMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->willReturn(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $typeAddressMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $typeAddressMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn([]);

        $this->eavConfigMock->expects($this->at(0))
            ->method('getEntityType')
            ->with('customer')
            ->willReturn($typeCustomerMock);
        $this->eavConfigMock->expects($this->at(1))
            ->method('getEntityType')
            ->with('customer_address')
            ->willReturn($typeAddressMock);

        $this->eavValidationRulesMock->expects($this->once())
            ->method('build')
            ->with(
                $attributeMock,
                [
                    'dataType' => 'frontend_input',
                    'formElement' => 'frontend_input',
                    'visible' => 'is_visible',
                    'required' => 'is_required',
                    'sortOrder' => 'sort_order',
                    'notice' => 'note',
                    'default' => 'default_value',
                    'size' => 'multiline_count',
                    'label' => __('frontend_label'),
                ]
            )
            ->willReturn(
                [
                    'max_file_size' => $maxFileSize,
                    'file_extensions' => 'ext1, eXt2 ', // Added spaces and upper-cases
                ]
            );

        $objectManager = new ObjectManager($this);
        $dataProvider = $objectManager->getObject(
            CustomerDataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->customerCollectionFactoryMock,
                'eavConfig' => $this->eavConfigMock,
            ]
        );

        $result = $dataProvider->getMeta();

        $this->assertNotEmpty($result);

        $expected = [
            'customer' => [
                'children' => [
                    $attributeCode => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'frontend_input',
                                    'formElement' => 'fileUploader',
                                    'componentType' => 'fileUploader',
                                    'maxFileSize' => $maxFileSize,
                                    'allowedExtensions' => $allowedExtension,
                                    'uploaderConfig' => [
                                        'url' => 'customer/file/customer_upload',
                                    ],
                                    'sortOrder' => 'sort_order',
                                    'required' => 'is_required',
                                    'visible' => null,
                                    'validation' => [
                                        'max_file_size' => $maxFileSize,
                                        'file_extensions' => 'ext1, eXt2 ',
                                    ],
                                    'label' => __('frontend_label'),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'address' => [
                'children' => [],
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    public function testGetDataWithVisibleAttributes()
    {
        $firstAttributesBundle = $this->getAttributeMock(
            'customer',
            [
                self::ATTRIBUTE_CODE => [
                    'visible' => true,
                    'is_used_in_forms' => ['customer_account_edit'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_1",
                ],
                'test-code-boolean' => [
                    'visible' => true,
                    'is_used_in_forms' => ['customer_account_create'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_1",
                ]
            ]
        );
        $secondAttributesBundle = $this->getAttributeMock(
            'customer',
            [
                self::ATTRIBUTE_CODE => [
                    'visible' => true,
                    'is_used_in_forms' => ['customer_account_create'],
                    'user_defined' => false,
                    'specific_code_prefix' => "_2",
                ],
                'test-code-boolean' => [
                    'visible' => true,
                    'is_used_in_forms' => ['customer_account_create'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_2",
                ]
            ]
        );

        $helper = new ObjectManager($this);
        /** @var DataProvider $dataProvider */
        $dataProvider = $helper->getObject(
            CustomerDataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->getCustomerCollectionFactoryMock(),
                'eavConfig' => $this->getEavConfigMock(array_merge($firstAttributesBundle, $secondAttributesBundle)),
                'fileUploaderDataResolver' => $this->fileUploaderDataResolver,
            ]
        );

        $meta = $dataProvider->getMeta();
        $this->assertNotEmpty($meta);
        $this->assertEquals($this->getExpectationForVisibleAttributes(), $meta);
    }

    /**
     * @return void
     */
    public function testGetDataWithVisibleAttributesWithAccountEdit()
    {
        $firstAttributesBundle = $this->getAttributeMock(
            'customer',
            [
                self::ATTRIBUTE_CODE => [
                    'visible' => true,
                    'is_used_in_forms' => ['customer_account_edit'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_1",
                ],
                'test-code-boolean' => [
                    'visible' => true,
                    'is_used_in_forms' => ['customer_account_create'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_1",
                ]
            ]
        );
        $secondAttributesBundle = $this->getAttributeMock(
            'customer',
            [
                self::ATTRIBUTE_CODE => [
                    'visible' => true,
                    'is_used_in_forms' => ['customer_account_create'],
                    'user_defined' => false,
                    'specific_code_prefix' => "_2",
                ],
                'test-code-boolean' => [
                    'visible' => true,
                    'is_used_in_forms' => ['customer_account_create'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_2",
                ]
            ]
        );

        $helper = new ObjectManager($this);
        $context = $this->getMockBuilder(ContextInterface::class)
            ->setMethods(['getRequestParam'])
            ->getMockForAbstractClass();
        $context->expects($this->any())
            ->method('getRequestParam')
            ->with('request-field-name')
            ->willReturn(1);
        /** @var DataProvider $dataProvider */
        $dataProvider = $helper->getObject(
            CustomerDataProvider::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->getCustomerCollectionFactoryMock(),
                'context' => $context,
                'eavConfig' => $this->getEavConfigMock(array_merge($firstAttributesBundle, $secondAttributesBundle)),
                'fileUploaderDataResolver' => $this->fileUploaderDataResolver,

            ]
        );

        $meta = $dataProvider->getMeta();
        $this->assertNotEmpty($meta);
        $this->assertEquals($this->getExpectationForVisibleAttributes(), $meta);
    }

    /**
     * Retrieve all customer variations of attributes with all variations of visibility.
     *
     * @return array
     */
    private function getCustomerAttributeExpectations()
    {
        return [
            self::ATTRIBUTE_CODE . "_1" => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => 'frontend_input',
                            'formElement' => 'frontend_input',
                            'options' => 'test-options',
                            'visible' => true,
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
            self::ATTRIBUTE_CODE . "_2" => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => 'frontend_input',
                            'formElement' => 'frontend_input',
                            'options' => 'test-options',
                            'visible' => true,
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
            'test-code-boolean_1' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => 'frontend_input',
                            'formElement' => 'frontend_input',
                            'visible' => true,
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
            'test-code-boolean_2' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => 'frontend_input',
                            'formElement' => 'frontend_input',
                            'visible' => true,
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
        ];
    }

    /**
     * Retrieve all variations of attributes with all variations of visibility.
     *
     * @return  array
     */
    private function getExpectationForVisibleAttributes()
    {
        return [
            'customer' => [
                'children' => $this->getCustomerAttributeExpectations(),
            ],
            'address' => [
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
                                    'label' => 'frontend_label',
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
                    'country_id' => [
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
                                    'filterBy' => [
                                        'target' => '${ $.provider }:data.customer.website_id',
                                        '__disableTmpl' => ['target' => false],
                                        'field' => 'website_ids',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
