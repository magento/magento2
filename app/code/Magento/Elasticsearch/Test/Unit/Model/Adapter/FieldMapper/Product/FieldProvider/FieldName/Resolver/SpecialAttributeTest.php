<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SpecialAttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\SpecialAttribute
     */
    private $resolver;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->resolver = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\SpecialAttribute::class
        );
    }

    /**
     * @dataProvider getFieldNameProvider
     * @param $attributeCode
     * @param $expected
     * @return void
     */
    public function testGetFieldName($attributeCode, $expected)
    {
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->assertEquals(
            $expected,
            $this->resolver->getFieldName($attributeMock)
        );
    }

    /**
     * @return array
     */
    public function getFieldNameProvider()
    {
        return [
            ['id', 'id'],
            ['sku', 'sku'],
            ['store_id', 'store_id'],
            ['visibility', 'visibility'],
            ['price', ''],
        ];
    }
}
