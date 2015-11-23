<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Elasticsearch\SearchAdapter\FieldMapperInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class FieldMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapper
     */
    protected $mapper;

    /**
     * @var \Magento\Eav\Model\Config|MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\Registry|MockObject
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $eavAttributeResource;

    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder('\Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getEntityType', 'getAttribute', 'getEntityAttributeCodes'])
            ->getMock();

        $this->fieldType = $this->getMockBuilder('\Magento\Elasticsearch\Model\Adapter\FieldType')
            ->disableOriginalConstructor()
            ->setMethods(['getFieldType'])
            ->getMock();

        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManagerInterface');

        $objectManager = new ObjectManagerHelper($this);

        $this->eavAttributeResource = $this->getMock(
            '\Magento\Catalog\Model\ResourceModel\Eav\Attribute',
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
            '\Magento\Elasticsearch\Model\Adapter\FieldMapper',
            [
                'eavConfig' => $this->eavConfig,
                'coreRegistry' => $this->coreRegistry,
                'storeManager' => $this->storeManager,
                'fieldType' => $this->fieldType
            ]
        );
    }

    /**
     * @dataProvider attributeCodeProvider
     * @param $attributeCode
     * @param $fieldName
     * @param array $context
     */
    public function testGetFieldName($attributeCode, $fieldName, $context = [])
    {
        $attribute = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Eav\Attribute')
            ->disableOriginalConstructor()
            ->getMock();

        $store = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->setMethods(['getId', '__wakeup'])->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $this->eavConfig->expects($this->any())->method('getAttribute')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode)
            ->willReturn($attribute);

        $this->assertEquals(
            $fieldName,
            $this->mapper->getFieldName($attributeCode, $context)
        );
    }

    /**
     * @dataProvider attributeTypesProvider
     * @return array
     */
    public function testGetAllAttributesTypes($backendType, $frontendType, $code)
    {
        $attributeMock = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Eav\Attribute')
            ->setMethods(['getBackendType', 'getFrontendInput'])
            ->disableOriginalConstructor()
            ->getMock();

        $store = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->setMethods(['getId', '__wakeup'])->disableOriginalConstructor()->getMock();
        $store->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->storeManager->expects($this->any())->method('getStore')->will($this->returnValue($store));
        $this->storeManager->expects($this->any())
            ->method('getStores')
            ->will($this->returnValue([$store]));

        $this->eavConfig->expects($this->any())->method('getEntityAttributeCodes')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->willReturn([$code]);

        $this->eavConfig->expects($this->any())->method('getAttribute')
            ->with(ProductAttributeInterface::ENTITY_TYPE_CODE, $code)
            ->willReturn($attributeMock);

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
            ['id', 'id'],
            ['price', 'price_22_66', ['customerGroupId' => '22', 'websiteId' => '66']],
            ['position', 'position_category_33', ['categoryId' => '33']],
            ['position', 'position_category_0'],
            ['test_code', 'test_code_1', ['type' => 'text']],
            ['test_code', 'test_code_2', ['type' => 'text', 'storeId'=>'2']],
        ];
    }

    /**
     * @return array
     */
    public static function attributeTypesProvider()
    {
        return [
            ['static', 'select', 'attr1'],
            ['static', 'text', 'attr1'],
            ['timestamp', 'select', 'attr1'],
            ['int', 'select', 'attr1'],
            ['decimal', 'select', 'attr1'],
            ['varchar', 'select', 'attr1'],
        ];
    }
}
