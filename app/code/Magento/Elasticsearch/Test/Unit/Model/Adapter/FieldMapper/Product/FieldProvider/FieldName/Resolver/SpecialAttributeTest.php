<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\SpecialAttribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD)
 */
class SpecialAttributeTest extends TestCase
{
    /**
     * @var SpecialAttribute
     */
    private $resolver;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->resolver = $objectManager->getObject(
            SpecialAttribute::class
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
            ->onlyMethods(['getAttributeCode'])
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
    public static function getFieldNameProvider()
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
