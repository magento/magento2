<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Config;

use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\MethodReflection;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Webapi\Model\Config\ClassReflector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class reflector.
 */
class ClassReflectorTest extends TestCase
{
    use MockCreationTrait;

    /** @var TypeProcessor|MockObject */
    protected $_typeProcessor;

    /** @var ClassReflector */
    protected $_classReflector;

    /**
     * Set up helper.
     */
    protected function setUp(): void
    {
        $this->_typeProcessor = $this->createPartialMockWithReflection(
            TypeProcessor::class,
            ['process']
        );
        $this->_typeProcessor->expects(
            $this->any()
        )->method(
            'process'
        )->willReturnMap(
            [['string', 'str'], ['int', 'int']]
        );
        $this->_classReflector = new ClassReflector($this->_typeProcessor);
    }

    public function testReflectClassMethods()
    {
        $data = $this->_classReflector->reflectClassMethods(
            TestServiceForClassReflector::class,
            ['generateRandomString' => ['method' => 'generateRandomString']]
        );
        $this->assertEquals(['generateRandomString' => $this->_getSampleReflectionData()], $data);
    }

    public function testReflectMethodWithoutDocBlock(): void
    {
        $reflector = new ClassReflector(new TypeProcessor());

        $data = $reflector->reflectClassMethods(
            UndocumentedServiceForClassReflector::class,
            ['execute' => ['method' => 'execute']]
        );

        self::assertSame('', $data['execute']['documentation']);
        self::assertSame([], $data['execute']['interface']['out']['throws'] ?? []);
        self::assertSame('string', $data['execute']['interface']['out']['parameters']['result']['type']);
    }

    public function testReflectMethodWithPartialParameterDocumentation(): void
    {
        $reflector = new ClassReflector(new TypeProcessor());

        $data = $reflector->reflectClassMethods(
            PartialDocBlockServiceForClassReflector::class,
            ['save' => ['method' => 'save']]
        );

        self::assertSame(
            [
                'id' => ['type' => 'int', 'required' => true, 'documentation' => ''],
                'items' => ['type' => 'string[]', 'required' => true, 'documentation' => 'Items to save'],
            ],
            $data['save']['interface']['in']['parameters']
        );
    }

    public function testExtractMethodData()
    {
        $classReflection = new ClassReflection(
            TestServiceForClassReflector::class
        );
        /** @var MethodReflection $methodReflection */
        $methodReflection = $classReflection->getMethods()[0];
        $methodData = $this->_classReflector->extractMethodData($methodReflection);
        $expectedResponse = $this->_getSampleReflectionData();
        $this->assertEquals($expectedResponse, $methodData);
    }

    /**
     * Expected reflection data for TestServiceForClassReflector generateRandomString method
     *
     * @return array
     */
    protected function _getSampleReflectionData()
    {
        return [
            'documentation' => 'Basic random string generator. This line is short description ' .
                'This line is long description. This is still the long description.',
            'interface' => [
                'in' => [
                    'parameters' => [
                        'length' => [
                            'type' => 'int',
                            'required' => true,
                            'documentation' => 'length of the random string',
                        ],
                    ],
                ],
                'out' => [
                    'parameters' => [
                        'result' => ['type' => 'string', 'documentation' => 'random string', 'required' => true],
                    ],
                ],
            ]
        ];
    }
}
