<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class FieldTypeTest extends \PHPUnit_Framework_TestCase
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

        $this->type = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\FieldType::class,
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
     * @return array
     */
    public function testGetFieldType($attributeCode, $backendType, $frontendType)
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

        $this->assertInternalType(
            'string',
            $this->type->getFieldType($attributeMock)
        );
    }

    /**
     * @return array
     */
    public static function attributeTypesProvider()
    {
        return [
            ['attr1','static', 'select'],
            ['attr1','static', 'text'],
            ['attr1','timestamp', 'select'],
            ['attr1','int', 'select'],
            ['attr1','decimal', 'text'],
            ['attr1','varchar', 'select'],
            ['attr1','array', 'multiselect'],
            ['price','int', 'text'],
            ['tier_price','int', 'text'],
        ];
    }
}
