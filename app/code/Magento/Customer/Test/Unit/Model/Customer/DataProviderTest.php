<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

/**
 * Class DataProviderTest
 *
 * Test for class \Magento\Customer\Model\Customer\DataProvider
 */
class DataProviderTest extends \PHPUnit_Framework_TestCase
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
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->eavConfigMock = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerCollectionFactoryMock = $this->getMock(
            'Magento\Customer\Model\ResourceModel\Customer\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->eavValidationRulesMock = $this
            ->getMockBuilder('Magento\Ui\DataProvider\EavValidationRules')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this
            ->getMockBuilder('Magento\Framework\Session\SessionManagerInterface')
            ->setMethods(['getCustomerFormData', 'unsCustomerFormData'])
            ->getMockForAbstractClass();

        $this->fileProcessor = $this->getMockBuilder('Magento\Customer\Model\FileProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileProcessorFactory = $this->getMockBuilder('Magento\Customer\Model\FileProcessorFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
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
        $helper = new ObjectManager($this);
        $dataProvider = $helper->getObject(
            '\Magento\Customer\Model\Customer\DataProvider',
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->getCustomerCollectionFactoryMock(),
                'eavConfig' => $this->getEavConfigMock()
            ]
        );

        $helper->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $meta = $dataProvider->getMeta();
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
                                            'visible' => 'is_visible',
                                            'required' => 'is_required',
                                            'label' => 'frontend_label',
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
                                            'visible' => 'is_visible',
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
                                            'visible' => 'is_visible',
                                            'required' => 'is_required',
                                            'label' => 'frontend_label',
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
                                            'visible' => 'is_visible',
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
        $collectionMock = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer\Collection')
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
     * @return Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEavConfigMock()
    {
        $this->eavConfigMock->expects($this->at(0))
            ->method('getEntityType')
            ->with('customer')
            ->willReturn($this->getTypeCustomerMock());
        $this->eavConfigMock->expects($this->at(1))
            ->method('getEntityType')
            ->with('customer_address')
            ->willReturn($this->getTypeAddressMock());

        return $this->eavConfigMock;
    }

    /**
     * @return Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTypeCustomerMock()
    {
        $typeCustomerMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();

        $typeCustomerMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($this->getAttributeMock());

        return $typeCustomerMock;
    }

    /**
     * @return Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTypeAddressMock()
    {
        $typeAddressMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();

        $typeAddressMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn($this->getAttributeMock());

        return $typeAddressMock;
    }

    /**
     * @return AbstractAttribute[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    protected function getAttributeMock()
    {
        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->setMethods(['getAttributeCode', 'getDataUsingMethod', 'usesSource', 'getSource'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sourceMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Source\AbstractSource')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $sourceMock->expects($this->any())
            ->method('getAllOptions')
            ->willReturn(self::OPTIONS_RESULT);

        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn(self::ATTRIBUTE_CODE);

        $attributeMock->expects($this->any())
            ->method('getDataUsingMethod')
            ->willReturnCallback(
                function ($origName) {
                    return $origName;
                }
            );
        $attributeMock->expects($this->any())
            ->method('usesSource')
            ->willReturn(true);
        $attributeMock->expects($this->any())
            ->method('getSource')
            ->willReturn($sourceMock);

        $attributeBooleanMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->setMethods(['getAttributeCode', 'getDataUsingMethod', 'usesSource', 'getFrontendInput'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $attributeBooleanMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('test-code-boolean');
        $attributeBooleanMock->expects($this->any())
            ->method('getFrontendInput')
            ->willReturn('boolean');
        $attributeBooleanMock->expects($this->any())
            ->method('getDataUsingMethod')
            ->willReturnCallback(
                function ($origName) {
                    return $origName;
                }
            );
        $attributeBooleanMock->expects($this->once())
            ->method('usesSource')
            ->willReturn(false);

        $this->eavValidationRulesMock->expects($this->any())
            ->method('build')
            ->willReturnMap([
                [$attributeMock, $this->logicalNot($this->isEmpty()), []],
                [$attributeBooleanMock, $this->logicalNot($this->isEmpty()), []],
            ]);

        return [$attributeMock, $attributeBooleanMock];
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetData()
    {
        $customer = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $address = $this->getMockBuilder('Magento\Customer\Model\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer\Collection')
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
            ->willReturn([
                'email' => 'test@test.ua',
                'default_billing' => 2,
                'default_shipping' => 2,
            ]);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customer->expects($this->once())
            ->method('getAttributes')
            ->willReturn([]);

        $address->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(2);
        $address->expects($this->once())
            ->method('load')
            ->with(2)
            ->willReturnSelf();
        $address->expects($this->once())
            ->method('getData')
            ->willReturn([
                'firstname' => 'firstname',
                'lastname' => 'lastname',
                'street' => "street\nstreet",
            ]);
        $address->expects($this->once())
            ->method('getAttributes')
            ->willReturn([]);

        $helper = new ObjectManager($this);
        $dataProvider = $helper->getObject(
            '\Magento\Customer\Model\Customer\DataProvider',
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->customerCollectionFactoryMock,
                'eavConfig' => $this->getEavConfigMock()
            ]
        );

        $reflection = new \ReflectionClass(get_class($dataProvider));
        $reflectionProperty = $reflection->getProperty('session');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($dataProvider, $this->sessionMock);

        $this->sessionMock->expects($this->once())
            ->method('getCustomerFormData')
            ->willReturn(null);

        $helper->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

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
                            'street' => [
                                'street',
                                'street',
                            ],
                            'default_billing' => 2,
                            'default_shipping' => 2,
                        ]
                    ]
                ]
            ],
            $dataProvider->getData()
        );
    }

    /**
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

        $customer = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $address = $this->getMockBuilder('Magento\Customer\Model\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer\Collection')
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
            ->willReturn([
                'email' => 'test@test.ua',
                'default_billing' => 2,
                'default_shipping' => 2,
            ]);
        $customer->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customer->expects($this->once())
            ->method('getAttributes')
            ->willReturn([]);

        $address->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(2);
        $address->expects($this->once())
            ->method('load')
            ->with(2)
            ->willReturnSelf();
        $address->expects($this->once())
            ->method('getData')
            ->willReturn([
                'firstname' => 'firstname',
                'lastname' => 'lastname',
                'street' => "street\nstreet",
            ]);
        $address->expects($this->once())
            ->method('getAttributes')
            ->willReturn([]);

        $helper = new ObjectManager($this);
        $dataProvider = $helper->getObject(
            '\Magento\Customer\Model\Customer\DataProvider',
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->customerCollectionFactoryMock,
                'eavConfig' => $this->getEavConfigMock()
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

        $helper->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $this->assertEquals([$customerId => $customerFormData], $dataProvider->getData());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetDataWithCustomAttributeImage()
    {
        $customerId = 1;
        $customerEmail = 'user1@example.com';

        $filename = '/filename.ext1';
        $viewUrl = 'viewUrl';

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
                        ],
                    ],
                ],
            ],
        ];

        $attributeMock = $this->getMockBuilder('Magento\Customer\Model\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->exactly(2))
            ->method('getFrontendInput')
            ->willReturn('image');
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('img1');

        $entityTypeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $entityTypeMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->willReturn(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $customerMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $customerMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'email' => $customerEmail,
                'img1' => $filename,
            ]);
        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);
        $customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $customerMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn([$attributeMock]);
        $customerMock->expects($this->once())
            ->method('getEntityType')
            ->willReturn($entityTypeMock);

        $collectionMock = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer\Collection')
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

        $this->fileProcessorFactory->expects($this->any())
            ->method('create')
            ->with([
                'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            ])
            ->willReturn($this->fileProcessor);

        $this->fileProcessor->expects($this->once())
            ->method('isExist')
            ->with($filename)
            ->willReturn(true);
        $this->fileProcessor->expects($this->once())
            ->method('getStat')
            ->with($filename)
            ->willReturn(['size' => 1]);
        $this->fileProcessor->expects($this->once())
            ->method('getViewUrl')
            ->with('/filename.ext1', 'image')
            ->willReturn($viewUrl);

        $objectManager = new ObjectManager($this);
        $dataProvider = $objectManager->getObject(
            '\Magento\Customer\Model\Customer\DataProvider',
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->customerCollectionFactoryMock,
                'eavConfig' => $this->getEavConfigMock()
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $dataProvider,
            'session',
            $this->sessionMock
        );

        $objectManager->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $this->assertEquals($expectedData, $dataProvider->getData());
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
            ],
        ];

        $attributeMock = $this->getMockBuilder('Magento\Customer\Model\Attribute')
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getFrontendInput')
            ->willReturn('image');
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('img1');

        $entityTypeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $entityTypeMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->willReturn(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $customerMock = $this->getMockBuilder('Magento\Customer\Model\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $customerMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'email' => $customerEmail,
            ]);
        $customerMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);
        $customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $customerMock->expects($this->once())
            ->method('getAttributes')
            ->willReturn([$attributeMock]);
        $customerMock->expects($this->once())
            ->method('getEntityType')
            ->willReturn($entityTypeMock);

        $collectionMock = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer\Collection')
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
            '\Magento\Customer\Model\Customer\DataProvider',
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->customerCollectionFactoryMock,
                'eavConfig' => $this->getEavConfigMock()
            ]
        );

        $objectManager->setBackwardCompatibleProperty(
            $dataProvider,
            'session',
            $this->sessionMock
        );

        $objectManager->setBackwardCompatibleProperty(
            $dataProvider,
            'fileProcessorFactory',
            $this->fileProcessorFactory
        );

        $this->assertEquals($expectedData, $dataProvider->getData());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetAttributesMetaWithCustomAttributeImage()
    {
        $maxFileSize = 1000;
        $allowedExtension = 'ext1 ext2';

        $attributeCode = 'img1';

        $collectionMock = $this->getMockBuilder('Magento\Customer\Model\ResourceModel\Customer\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('*');

        $this->customerCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->setMethods([
                'getAttributeCode',
                'getFrontendInput',
                'getDataUsingMethod',
            ])
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

        $typeCustomerMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $typeCustomerMock->expects($this->once())
            ->method('getAttributeCollection')
            ->willReturn([$attributeMock]);
        $typeCustomerMock->expects($this->once())
            ->method('getEntityTypeCode')
            ->willReturn(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);

        $typeAddressMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
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
            ->with($attributeMock, [
                'dataType' => 'frontend_input',
                'formElement' => 'frontend_input',
                'visible' => 'is_visible',
                'required' => 'is_required',
                'sortOrder' => 'sort_order',
                'notice' => 'note',
                'default' => 'default_value',
                'size' => 'multiline_count',
                'label' => __('frontend_label'),
            ])
            ->willReturn([
                'max_file_size' => $maxFileSize,
                'file_extensions' => 'ext1, eXt2 ', // Added spaces and upper-cases
            ]);

        $this->fileProcessorFactory->expects($this->any())
            ->method('create')
            ->with([
                'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            ])
            ->willReturn($this->fileProcessor);

        $objectManager = new ObjectManager($this);
        $dataProvider = $objectManager->getObject(
            '\Magento\Customer\Model\Customer\DataProvider',
            [
                'name' => 'test-name',
                'primaryFieldName' => 'primary-field-name',
                'requestFieldName' => 'request-field-name',
                'eavValidationRules' => $this->eavValidationRulesMock,
                'customerCollectionFactory' => $this->customerCollectionFactoryMock,
                'eavConfig' => $this->eavConfigMock,
                'fileProcessorFactory' => $this->fileProcessorFactory,
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
                                    'formElement' => 'fileUploader',
                                    'componentType' => 'fileUploader',
                                    'maxFileSize' => $maxFileSize,
                                    'allowedExtensions' => $allowedExtension,
                                    'uploaderConfig' => [
                                        'url' => 'customer/file/customer_upload',
                                    ],
                                    'sortOrder' => 'sort_order',
                                    'required' => 'is_required',
                                    'visible' => 'is_visible',
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
}
