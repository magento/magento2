<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreStart
namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Exception\SerializationException;
use Magento\Framework\Reflection\Test\Unit\Fixture\TSample;
use Magento\Framework\Reflection\TypeProcessor;
use Zend\Code\Reflection\ClassReflection;

class TypeProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->typeProcessor = new TypeProcessor();
    }

    /**
     * Test Retrieving of processed types data.
     */
    public function testGetTypesData()
    {
        $this->typeProcessor->setTypeData('typeA', ['dataA']);
        $this->typeProcessor->setTypeData('typeB', ['dataB']);
        $this->assertEquals(
            ['typeA' => ['dataA'], 'typeB' => ['dataB']],
            $this->typeProcessor->getTypesData()
        );
    }

    /**
     * Test set of processed types data.
     */
    public function testSetTypesData()
    {
        $this->typeProcessor->setTypeData('typeC', ['dataC']);
        $this->assertEquals(['typeC' => ['dataC']], $this->typeProcessor->getTypesData());
        $typeData = ['typeA' => ['dataA'], 'typeB' => ['dataB']];
        $this->typeProcessor->setTypesData($typeData);
        $this->assertEquals($typeData, $this->typeProcessor->getTypesData());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Data type "NonExistentType" is not declared.
     */
    public function testGetTypeDataInvalidArgumentException()
    {
        $this->typeProcessor->getTypeData('NonExistentType');
    }

    /**
     * Test retrieval of data type details for the given type name.
     */
    public function testGetTypeData()
    {
        $this->typeProcessor->setTypeData('typeA', ['dataA']);
        $this->assertEquals(['dataA'], $this->typeProcessor->getTypeData('typeA'));
    }

    /**
     * Test data type details for the same type name set multiple times.
     */
    public function testSetTypeDataArrayMerge()
    {
        $this->typeProcessor->setTypeData('typeA', ['dataA1']);
        $this->typeProcessor->setTypeData('typeA', ['dataA2']);
        $this->typeProcessor->setTypeData('typeA', ['dataA3']);
        $this->typeProcessor->setTypeData('typeA', [null]);
        $this->assertEquals(
            ['dataA1', 'dataA2', 'dataA3', null],
            $this->typeProcessor->getTypeData('typeA')
        );
    }

    public function testNormalizeType()
    {
        $this->assertEquals('blah', $this->typeProcessor->normalizeType('blah'));
        $this->assertEquals('string', $this->typeProcessor->normalizeType('str'));
        $this->assertEquals('int', $this->typeProcessor->normalizeType('integer'));
        $this->assertEquals('boolean', $this->typeProcessor->normalizeType('bool'));
        $this->assertEquals('anyType', $this->typeProcessor->normalizeType('mixed'));
    }

    public function testIsTypeSimple()
    {
        $this->assertTrue($this->typeProcessor->isTypeSimple('string'));
        $this->assertTrue($this->typeProcessor->isTypeSimple('string[]'));
        $this->assertTrue($this->typeProcessor->isTypeSimple('int'));
        $this->assertTrue($this->typeProcessor->isTypeSimple('float'));
        $this->assertTrue($this->typeProcessor->isTypeSimple('double'));
        $this->assertTrue($this->typeProcessor->isTypeSimple('boolean'));
        $this->assertFalse($this->typeProcessor->isTypeSimple('blah'));
    }

    public function testIsTypeAny()
    {
        $this->assertTrue($this->typeProcessor->isTypeAny('mixed'));
        $this->assertTrue($this->typeProcessor->isTypeAny('mixed[]'));
        $this->assertFalse($this->typeProcessor->isTypeAny('int'));
        $this->assertFalse($this->typeProcessor->isTypeAny('int[]'));
    }

    public function testIsArrayType()
    {
        $this->assertFalse($this->typeProcessor->isArrayType('string'));
        $this->assertTrue($this->typeProcessor->isArrayType('string[]'));
    }

    public function testIsValidTypeDeclaration()
    {
        $this->assertTrue($this->typeProcessor->isValidTypeDeclaration('Traversable')); // Interface
        $this->assertTrue($this->typeProcessor->isValidTypeDeclaration('stdObj')); // Class
        $this->assertTrue($this->typeProcessor->isValidTypeDeclaration('array'));
        $this->assertTrue($this->typeProcessor->isValidTypeDeclaration('callable'));
        $this->assertTrue($this->typeProcessor->isValidTypeDeclaration('self'));
        $this->assertTrue($this->typeProcessor->isValidTypeDeclaration('self'));
        $this->assertFalse($this->typeProcessor->isValidTypeDeclaration('string'));
        $this->assertFalse($this->typeProcessor->isValidTypeDeclaration('string[]'));
        $this->assertFalse($this->typeProcessor->isValidTypeDeclaration('int'));
        $this->assertFalse($this->typeProcessor->isValidTypeDeclaration('float'));
        $this->assertFalse($this->typeProcessor->isValidTypeDeclaration('double'));
        $this->assertFalse($this->typeProcessor->isValidTypeDeclaration('boolean'));
        $this->assertFalse($this->typeProcessor->isValidTypeDeclaration('[]'));
        $this->assertFalse($this->typeProcessor->isValidTypeDeclaration('mixed[]'));
        $this->assertFalse($this->typeProcessor->isValidTypeDeclaration('stdObj[]'));
        $this->assertFalse($this->typeProcessor->isValidTypeDeclaration('Traversable[]'));
    }

    public function getArrayItemType()
    {
        $this->assertEquals('string', $this->typeProcessor->getArrayItemType('str[]'));
        $this->assertEquals('string', $this->typeProcessor->getArrayItemType('string[]'));
        $this->assertEquals('integer', $this->typeProcessor->getArrayItemType('int[]'));
        $this->assertEquals('boolean', $this->typeProcessor->getArrayItemType('bool[]'));
        $this->assertEquals('any', $this->typeProcessor->getArrayItemType('mixed[]'));
    }

    public function testTranslateTypeName()
    {
        $this->assertEquals(
            'TestModule1V1EntityItem',
            $this->typeProcessor->translateTypeName(\Magento\TestModule1\Service\V1\Entity\Item::class)
        );
        $this->assertEquals(
            'TestModule3V1EntityParameter[]',
            $this->typeProcessor->translateTypeName('\Magento\TestModule3\Service\V1\Entity\Parameter[]')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid parameter type "\Magento\TestModule3\V1\Parameter[]".
     */
    public function testTranslateTypeNameInvalidArgumentException()
    {
        $this->typeProcessor->translateTypeName('\Magento\TestModule3\V1\Parameter[]');
    }

    public function testTranslateArrayTypeName()
    {
        $this->assertEquals('ArrayOfComplexType', $this->typeProcessor->translateArrayTypeName('complexType'));
    }

    public function testProcessSimpleTypeIntToString()
    {
        $value = 1;
        $type = 'string';
        $this->assertSame('1', $this->typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeStringToInt()
    {
        $value = '1';
        $type = 'int';
        $this->assertSame(1, $this->typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeMixed()
    {
        $value = 1;
        $type = 'mixed';
        $this->assertSame(1, $this->typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeIntArrayToStringArray()
    {
        $value = [1, 2, 3, 4, 5];
        $type = 'string[]';
        $this->assertSame(['1', '2', '3', '4', '5'], $this->typeProcessor->processSimpleAndAnyType($value, $type));
    }

    public function testProcessSimpleTypeStringArrayToIntArray()
    {
        $value = ['1', '2', '3', '4', '5'];
        $type = 'int[]';
        $this->assertSame([1, 2, 3, 4, 5], $this->typeProcessor->processSimpleAndAnyType($value, $type));
    }

    /**
     * @dataProvider processSimpleTypeExceptionProvider
     */
    public function testProcessSimpleTypeException($value, $type)
    {
        $this->expectException(
            SerializationException::class,
            'Invalid type for value: "' . $value . '". Expected Type: "' . $type . '"'
        );
        $this->typeProcessor->processSimpleAndAnyType($value, $type);
    }

    /**
     * @return array
     */
    public static function processSimpleTypeExceptionProvider()
    {
        return [
            "int type, string value" => ['test', 'int'],
            "float type, string value" => ['test', 'float'],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\SerializationException
     * @expectedExceptionMessage Invalid type for value: "integer". Expected Type: "int[]".
     */
    public function testProcessSimpleTypeInvalidType()
    {
        $value = 1;
        $type = 'int[]';
        $this->typeProcessor->processSimpleAndAnyType($value, $type);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp /@param annotation is incorrect for the parameter "name" \w+/
     */
    public function testGetParamTypeWithIncorrectAnnotation()
    {
        $class = new ClassReflection(DataObject::class);
        $methodReflection = $class->getMethod('setName');
        $paramsReflection = $methodReflection->getParameters();
        $this->typeProcessor->getParamType($paramsReflection[0]);
    }

    /**
     * Checks a case for different array param types.
     *
     * @param string $methodName
     * @param string $type
     * @dataProvider arrayParamTypeDataProvider
     */
    public function testGetArrayParamType(string $methodName, string $type)
    {
        $class = new ClassReflection(DataObject::class);
        $methodReflection = $class->getMethod($methodName);
        $params = $methodReflection->getParameters();
        $this->assertEquals($type, $this->typeProcessor->getParamType(array_pop($params)));
    }

    /**
     * Get list of methods with expected param types.
     *
     * @return array
     */
    public function arrayParamTypeDataProvider()
    {
        return [
            ['method name' => 'addData', 'type' => 'array[]'],
            ['method name' => 'addObjectList', 'type' => 'TSampleInterface[]']
        ];
    }

    /**
     * Checks a case when method param has additional description.
     *
     * @param string $methodName
     * @param array $descriptions
     * @dataProvider methodParamsDataProvider
     */
    public function testGetParameterDescription(string $methodName, array $descriptions)
    {
        $class = new ClassReflection(DataObject::class);
        $methodReflection = $class->getMethod($methodName);
        $paramsReflection = $methodReflection->getParameters();
        foreach ($paramsReflection as $paramReflection) {
            $description = array_shift($descriptions);
            $this->assertEquals(
                $description,
                $this->typeProcessor->getParamDescription($paramReflection)
            );
        }
    }

    /**
     * Gets list of method names with params and their descriptions.
     *
     * @return array
     */
    public function methodParamsDataProvider()
    {
        return [
            ['method name' => 'setName', 'descriptions' => ['Name of the attribute']],
            ['method name' => 'setData', 'descriptions' => ['Key is used as index', null]],
        ];
    }

    public function testGetOperationName()
    {
        $this->assertEquals(
            "resNameMethodName",
            $this->typeProcessor->getOperationName("resName", "methodName")
        );
    }

    /**
     * Checks a case when method has only `@inheritdoc` annotation.
     */
    public function testGetReturnTypeWithInheritDocBlock()
    {
        $expected = [
            'type' => 'string',
            'isRequired' => true,
            'description' => null,
            'parameterCount' => 0
        ];

        $classReflection = new ClassReflection(TSample::class);
        $methodReflection = $classReflection->getMethod('getPropertyName');

        self::assertEquals($expected, $this->typeProcessor->getGetterReturnType($methodReflection));
    }

    /**
     * Checks a case when method and parent interface don't have `@return` annotation.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Method's return type must be specified using @return annotation. See Magento\Framework\Reflection\Test\Unit\Fixture\TSample::getName()
     */
    public function testGetReturnTypeWithoutReturnTag()
    {
        $classReflection = new ClassReflection(TSample::class);
        $methodReflection = $classReflection->getMethod('getName');
        $this->typeProcessor->getGetterReturnType($methodReflection);
    }
}
