<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// @codingStandardsIgnoreStart
namespace Magento\Framework\Reflection\Test\Unit;

use Laminas\Code\Reflection\ClassReflection;
use Magento\Framework\Exception\SerializationException;
use Magento\Framework\Reflection\Test\Unit\Fixture\TSample;
use Magento\Framework\Reflection\Test\Unit\Fixture\TSampleInterface;
use Magento\Framework\Reflection\Test\Unit\Fixture\UseClasses\SampleOne;
use Magento\Framework\Reflection\Test\Unit\Fixture\UseClasses\SampleOne\SampleThree;
use Magento\Framework\Reflection\Test\Unit\Fixture\UseClasses\SampleTwo;
use Magento\Framework\Reflection\Test\Unit\Fixture\UseClasses\SampleTwo\SampleFour;
use Magento\Framework\Reflection\Test\Unit\Fixture\UseSample;
use Magento\Framework\Reflection\TypeProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TypeProcessorTest extends TestCase
{
    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * Set up helper.
     */
    protected function setUp(): void
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

    public function testGetTypeDataInvalidArgumentException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The "NonExistentType" data type isn\'t declared. Verify the type and try again.');
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

    public function testTranslateTypeNameInvalidArgumentException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The "\Magento\TestModule3\V1\Parameter[]" parameter type is invalid. Verify the parameter and try again.');
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

    public function testProcessSimpleTypeInvalidType()
    {
        $this->expectException('Magento\Framework\Exception\SerializationException');
        $this->expectExceptionMessage('The "integer" value\'s type is invalid. The "int[]" type was expected. Verify and try again.');
        $value = 1;
        $type = 'int[]';
        $this->typeProcessor->processSimpleAndAnyType($value, $type);
    }

    public function testGetParamTypeWithIncorrectAnnotation()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessageMatches('/@param annotation is incorrect for the parameter "name" \w+/');
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
            ['method name' => 'addObjectList', 'type' => '\\' . TSampleInterface::class . '[]']
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
     */
    public function testGetReturnTypeWithoutReturnTag()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Method\'s return type must be specified using @return annotation. See Magento\Framework\Reflection\Test\Unit\Fixture\TSample::getName()');
        $classReflection = new ClassReflection(TSample::class);
        $methodReflection = $classReflection->getMethod('getName');
        $this->typeProcessor->getGetterReturnType($methodReflection);
    }

    /**
     * Checks a case when method return annotation has a null-type at first position,
     * and a valid type at second.
     */
    public function testGetReturnTypeNullAtFirstPos()
    {
        $expected = [
            'type' => 'string',
            'isRequired' => false,
            'description' => null,
            'parameterCount' => 0
        ];

        $classReflection = new ClassReflection(TSample::class);
        $methodReflection = $classReflection->getMethod('getWithNull');

        self::assertEquals($expected, $this->typeProcessor->getGetterReturnType($methodReflection));
    }

    /**
     * Simple and complex data provider
     *
     * @return array
     */
    public function simpleAndComplexDataProvider(): array
    {
        return [
            ['string', true],
            ['array', true],
            ['int', true],
            ['SomeClass', false],
            ['\\My\\Namespace\\Model\\Class', false],
            ['Some\\Other\\Class', false],
        ];
    }

    /**
     * Test simple type detection method
     *
     * @dataProvider simpleAndComplexDataProvider
     * @param string $type
     * @param bool $expectedValue
     */
    public function testIsSimpleType(string $type, bool $expectedValue)
    {
        self::assertEquals($expectedValue, $this->typeProcessor->isSimpleType($type));
    }

    /**
     * Simple and complex data provider
     *
     * @return array
     */
    public function basicClassNameProvider(): array
    {
        return [
            ['SomeClass[]', 'SomeClass'],
            ['\\My\\Namespace\\Model\\Class[]', '\\My\\Namespace\\Model\\Class'],
            ['Some\\Other\\Class[]', 'Some\\Other\\Class'],
            ['SomeClass', 'SomeClass'],
            ['\\My\\Namespace\\Model\\Class', '\\My\\Namespace\\Model\\Class'],
            ['Some\\Other\\Class', 'Some\\Other\\Class'],
        ];
    }

    /**
     * Extract basic class name
     *
     * @dataProvider basicClassNameProvider
     * @param string $type
     * @param string $expectedValue
     */
    public function testBasicClassName(string $type, string $expectedValue)
    {
        self::assertEquals($expectedValue, $this->typeProcessor->getBasicClassName($type));
    }

    /**
     * Fully qualified class names data provider
     *
     * @return array
     */
    public function isFullyQualifiedClassNamesDataProvider(): array
    {
        return [
            ['SomeClass', false],
            ['\\My\\Namespace\\Model\\Class', true],
            ['Some\\Other\\Class', false],
        ];
    }

    /**
     * Test fully qualified class name detector
     *
     * @dataProvider isFullyQualifiedClassNamesDataProvider
     * @param string $type
     * @param bool $expectedValue
     */
    public function testIsFullyQualifiedClassName(string $type, bool $expectedValue)
    {
        self::assertEquals($expectedValue, $this->typeProcessor->isFullyQualifiedClassName($type));
    }

    /**
     * Test alias mapping
     */
    public function testGetAliasMapping()
    {
        $sourceClass = new ClassReflection(UseSample::class);
        $aliasMap = $this->typeProcessor->getAliasMapping($sourceClass);

        self::assertEquals([
            'SampleOne' => SampleOne::class,
            'Sample2' => SampleTwo::class,
        ], $aliasMap);
    }

    /**
     * Resolve fully qualified class names data provider
     *
     * @return array
     */
    public function resolveFullyQualifiedClassNamesDataProvider(): array
    {
        return [
            [UseSample::class, 'string', 'string'],
            [UseSample::class, 'string[]', 'string[]'],

            [UseSample::class, 'SampleOne', '\\' . SampleOne::class],
            [UseSample::class, 'Sample2', '\\' . SampleTwo::class],
            [
                UseSample::class,
                '\\Magento\\Framework\\Reflection\\Test\\Unit\\Fixture\\UseClasses\\SampleOne',
                '\\' . SampleOne::class
            ],
            [
                UseSample::class,
                '\\Magento\\Framework\\Reflection\\Test\\Unit\\Fixture\\UseClasses\\SampleTwo',
                '\\' . SampleTwo::class
            ],
            [UseSample::class, 'UseClasses\\SampleOne', '\\' . SampleOne::class],
            [UseSample::class, 'UseClasses\\SampleTwo', '\\' . SampleTwo::class],

            [UseSample::class, 'SampleOne[]', '\\' . SampleOne::class . '[]'],
            [UseSample::class, 'Sample2[]', '\\' . SampleTwo::class . '[]'],
            [
                UseSample::class,
                '\\Magento\\Framework\\Reflection\\Test\\Unit\\Fixture\\UseClasses\\SampleOne[]',
                '\\' . SampleOne::class . '[]'
            ],
            [
                UseSample::class,
                '\\Magento\\Framework\\Reflection\\Test\\Unit\\Fixture\\UseClasses\\SampleTwo[]',
                '\\' . SampleTwo::class . '[]'
            ],
            [UseSample::class, 'UseClasses\\SampleOne[]', '\\' . SampleOne::class . '[]'],
            [UseSample::class, 'UseClasses\\SampleTwo[]', '\\' . SampleTwo::class . '[]'],

            [UseSample::class, 'SampleOne\SampleThree', '\\' . SampleThree::class],
            [UseSample::class, 'SampleOne\SampleThree[]', '\\' . SampleThree::class . '[]'],

            [UseSample::class, 'Sample2\SampleFour', '\\' . SampleFour::class],
            [UseSample::class, 'Sample2\SampleFour[]', '\\' . SampleFour::class . '[]'],

            [UseSample::class, 'Sample2\NotExisting', 'Sample2\NotExisting'],
            [UseSample::class, 'Sample2\NotExisting[]', 'Sample2\NotExisting[]'],

            [
                UseSample::class,
                '\\Magento\\Framework\\Reflection\\Test\\Unit\\Fixture\\UseClasses\\NotExisting',
                '\\Magento\\Framework\\Reflection\\Test\\Unit\\Fixture\\UseClasses\\NotExisting'
            ],
            [
                UseSample::class,
                '\\Magento\\Framework\\Reflection\\Test\\Unit\\Fixture\\UseClasses\\NotExisting[]',
                '\\Magento\\Framework\\Reflection\\Test\\Unit\\Fixture\\UseClasses\\NotExisting[]'
            ],
        ];
    }

    /**
     * Resolve fully qualified class names
     *
     * @dataProvider resolveFullyQualifiedClassNamesDataProvider
     * @param string $className
     * @param string $type
     * @param string $expectedValue
     * @throws \ReflectionException
     */
    public function testResolveFullyQualifiedClassNames(string $className, string $type, string $expectedValue)
    {
        $sourceClass = new ClassReflection($className);
        $fullyQualified = $this->typeProcessor->resolveFullyQualifiedClassName($sourceClass, $type);

        self::assertEquals($expectedValue, $fullyQualified);
    }
}
