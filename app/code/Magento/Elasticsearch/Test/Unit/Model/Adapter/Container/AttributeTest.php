<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Container;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Magento\Elasticsearch\Model\Adapter\Container\Attribute
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Container\Attribute
     */
    private $attribute;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->attribute = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\Container\Attribute::class,
            [
                'attributeCollection' => $this->collectionMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetAttributeCodeById()
    {
        $attributeId = 555;
        $attributeCode = 'test_attr_code1';
        $expected = 'test_attr_code1';
        $this->mockAttributeById($attributeId, $attributeCode);
        $result = $this->attribute->getAttributeCodeById($attributeId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    public function testGetOptionsAttributeCodeById()
    {
        $attributeId = 'options';
        $expected = 'options';
        $result = $this->attribute->getAttributeCodeById($attributeId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    public function testGetAttributeIdByCode()
    {
        $attributeId = 100;
        $attributeCode = 'test_attribute_code';
        $this->mockAttributeByCode($attributeId, $attributeCode);
        $result = $this->attribute->getAttributeIdByCode($attributeCode);
        $this->assertEquals($attributeId, $result);
    }

    /**
     * Test getAttributeIdByCode() method.
     */
    public function testGetOptionsAttributeIdByCode()
    {
        $attributeCode = 'options';
        $expected = 'options';
        $result = $this->attribute->getAttributeIdByCode($attributeCode);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return void
     */
    public function testGetMultipleAttributeIdsByCode()
    {
        $firstAttributeId = 100;
        $firstAttributeCode = 'test_attribute_code_100';
        $this->mockAttributeByCode($firstAttributeId, $firstAttributeCode, 0);
        $this->assertEquals($firstAttributeId, $this->attribute->getAttributeIdByCode($firstAttributeCode));

        $secondAttributeId = 200;
        $secondAttributeCode = 'test_attribute_code_200';
        $this->mockAttributeByCode($secondAttributeId, $secondAttributeCode, 0);
        $this->assertEquals($secondAttributeId, $this->attribute->getAttributeIdByCode($secondAttributeCode));
    }

    /**
     * @return void
     */
    public function testGetAttributeByIdTwice()
    {
        $attributeId = 555;
        $attributeCode = 'test_attr_code2';
        $expected = 'test_attr_code2';
        $this->mockAttributeById($attributeId, $attributeCode, 0);
        $this->assertEquals($expected, $this->attribute->getAttributeCodeById($attributeId));
        $this->assertEquals($expected, $this->attribute->getAttributeCodeById($attributeId));
    }

    /**
     * @return void
     */
    public function testGetAttributeByIdCachedInGetAttributeByCode()
    {
        $attributeId = 100;
        $attributeCode = 'test_attribute_code';
        $this->mockAttributeByCode($attributeId, $attributeCode);
        $this->assertEquals($attributeId, $this->attribute->getAttributeIdByCode($attributeCode));
        $this->assertEquals($attributeCode, $this->attribute->getAttributeCodeById($attributeId));
    }

    /**
     * @return void
     */
    public function testGetAttribute()
    {
        $attributeCode = 'attr_code_120';
        $attribute = $this->createAttributeMock(120, $attributeCode);
        $attributes = [
            $attribute
        ];
        $this->mockAttributes($attributes);
        $this->assertEquals($attribute, $this->attribute->getAttribute($attributeCode));
    }

    /**
     * @return void
     */
    public function testGetUnknownAttribute()
    {
        $attributeCode = 'attr_code_120';
        $attributes = [
            $this->createAttributeMock(120, 'attribute_code')
        ];
        $this->mockAttributes($attributes);
        $this->assertEquals(null, $this->attribute->getAttribute($attributeCode));
    }

    /**
     * @return void
     */
    public function testGetAttributes()
    {
        $attributes = [
            'attr_1_mock' => $this->createAttributeMock(1, 'attr_1_mock'),
            'attr_20_mock' => $this->createAttributeMock(20, 'attr_20_mock'),
            'attr_25_mock' => $this->createAttributeMock(25, 'attr_25_mock'),
            'attr_40_mock' => $this->createAttributeMock(40, 'attr_40_mock'),
            'attr_73_mock' => $this->createAttributeMock(73, 'attr_73_mock'),
            'attr_52_mock' => $this->createAttributeMock(52, 'attr_52_mock'),
            'attr_97_mock' => $this->createAttributeMock(97, 'attr_97_mock'),
        ];
        $this->mockAttributes($attributes);
        $this->assertEquals($attributes, $this->attribute->getAttributes());
    }

    /**
     * @param array $attributes
     * @return void
     */
    private function mockAttributes(array $attributes)
    {
        $this->collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($attributes));
    }

    /**
     * @param int $attributeId
     * @param string $attributeCode
     * @param int $sequence
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockAttributeById($attributeId, $attributeCode, $sequence = 0)
    {
        $attribute = $this->createAttributeMock($attributeId, $attributeCode);
        $this->collectionMock->expects($this->at($sequence))
            ->method('getItemById')
            ->with($attributeId)
            ->willReturn($attribute);
        return $attribute;
    }

    /**
     * @param int $attributeId
     * @param string $attributeCode
     * @param int $sequence
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockAttributeByCode($attributeId, $attributeCode, $sequence = 0)
    {
        $attribute = $this->createAttributeMock($attributeId, $attributeCode);
        $this->collectionMock->expects($this->at($sequence))
            ->method('getItemByColumnValue')
            ->with('attribute_code', $attributeCode)
            ->willReturn($attribute);
        return $attribute;
    }

    /**
     * @param int $attributeId
     * @param string $attributeCode
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createAttributeMock($attributeId, $attributeCode)
    {
        $attribute = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->setMethods(['getAttributeCode', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attribute->method('getId')
            ->willReturn($attributeId);
        return $attribute;
    }
}
