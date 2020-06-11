<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Model\Config;

use Laminas\Code\Reflection\ClassReflection;
use Laminas\Code\Reflection\MethodReflection;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Webapi\Model\Config\ClassReflector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class reflector.
 */
class ClassReflectorTest extends TestCase
{
    /** @var TypeProcessor|MockObject */
    protected $_typeProcessor;

    /** @var ClassReflector */
    protected $_classReflector;

    /**
     * Set up helper.
     */
    protected function setUp(): void
    {
        $this->_typeProcessor = $this->getMockBuilder(TypeProcessor::class)
            ->addMethods(['process'])
            ->disableOriginalConstructor()
            ->getMock();
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
