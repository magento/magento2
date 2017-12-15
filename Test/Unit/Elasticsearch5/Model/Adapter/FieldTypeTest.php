<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Elasticsearch5\Model\Adapter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class FieldTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldType
     */
    protected $type;

    /**
     * @var \Magento\Eav\Model\Config|MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavAttributeResource;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityType', 'getAttribute', 'getEntityAttributeCodes'])
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);

        $this->eavAttributeResource = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
            [
                '__wakeup',
                'getBackendType',
                'getFrontendInput'
            ]
        );

        $this->type = $objectManager->getObject(
            \Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldType::class,
            [
                'eavConfig' => $this->eavConfig,
            ]
        );
    }

    /**
     * Test getFieldType() method.
     *
     * @dataProvider attributeTypesProvider
     * @param string $attributeCode
     * @param string $backendType
     * @param string $frontendType
     * @param string $expectedFieldType
     * @return void
     */
    public function testGetFieldType($attributeCode, $backendType, $frontendType, $expectedFieldType)
    {
        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->setMethods(['getBackendType', 'getFrontendInput', 'getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $attributeMock->expects($this->any())->method('getBackendType')
            ->will($this->returnValue($backendType));

        $attributeMock->expects($this->any())->method('getFrontendInput')
            ->will($this->returnValue($frontendType));

        $attributeMock->expects($this->any())->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));

        $this->assertEquals($expectedFieldType, $this->type->getFieldType($attributeMock));
    }

    /**
     * @return array
     */
    public static function attributeTypesProvider()
    {
        return [
            ['attr1', 'static', 'select', 'integer'],
            ['attr1', 'static', 'text', 'text'],
            ['attr1', 'timestamp', 'select', 'date'],
            ['attr1', 'datetime', 'text', 'date'],
            ['attr1', 'int', 'select', 'integer'],
            ['attr1', 'decimal', 'text', 'float'],
            ['attr1', 'varchar', 'select', 'text'],
            ['attr1', 'array', 'multiselect', 'text'],
            ['price', 'int', 'text', 'integer'],
            ['tier_price', 'int', 'text', 'integer'],
            ['tier_price', 'smallint', 'text', 'integer'],
        ];
    }
}
