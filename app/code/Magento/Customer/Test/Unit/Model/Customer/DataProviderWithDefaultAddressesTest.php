<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;

/**
 * Class DataProviderTest
 *
 * Test for class \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderWithDefaultAddressesTest extends \PHPUnit\Framework\TestCase
{
    const ATTRIBUTE_CODE = 'test-code';
    const OPTIONS_RESULT = 'test-options';

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerCollectionFactoryMock;

    /**
     * @var EavValidationRules|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavValidationRulesMock;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Customer\Model\FileProcessorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileProcessorFactory;

    /**
     * @var \Magento\Customer\Model\FileProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileProcessor;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactoryMock;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customerMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    private $customerCollectionMock;

    /**
     * @var \Magento\Customer\Model\Config\Share
     */
    private $shareConfigMock;

    /**
     * @var CountryWithWebsites
     */
    private $countryWithWebsitesMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->eavConfigMock = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerCollectionFactoryMock = $this->createPartialMock(
            \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory::class,
            ['create']
        );
        $this->eavValidationRulesMock = $this
            ->getMockBuilder(\Magento\Ui\DataProvider\EavValidationRules::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this
            ->getMockBuilder(\Magento\Framework\Session\SessionManagerInterface::class)
            ->setMethods(['getCustomerFormData', 'unsCustomerFormData'])
            ->getMockForAbstractClass();

        $this->fileProcessor = $this->getMockBuilder(\Magento\Customer\Model\FileProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileProcessorFactory = $this->getMockBuilder(\Magento\Customer\Model\FileProcessorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->countryFactoryMock = $this->getMockBuilder(\Magento\Directory\Model\CountryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'loadByCode', 'getName'])
            ->getMock();

        $this->customerMock = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerCollectionMock = $this->getMockBuilder(
            \Magento\Customer\Model\ResourceModel\Customer\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerCollectionMock->expects($this->any())->method('addAttributeToSelect')->with('*');
        $this->customerCollectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->customerCollectionMock);

        $this->eavConfigMock->expects($this->at(0))
            ->method('getEntityType')
            ->with('customer')
            ->willReturn($this->getTypeCustomerMock([]));
        $this->eavConfigMock->expects($this->at(1))
            ->method('getEntityType')
            ->with('customer_address')
            ->willReturn($this->getTypeAddressMock());

        $this->shareConfigMock = $this->getMockBuilder(\Magento\Customer\Model\Config\Share::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->countryWithWebsitesMock = $this->getMockBuilder(CountryWithWebsites::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllOptions'])
            ->getMock();
        $this->countryWithWebsitesMock->expects($this->any())->method('getAllOptions')->willReturn('test-options');

        $helper = new ObjectManager($this);
        $this->dataProvider = $helper->getObject(
            \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses::class,
            [
                'name'                      => 'test-name',
                'primaryFieldName'          => 'primary-field-name',
                'requestFieldName'          => 'request-field-name',
                'eavValidationRules'        => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->customerCollectionFactoryMock,
                'eavConfig'                 => $this->eavConfigMock,
                'countryFactory'            => $this->countryFactoryMock,
                'session'                   => $this->sessionMock,
                'fileProcessorFactory'      => $this->fileProcessorFactory,
                'shareConfig'               => $this->shareConfigMock,
                'countryWithWebsites'       => $this->countryWithWebsitesMock,
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
    public function testGetAttributesMetaWithOptions(array $expected)
    {
        $meta = $this->dataProvider->getMeta();
        $this->assertNotEmpty($meta);
        $this->assertEquals($expected, $meta);
    }

    /**
     * Data provider for testGetAttributesMeta
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
                                                'field' => 'website_ids'
                                            ]
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
     * @return CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCustomerCollectionFactoryMock()
    {
        $collectionMock = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Customer\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionMock->expects($this->any())
            ->method('addAttributeToSelect')
            ->with('*');

        $this->customerCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($collectionMock);

        return $this->customerCollectionFactoryMock;
    }

    /**
     * @return Config|\PHPUnit_Framework_MockObject_MockObject
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
     * @return Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTypeCustomerMock($customerAttributes = [])
    {
        $typeCustomerMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
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
     * @return Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTypeAddressMock()
    {
        $typeAddressMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();

        $typeAddressMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($this->getAttributeMock('address'));

        return $typeAddressMock;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $attributeMock
     * @param \PHPUnit_Framework_MockObject_MockObject $attributeBooleanMock
     * @param array $options
     */
    private function injectVisibilityProps(
        \PHPUnit_Framework_MockObject_MockObject $attributeMock,
        \PHPUnit_Framework_MockObject_MockObject $attributeBooleanMock,
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
     * @return AbstractAttribute[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    protected function getAttributeMock($type = 'customer', $options = [])
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
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
        $sourceMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attributeCode = self::ATTRIBUTE_CODE;
        if (isset($options[self::ATTRIBUTE_CODE]['specific_code_prefix'])) {
            $attributeCode .= $options[self::ATTRIBUTE_CODE]['specific_code_prefix'];
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

        $attributeBooleanMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
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
            $booleanAttributeCode .= $options['test-code-boolean']['specific_code_prefix'];
        }

        $attributeBooleanMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn($booleanAttributeCode);

        $this->eavValidationRulesMock->expects($this->any())
            ->method('build')
            ->willReturnMap([
                [$attributeMock, $this->logicalNot($this->isEmpty()), []],
                [$attributeBooleanMock, $this->logicalNot($this->isEmpty()), []],
            ]);
        $mocks = [$attributeMock, $attributeBooleanMock];
        $this->injectVisibilityProps($attributeMock, $attributeBooleanMock, $options);
        if ($type == "address") {
            $mocks[] = $this->getCountryAttrMock();
        }
        return $mocks;
    }

    /**
     * Callback for ::getDataUsingMethod
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCountryAttrMock()
    {
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [CountryWithWebsites::class, $this->countryWithWebsitesMock],
                [Share::class, $this->shareConfigMock],
            ]);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
        $countryAttrMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
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

        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)->disableOriginalConstructor()
            ->getMock();
        $this->customerCollectionMock->expects($this->once())->method('getItems')->willReturn([$this->customerMock]);
        $this->customerMock->expects($this->once())->method('getData')->willReturn($customerData);
        $this->customerMock->expects($this->once())->method('getAttributes')->willReturn([]);

        $this->customerMock->expects($this->once())->method('getDefaultBillingAddress')->willReturn($address);
        $this->countryFactoryMock->expects($this->once())->method('create')->willReturnSelf();
        $this->countryFactoryMock->expects($this->once())->method('loadByCode')->willReturnSelf();
        $this->countryFactoryMock->expects($this->once())->method('getName')->willReturn('Ukraine');

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
                    'default_billing_address' => [
                        'country' => 'Ukraine',
                    ],
                    'default_shipping_address' => []
                ]
            ],
            $this->dataProvider->getData()
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

        $this->customerCollectionMock->expects($this->once())->method('getItems')->willReturn([$this->customerMock]);
        $this->customerMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'email' => 'test@test.ua',
                'default_billing' => 2,
                'default_shipping' => 2,
            ]);
        $this->customerMock->expects($this->once())->method('getId')->willReturn($customerId);
        $this->customerMock->expects($this->once())->method('getAttributes')->willReturn([]);

        $this->sessionMock->expects($this->once())->method('getCustomerFormData')->willReturn($customerFormData);
        $this->sessionMock->expects($this->once())->method('unsCustomerFormData');

        $this->assertEquals([$customerId => $customerFormData], $this->dataProvider->getData());
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
        $viewUrl = 'viewUrl';
        $mime = 'image/png';

        $expectedData = [
            $customerId => [
                'customer' => [
                    'email' => $customerEmail,
                    'img1' => [
                        [
                            'file' => $filename,
                            'size' => 1,
                            'url' => $viewUrl,
                            'name' => 'filename.ext1',
                            'type' => $mime,
                        ],
                    ],
                ],
                'default_billing_address' => [],
                'default_shipping_address' => [],
            ],
        ];

        $attributeMock = $this->getMockBuilder(\Magento\Customer\Model\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->exactly(2))
            ->method('getFrontendInput')
            ->willReturn('image');
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('img1');

        $entityTypeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityTypeMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->willReturn(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $this->customerMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'email' => $customerEmail,
                'img1' => $filename,
            ]);
        $this->customerMock->expects($this->once())->method('getId')->willReturn($customerId);
        $this->customerMock->expects($this->once())->method('getAttributes')->willReturn([$attributeMock]);
        $this->customerMock->expects($this->once())->method('getEntityType')->willReturn($entityTypeMock);
        $this->customerCollectionMock->expects($this->any())->method('getItems')->willReturn([$this->customerMock]);
        $this->sessionMock->expects($this->once())->method('getCustomerFormData')->willReturn([]);
        $this->fileProcessorFactory->expects($this->any())
            ->method('create')
            ->with([
                'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            ])
            ->willReturn($this->fileProcessor);
        $this->fileProcessor->expects($this->once())->method('isExist')->with($filename)->willReturn(true);
        $this->fileProcessor->expects($this->once())->method('getStat')->with($filename)->willReturn(['size' => 1]);
        $this->fileProcessor->expects($this->once())->method('getViewUrl')
            ->with('/filename.ext1', 'image')
            ->willReturn($viewUrl);
        $this->fileProcessor->expects($this->once())->method('getMimeType')->with($filename)->willReturn($mime);

        $objectManager = new ObjectManager($this);

        $objectManager->setBackwardCompatibleProperty(
            $this->dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $this->assertEquals($expectedData, $this->dataProvider->getData());
    }

    public function testGetDataWithCustomAttributeImageNoData()
    {
        $customerId = 1;
        $customerEmail = 'user1@example.com';

        $expectedData = [
            $customerId => [
                'customer' => [
                    'email' => $customerEmail,
                    'img1' => [],
                ],
                'default_billing_address' => [],
                'default_shipping_address' => [],
            ],
        ];

        $attributeMock = $this->getMockBuilder(\Magento\Customer\Model\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('image');
        $attributeMock->expects($this->exactly(2))->method('getAttributeCode')
            ->willReturn('img1');

        $entityTypeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityTypeMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->willReturn(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $this->customerMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'email' => $customerEmail,
            ]);
        $this->customerMock->expects($this->once())->method('getId')->willReturn($customerId);
        $this->customerMock->expects($this->once())->method('getAttributes')->willReturn([$attributeMock]);
        $this->customerMock->expects($this->once())->method('getEntityType')->willReturn($entityTypeMock);
        $this->customerCollectionMock->expects($this->any())->method('getItems')->willReturn([$this->customerMock]);
        $this->sessionMock->expects($this->once())->method('getCustomerFormData')->willReturn([]);

        $this->assertEquals($expectedData, $this->dataProvider->getData());
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
                    'specific_code_prefix' => "_1"
                ],
                'test-code-boolean' => [
                    'visible' => true,
                    'is_used_in_forms' => ['customer_account_create'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_1"
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
                    'specific_code_prefix' => "_2"
                ],
                'test-code-boolean' => [
                    'visible' => true,
                    'is_used_in_forms' => ['customer_account_create'],
                    'user_defined' => true,
                    'specific_code_prefix' => "_2"
                ]
            ]
        );

        $helper = new ObjectManager($this);
        /** @var \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses $dataProvider */
        $dataProvider = $helper->getObject(
            \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses::class,
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->getCustomerCollectionFactoryMock(),
                'eavConfig' => $this->getEavConfigMock(array_merge($firstAttributesBundle, $secondAttributesBundle))
            ]
        );

        $helper->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $meta = $dataProvider->getMeta();
        $this->assertNotEmpty($meta);
        $this->assertEquals($this->getExpectationForVisibleAttributes(), $meta);
    }

    /**
     * Retrieve all customer variations of attributes with all variations of visibility
     *
     * @param bool $isRegistration
     * @return array
     */
    private function getCustomerAttributeExpectations($isRegistration)
    {
        return [
            self::ATTRIBUTE_CODE . "_1" => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'dataType' => 'frontend_input',
                            'formElement' => 'frontend_input',
                            'options' => 'test-options',
                            'visible' => !$isRegistration,
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
                            'visible' => $isRegistration,
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
                            'visible' => $isRegistration,
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
     * Retrieve all variations of attributes with all variations of visibility
     *
     * @param bool $isRegistration
     * @return  array
     */
    private function getExpectationForVisibleAttributes($isRegistration = true)
    {
        return [
            'customer' => [
                'children' => $this->getCustomerAttributeExpectations($isRegistration),
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
                                    'options' => null,
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
                                        'field' => 'website_ids'
                                    ]
                                ],
                            ],
                        ],
                    ]
                ],
            ],
        ];
    }
}
