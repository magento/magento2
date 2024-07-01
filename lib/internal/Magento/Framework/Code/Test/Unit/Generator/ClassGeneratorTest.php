<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Generator;

use Laminas\Code\Generator\AbstractMemberGenerator;
use Laminas\Code\Generator\DocBlock\Tag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\ValueGenerator;
use Magento\Framework\Code\Generator\ClassGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Framework\Code\Generator\ClassGenerator
 */
class ClassGeneratorTest extends TestCase
{
    /**#@+
     * Possible flags for assertion
     */
    const FLAG_CONST = 'const';

    const FLAG_STATIC = 'static';

    const FLAG_FINAL = 'final';

    const FLAG_ABSTRACT = 'abstract';

    const FLAG_REFERENCE = 'passedByReference';

    const FLAG_VARIADIC = 'variadic';

    /**#@-*/
    /**
     * @var ClassGenerator
     */
    protected $_model;

    /**
     * Methods to verify flags
     *
     * @var array
     */
    protected $_flagVerification = [
        self::FLAG_CONST => 'isConst',
        self::FLAG_STATIC => 'isStatic',
        self::FLAG_FINAL => 'isFinal',
        self::FLAG_ABSTRACT => 'isAbstract',
        self::FLAG_REFERENCE => 'getPassedByReference',
        self::FLAG_VARIADIC => 'getVariadic',
    ];

    /**
     * Doc block test data
     *
     * @var array
     */
    protected $_docBlockData = [
        'shortDescription' => 'test_short_description',
        'longDescription' => 'test_long_description',
        'tags' => [
            'tag1' => ['name' => 'tag1', 'description' => 'data1'],
            'tag2' => ['name' => 'tag2', 'description' => 'data2'],
        ],
    ];

    /**
     * Method test data
     *
     * @var array
     */
    protected $_methodData = [
        'testmethod1' => [
            'name' => 'testMethod1',
            'final' => true,
            'static' => true,
            'parameters' => [
                [
                    'name' => 'data',
                    'type' => 'array',
                    'defaultValue' => [],
                    'passedByReference' => true
                ],
            ],
            'body' => 'return 1;',
            'docblock' => ['shortDescription' => 'test short description'],
        ],
        '_testmethod2' => [
            'name' => '_testMethod2',
            'visibility' => 'private',
            'abstract' => true,
            'parameters' => [
                ['name' => 'data', 'defaultValue' => 'test_default'],
                ['name' => 'flag', 'defaultValue' => true],
            ],
            'body' => 'return 2;',
            'docblock' => [
                'shortDescription' => 'test short description',
                'longDescription' => 'test long description',
                'tags' => [
                    'tag1' => ['name' => 'tag1', 'description' => 'data1'],
                    'tag2' => ['name' => 'tag2', 'description' => 'data2'],
                ],
            ],
        ],
        'testmethod3' => ['name' => 'testMethod3', 'body' => 'return 3;'],
    ];

    /**
     * Property test data
     *
     * @var array
     */
    protected $_propertyData = [
        '_protectedProperty' => [
            'name' => '_protectedProperty',
            'visibility' => 'protected',
            'static' => 'true',
            'docblock' => [
                'shortDescription' => 'Object Manager instance',
                'tags' => ['var' => ['name' => 'var', 'description' => 'tag description']],
            ],
        ],
        'publicProperty' => ['name' => 'publicProperty'],
    ];

    protected function setUp(): void
    {
        $this->_model = new ClassGenerator();
    }

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    public function testSetClassDocBlock()
    {
        $this->_model->setClassDocBlock($this->_docBlockData);
        $actualDocBlock = $this->_model->getDocBlock();

        $this->_assertDocBlockData($this->_docBlockData, $actualDocBlock);
    }

    /**
     * @param array $expectedDocBlock
     * @param DocBlockGenerator $actualDocBlock
     */
    protected function _assertDocBlockData(
        array $expectedDocBlock,
        DocBlockGenerator $actualDocBlock
    ) {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');
        // assert plain string data
        foreach ($expectedDocBlock as $propertyName => $propertyData) {
            if (is_string($propertyData)) {
                $this->assertAttributeEquals($propertyData, $propertyName, $actualDocBlock);
            }
        }

        // assert tags
        if (isset($expectedDocBlock['tags'])) {
            $expectedTagsData = $expectedDocBlock['tags'];
            $actualTags = $actualDocBlock->getTags();
            $this->assertSameSize($expectedTagsData, $actualTags);
            /** @var Tag $actualTag */
            foreach ($actualTags as $actualTag) {
                $tagName = $actualTag->getName();
                $this->assertArrayHasKey($tagName, $expectedTagsData);
                $this->assertEquals($expectedTagsData[$tagName]['name'], $tagName);
                $this->assertEquals($expectedTagsData[$tagName]['description'], $actualTag->getDescription());
            }
        }
    }

    public function testAddMethods()
    {
        $this->_model->addMethods($this->_methodData);
        $actualMethods = $this->_model->getMethods();

        $this->assertSameSize($this->_methodData, $actualMethods);

        /** @var MethodGenerator $method */
        foreach ($actualMethods as $methodName => $method) {
            $this->assertArrayHasKey($methodName, $this->_methodData);
            $expectedMethodData = $this->_methodData[$methodName];

            $this->assertEquals($expectedMethodData['name'], $method->getName());
            $this->assertEquals($expectedMethodData['body'], $method->getBody());

            // assert flags
            $this->_assertFlag(self::FLAG_STATIC, $expectedMethodData, $method);
            $this->_assertFlag(self::FLAG_FINAL, $expectedMethodData, $method);
            $this->_assertFlag(self::FLAG_ABSTRACT, $expectedMethodData, $method);

            // assert visibility
            $this->_assertVisibility($expectedMethodData, $method);

            // assert parameters
            if (isset($expectedMethodData['parameters'])) {
                $actualParameters = $method->getParameters();
                $this->assertSameSize($expectedMethodData['parameters'], $actualParameters);
                foreach ($expectedMethodData['parameters'] as $parameterData) {
                    $parameterName = $parameterData['name'];
                    $this->assertArrayHasKey($parameterName, $actualParameters);
                    /** @var ParameterGenerator $actualParameter */
                    $actualParameter = $actualParameters[$parameterName];
                    $this->assertEquals($parameterName, $actualParameter->getName());

                    // assert reference flag
                    $this->_assertFlag(self::FLAG_REFERENCE, $parameterData, $actualParameter);

                    // assert parameter type
                    if (isset($parameterData['type'])) {
                        $this->assertEquals($parameterData['type'], $actualParameter->getType());
                    }

                    // assert default value
                    if (isset($parameterData['defaultValue'])) {
                        /** @var ValueGenerator $actualDefaultValue */
                        $actualDefaultValue = $actualParameter->getDefaultValue();
                        $this->assertEquals($parameterData['defaultValue'], $actualDefaultValue->getValue());
                    }

                    // assert variadic flag
                    $this->_assertFlag(self::FLAG_VARIADIC, $parameterData, $actualParameter);
                }
            }

            // assert docblock
            if (isset($expectedMethodData['docblock'])) {
                $actualDocBlock = $method->getDocBlock();
                $this->_assertDocBlockData($expectedMethodData['docblock'], $actualDocBlock);
            }
        }
    }

    /**
     * @param string $flagType
     * @param array $expectedData
     * @param object $actualObject
     */
    protected function _assertFlag($flagType, array $expectedData, $actualObject)
    {
        $expectedFlagValue = isset($expectedData[$flagType]) && $expectedData[$flagType];
        $flagGetter = $this->_flagVerification[$flagType];
        $this->assertEquals($expectedFlagValue, $actualObject->{$flagGetter}());
    }

    /**
     * @param array $expectedData
     * @param AbstractMemberGenerator $actualObject
     */
    protected function _assertVisibility(
        array $expectedData,
        AbstractMemberGenerator $actualObject
    ) {
        $expectedVisibility = isset($expectedData['visibility']) ? $expectedData['visibility'] : 'public';
        $this->assertEquals($expectedVisibility, $actualObject->getVisibility());
    }

    public function testAddMethodFromGenerator(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('addMethodFromGenerator() expects non-empty string for name');
        $invalidMethod = new MethodGenerator();
        $this->_model->addMethodFromGenerator($invalidMethod);
    }

    public function testAddProperties()
    {
        $this->_model->addProperties($this->_propertyData);
        $actualProperties = $this->_model->getProperties();

        $this->assertSameSize($this->_propertyData, $actualProperties);

        /** @var PropertyGenerator $property */
        foreach ($actualProperties as $propertyName => $property) {
            $this->assertArrayHasKey($propertyName, $this->_propertyData);
            $expectedPropertyData = $this->_propertyData[$propertyName];

            $this->assertEquals($expectedPropertyData['name'], $property->getName());

            // assert flags
            $this->_assertFlag(self::FLAG_CONST, $expectedPropertyData, $property);
            $this->_assertFlag(self::FLAG_STATIC, $expectedPropertyData, $property);

            // assert visibility
            $this->_assertVisibility($expectedPropertyData, $property);

            // assert default value
            if (isset($expectedPropertyData['defaultValue'])) {
                /** @var ValueGenerator $actualDefaultValue */
                $actualDefaultValue = $property->getDefaultValue();
                $this->assertEquals($expectedPropertyData['defaultValue'], $actualDefaultValue->getValue());
            }

            // assert docblock
            if (isset($expectedPropertyData['docblock'])) {
                $actualDocBlock = $property->getDocBlock();
                $this->_assertDocBlockData($expectedPropertyData['docblock'], $actualDocBlock);
            }
        }
    }

    public function testAddPropertyFromGenerator(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('addPropertyFromGenerator() expects non-empty string for name');
        $invalidProperty = new PropertyGenerator();
        $this->_model->addPropertyFromGenerator($invalidProperty);
    }

    /**
     * @dataProvider providerNamespaces
     * @param string $actualNamespace
     * @param string $expectedNamespace
     */
    public function testNamespaceName($actualNamespace, $expectedNamespace)
    {
        $this->assertEquals(
            $expectedNamespace,
            $this->_model->setNamespaceName($actualNamespace)
                ->getNamespaceName()
        );
    }

    /**
     * DataProvider for testNamespaceName
     * @return array
     */
    public function providerNamespaces()
    {
        return [
            ['Laminas', 'Laminas'],
            ['\Laminas', 'Laminas'],
            ['\Laminas\SomeClass', 'Laminas\SomeClass'],
            ['', null],
        ];
    }
}
