<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
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

    /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $eavAttributeResource;

    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder('\Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getEntityType', 'getAttribute', 'getEntityAttributeCodes'])
            ->getMock();

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

        $this->type = $objectManager->getObject(
            '\Magento\Elasticsearch\Model\Adapter\FieldType',
            [
                'eavConfig' => $this->eavConfig,
            ]
        );
    }

    /**
     * @dataProvider attributeTypesProvider
     * @return array
     */
    public function testGetFieldType($backendType, $frontendType)
    {
        $attributeMock = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Eav\Attribute')
            ->setMethods(['getBackendType', 'getFrontendInput'])
            ->disableOriginalConstructor()
            ->getMock();

        $attributeMock->expects($this->any())->method('getBackendType')
            ->will($this->returnValue($backendType));

        $attributeMock->expects($this->any())->method('getFrontendInput')
            ->will($this->returnValue($frontendType));

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
            ['static', 'select'],
            ['static', 'text'],
            ['timestamp', 'select'],
            ['int', 'select'],
            ['decimal', 'select'],
            ['varchar', 'select'],
        ];
    }
}
