<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Store\Api\Data\StoreInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class NotEavAttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\NotEavAttribute
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
            \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\NotEavAttribute::class
        );
    }

    /**
     * @dataProvider getFieldNameProvider
     * @param $attributeCode
     * @param $isEavAttribute
     * @param $context
     * @param $expected
     * @return void
     */
    public function testGetFieldName($attributeCode, $isEavAttribute, $context, $expected)
    {
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEavAttribute', 'getAttributeCode'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('isEavAttribute')
            ->willReturn($isEavAttribute);
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->assertEquals(
            $expected,
            $this->resolver->getFieldName($attributeMock, $context)
        );
    }

    /**
     * @return array
     */
    public function getFieldNameProvider()
    {
        return [
            ['code', true, [], ''],
            ['code', false, [], 'code'],
        ];
    }
}
