<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Elasticsearch5\Model\Adapter\FieldMapper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldType;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ProductFieldMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapper\ProductFieldMapper
     */
    protected $mapper;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavAttributeResource;

    /**
     * @var FieldType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldType;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityType', 'getAttribute', 'getEntityAttributeCodes'])
            ->getMock();

        $this->fieldType = $this->getMockBuilder(FieldType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldType'])
            ->getMock();

        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerGroupId'])
            ->getMock();

        $this->storeManager = $this->storeManager = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false
        );

        $this->store = $this->getMockForAbstractClass(
            \Magento\Store\Api\Data\StoreInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getWebsiteId', 'getRootCategoryId']
        );

        $this->coreRegistry = $this->createMock(\Magento\Framework\Registry::class);

        $objectManager = new ObjectManagerHelper($this);

        $this->eavAttributeResource = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            [
                '__wakeup',
                'getBackendType',
                'getFrontendInput'
            ]
        );

        $this->mapper = $objectManager->getObject(
            \Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapper::class,
            [
                'eavConfig' => $this->eavConfig,
                'storeManager' => $this->storeManager,
                'fieldType' => $this->fieldType,
                'customerSession' => $this->customerSession,
                'coreRegistry' => $this->coreRegistry
            ]
        );
    }

    /**
     * @dataProvider attributeCodeProvider
     * @param string $attributeCode
     * @param string $fieldName
     * @param string $fieldType
     * @param array $context
     *
     * @return void
     */
    public function testGetFieldName($attributeCode, $fieldName, $fieldType, $context = [])
    {
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->setMethods(['getBackendType', 'getFrontendInput', 'getAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSession->expects($this->any())
            ->method('getCustomerGroupId')
            ->willReturn('0');

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn('1');
        $this->store->expects($this->any())
            ->method('getRootCategoryId')
            ->willReturn('1');

        $this->eavConfig->expects($this->any())->method('getAttribute')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($attributeMock);

        $attributeMock->expects($this->any())->method('getFrontendInput')
            ->will($this->returnValue('select'));

        $this->fieldType->expects($this->any())->method('getFieldType')
            ->with($attributeMock)
            ->willReturn($fieldType);

        $this->assertEquals(
            $fieldName,
            $this->mapper->getFieldName($attributeCode, $context)
        );
    }

    /**
     * @return void
     */
    public function testGetFieldNameWithoutAttribute()
    {
        $this->eavConfig->expects($this->any())->method('getAttribute')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, 'attr1')
            ->willReturn('');

        $this->assertEquals(
            'attr1',
            $this->mapper->getFieldName('attr1', [])
        );
    }

    /**
     * @dataProvider attributeProvider
     * @param string $attributeCode
     * @param string $inputType
     * @param array $searchAttributes
     * @param array $expected
     * @return void
     */
    public function testGetAllAttributesTypes($attributeCode, $inputType, $searchAttributes, $expected)
    {
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavConfig->expects($this->any())->method('getEntityAttributeCodes')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->willReturn([$attributeCode]);

        $this->eavConfig->expects($this->any())->method('getAttribute')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($attributeMock);

        $this->fieldType->expects($this->once())->method('getFieldType')->willReturn(FieldType::ES_DATA_TYPE_INT);

        $attributeMock->expects($this->any())
            ->method('getIsSearchable')
            ->willReturn($searchAttributes['searchable']);
        $attributeMock->expects($this->any())
            ->method('getIsFilterable')
            ->willReturn($searchAttributes['filterable']);
        $attributeMock->expects($this->any())
            ->method('getIsFilterableInSearch')
            ->willReturn($searchAttributes['filterableInSearch']);
        $attributeMock->expects($this->any())
            ->method('getIsVisibleInAdvancedSearch')
            ->willReturn($searchAttributes['advSearch']);

        $attributeMock->expects($this->any())->method('getFrontendInput')
            ->will($this->returnValue($inputType));

        $this->assertEquals(
            $expected,
            $this->mapper->getAllAttributesTypes()
        );
    }

    /**
     * @return array
     */
    public function attributeCodeProvider()
    {
        return [
            ['id', 'id', 'text'],
            ['status', 'status', 'text'],
            ['status', 'status_value', 'text', ['type'=>'default']],
            ['price', 'price_0_1', 'text', ['type'=>'default']],
            ['position', 'position_category_1', 'text', ['type'=>'default']],
            ['price', 'price_2_3', 'text', ['type'=>'default', 'customerGroupId'=>'2', 'websiteId'=>'3']],
            ['position', 'position_category_3', 'text', ['type'=>'default', 'categoryId'=>'3']],
            ['color', 'color_value', 'text', ['type'=>'text']],
            ['description', 'sort_description', 'text', ['type'=>'some']],
            ['*', '_all', 'text', ['type'=>'text']],
            ['description', 'description_value', 'text', ['type'=>'text']],
        ];
    }

    /**
     * @return array
     */
    public function attributeProvider()
    {
        return [
            [
                'category_ids',
                'select',
                ['searchable' => false, 'filterable' => false, 'filterableInSearch' => false, 'advSearch' => false],
                ['category_ids' => ['type' => 'keyword'], 'category_ids_value' => ['type' => 'text']]
            ],
            [
                'attr_code',
                'text',
                ['searchable' => false, 'filterable' => false, 'filterableInSearch' => false, 'advSearch' => false],
                ['attr_code' => ['type' => 'integer']]
            ],
            [
                'attr_code',
                'text',
                ['searchable' => '0', 'filterable' => '0', 'filterableInSearch' => '0', 'advSearch' => '0'],
                ['attr_code' => ['type' => 'integer']]
            ],
            [
                'attr_code',
                'text',
                ['searchable' => true, 'filterable' => false, 'filterableInSearch' => false, 'advSearch' => false],
                ['attr_code' => ['type' => 'integer']]
            ],
            [
                'attr_code',
                'text',
                ['searchable' => '1', 'filterable' => '0', 'filterableInSearch' => '0', 'advSearch' => '0'],
                ['attr_code' => ['type' => 'integer']]
            ],
            [
                'attr_code',
                'text',
                ['searchable' => false, 'filterable' => false, 'filterableInSearch' => false, 'advSearch' => true],
                ['attr_code' => ['type' => 'integer']]
            ],
            [
                'attr_code',
                'text',
                ['searchable' => '0', 'filterable' => '0', 'filterableInSearch' => '1', 'advSearch' => '0'],
                ['attr_code' => ['type' => 'integer']]
            ],
        ];
    }
}
