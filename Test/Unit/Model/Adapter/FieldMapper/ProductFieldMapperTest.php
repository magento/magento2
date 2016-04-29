<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;

class ProductFieldMapperTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Elasticsearch\Model\Adapter\FieldType|\PHPUnit_Framework_MockObject_MockObject
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

        $this->fieldType = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\FieldType::class)
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

        $this->coreRegistry = $this->getMock(\Magento\Framework\Registry::class);

        $objectManager = new ObjectManagerHelper($this);

        $this->eavAttributeResource = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            [
                '__wakeup',
                'getBackendType',
                'getFrontendInput'
            ],
            [],
            '',
            false
        );

        $this->mapper = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\FieldMapper\ProductFieldMapper::class,
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
     *
     * @return void
     */
    public function testGetAllAttributesTypes($attributeCode)
    {
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->setMethods(['getBackendType', 'getFrontendInput'])
            ->disableOriginalConstructor()
            ->getMock();

        $store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getId', '__wakeup'])->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->storeManager->expects($this->any())
            ->method('getStores')
            ->will($this->returnValue([$store]));

        $this->eavConfig->expects($this->any())->method('getEntityAttributeCodes')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->willReturn([$attributeCode]);

        $this->eavConfig->expects($this->any())->method('getAttribute')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($attributeMock);

        $attributeMock->expects($this->any())->method('getFrontendInput')
            ->will($this->returnValue('select'));

        $this->eavAttributeResource->expects($this->any())
            ->method('getIsFilterable')
            ->willReturn(true);
        $this->eavAttributeResource->expects($this->any())
            ->method('getIsVisibleInAdvancedSearch')
            ->willReturn(true);
        $this->eavAttributeResource->expects($this->any())
            ->method('getIsFilterableInSearch')
            ->willReturn(false);
        $this->eavAttributeResource->expects($this->any())
            ->method('getIsGlobal')
            ->willReturn(false);
        $this->eavAttributeResource->expects($this->any())
            ->method('getIsGlobal')
            ->willReturn(true);

        $this->assertInternalType(
            'array',
            $this->mapper->getAllAttributesTypes()
        );
    }

    /**
     * @return array
     */
    public static function attributeCodeProvider()
    {
        return [
            ['id', 'id', 'string'],
            ['status', 'status', 'string'],
            ['status', 'status', 'string', ['type'=>'default']],
            ['price', 'price_0_1', 'string', ['type'=>'default']],
            ['position', 'position_category_1', 'string', ['type'=>'default']],
            ['price', 'price_2_3', 'string', ['type'=>'default', 'customerGroupId'=>'2', 'websiteId'=>'3']],
            ['position', 'position_category_3', 'string', ['type'=>'default', 'categoryId'=>'3']],
            ['color', 'color', 'select', ['type'=>'default']],
            ['description', 'sort_description', 'string', ['type'=>'some']],
            ['*', '_all', 'string', ['type'=>'text']],
            ['description', 'description', 'string', ['type'=>'text']],
        ];
    }

    /**
     * @return array
     */
    public static function attributeProvider()
    {
        return [
            ['category_ids'],
            ['attr_code'],
        ];
    }
}
