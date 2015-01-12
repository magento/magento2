<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection;

/**
 * Type processor Test
 */
class TypeProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Reflection\TypeProcessor
     */
    protected $_typeProcessor;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->_typeProcessor = new \Magento\Framework\Reflection\TypeProcessor();
    }

    /**
     * Test Retrieving of processed types data.
     */
    public function testGetTypesData()
    {
        $this->_typeProcessor->setTypeData('typeA', ['dataA']);
        $this->_typeProcessor->setTypeData('typeB', ['dataB']);
        $this->assertEquals(
            ['typeA' => ['dataA'], 'typeB' => ['dataB']],
            $this->_typeProcessor->getTypesData()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Data type "NonExistentType" is not declared.
     */
    public function testGetTypeDataInvalidArgumentException()
    {
        $this->_typeProcessor->getTypeData('NonExistentType');
    }

    /**
     * Test retrieval of data type details for the given type name.
     */
    public function testGetTypeData()
    {
        $this->_typeProcessor->setTypeData('typeA', ['dataA']);
        $this->assertEquals(['dataA'], $this->_typeProcessor->getTypeData('typeA'));
    }

    /**
     * Test data type details for the same type name set multiple times.
     */
    public function testSetTypeDataArrayMerge()
    {
        $this->_typeProcessor->setTypeData('typeA', ['dataA1']);
        $this->_typeProcessor->setTypeData('typeA', ['dataA2']);
        $this->_typeProcessor->setTypeData('typeA', ['dataA3']);
        $this->_typeProcessor->setTypeData('typeA', [null]);
        $this->assertEquals(['dataA1', 'dataA2', 'dataA3', null], $this->_typeProcessor->getTypeData('typeA'));
    }

    public function testNormalizeType()
    {
        $this->assertEquals('blah', $this->_typeProcessor->normalizeType('blah'));
        $this->assertEquals('string', $this->_typeProcessor->normalizeType('str'));
        $this->assertEquals('int', $this->_typeProcessor->normalizeType('integer'));
        $this->assertEquals('boolean', $this->_typeProcessor->normalizeType('bool'));
        $this->assertEquals('anyType', $this->_typeProcessor->normalizeType('mixed'));
    }

    public function testIsTypeSimple()
    {
        $this->assertTrue($this->_typeProcessor->isTypeSimple('string'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('string[]'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('int'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('float'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('double'));
        $this->assertTrue($this->_typeProcessor->isTypeSimple('boolean'));
        $this->assertFalse($this->_typeProcessor->isTypeSimple('blah'));
    }

    public function testIsTypeAny()
    {
        $this->assertTrue($this->_typeProcessor->isTypeAny('mixed'));
        $this->assertTrue($this->_typeProcessor->isTypeAny('mixed[]'));
        $this->assertFalse($this->_typeProcessor->isTypeAny('int'));
        $this->assertFalse($this->_typeProcessor->isTypeAny('int[]'));
    }

    public function testIsArrayType()
    {
        $this->assertFalse($this->_typeProcessor->isArrayType('string'));
        $this->assertTrue($this->_typeProcessor->isArrayType('string[]'));
    }

    public function getArrayItemType()
    {
        $this->assertEquals('string', $this->_typeProcessor->getArrayItemType('str[]'));
        $this->assertEquals('string', $this->_typeProcessor->getArrayItemType('string[]'));
        $this->assertEquals('integer', $this->_typeProcessor->getArrayItemType('int[]'));
        $this->assertEquals('boolean', $this->_typeProcessor->getArrayItemType('bool[]'));
        $this->assertEquals('any', $this->_typeProcessor->getArrayItemType('mixed[]'));
    }

    public function testTranslateTypeName()
    {
        $this->assertEquals(
            'TestModule1V1EntityItem',
            $this->_typeProcessor->translateTypeName('\Magento\TestModule1\Service\V1\Entity\Item')
        );
        $this->assertEquals(
            'TestModule3V1EntityParameter[]',
            $this->_typeProcessor->translateTypeName('\Magento\TestModule3\Service\V1\Entity\Parameter[]')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid parameter type "\Magento\TestModule3\V1\Parameter[]".
     */
    public function testTranslateTypeNameInvalidArgumentException()
    {
        $this->_typeProcessor->translateTypeName('\Magento\TestModule3\V1\Parameter[]');
    }

    public function testTranslateArrayTypeName()
    {
        $this->assertEquals('ArrayOfComplexType', $this->_typeProcessor->translateArrayTypeName('complexType'));
    }

    public function testProcessSimpleTypeIntToString()
    {
        $value = 1;
        $type = 'string';
        $this->assertSame('1', $this->_typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeStringToInt()
    {
        $value = '1';
        $type = 'int';
        $this->assertSame(1, $this->_typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeMixed()
    {
        $value = 1;
        $type = 'mixed';
        $this->assertSame(1, $this->_typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeIntArrayToStringArray()
    {
        $value = [1, 2, 3, 4, 5];
        $type = 'string[]';
        $this->assertSame(['1', '2', '3', '4', '5'], $this->_typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeStringArrayToIntArray()
    {
        $value = ['1', '2', '3', '4', '5'];
        $type = 'int[]';
        $this->assertSame([1, 2, 3, 4, 5], $this->_typeProcessor->processSimpleAndAnyType($value, $type));
    }

    /**
     * @expectedException \Magento\Framework\Exception\SerializationException
     * @expectedExceptionMessage Invalid type for value :"1". Expected Type: "int[]".
     */
    public function testProcessSimpleTypeInvalidType()
    {
        $value = 1;
        $type = 'int[]';
        $this->_typeProcessor->processSimpleAndAnyType($value, $type);
    }
}
