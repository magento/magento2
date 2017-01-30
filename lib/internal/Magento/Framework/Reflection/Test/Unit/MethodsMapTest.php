<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Reflection\FieldNamer;

/**
 * MethodsMap test
 */
class MethodsMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MethodsMap
     */
    private $model;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $cacheMock = $this->getMockBuilder('Magento\Framework\Cache\FrontendInterface')
            ->getMockForAbstractClass();
        $cacheMock->expects($this->any())
            ->method('save');
        $cacheMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue(null));

        $attributeTypeResolverMock = $this->getMockBuilder('Magento\Framework\Api\AttributeTypeResolverInterface')
            ->getMockForAbstractClass();
        $fieldNamerMock = $this->getMockBuilder('Magento\Framework\Reflection\FieldNamer')
            ->getMockForAbstractClass();
        $this->model = $objectManager->getObject(
            'Magento\Framework\Reflection\MethodsMap',
            [
                'cache' => $cacheMock,
                'typeProcessor' => new TypeProcessor(),
                'typeResolver' => $attributeTypeResolverMock,
                'fieldNamer' => $fieldNamerMock,
            ]
        );
    }

    public function testGetMethodReturnType()
    {
        $this->assertEquals(
            'string',
            $this->model->getMethodReturnType('Magento\Framework\Reflection\FieldNamer', 'getFieldNameForMethodName')
        );
        $this->assertEquals(
            'mixed',
            $this->model->getMethodReturnType('Magento\Framework\Reflection\TypeCaster', 'castValueToType')
        );
        $this->assertEquals(
            'array',
            $this->model->getMethodReturnType('Magento\Framework\Reflection\MethodsMap', 'getMethodsMap')
        );
    }

    public function testGetMethodsMap()
    {
        $methodsMap = $this->model->getMethodsMap('Magento\Framework\Reflection\MethodsMap');
        $this->assertEquals(
            [
                'getMethodReturnType' => [
                    'type' => 'string',
                    'isRequired' => true,
                    'description' => null,
                    'parameterCount' => 2,
                ],
                'getMethodsMap' => [
                    'type' => 'array',
                    'isRequired' => true,
                    'description' => "<pre> Service methods' reflection data stored in cache as 'methodName' => "
                        . "'returnType' ex. [ 'create' => '\Magento\Customer\Api\Data\Customer', 'validatePassword' "
                        . "=> 'boolean' ] </pre>",
                    'parameterCount' => 1,
                ],
                'getMethodParams' => [
                    'type' => 'array',
                    'isRequired' => true,
                    'description' => null,
                    'parameterCount' => 2
                ],
                'isMethodValidForDataField' => [
                    'type' => 'bool',
                    'isRequired' => true,
                    'description' => null,
                    'parameterCount' => 2,
                ],
                'isMethodReturnValueRequired' => [
                    'type' => 'bool',
                    'isRequired' => true,
                    'description' => null,
                    'parameterCount' => 2,
                ],
            ],
            $methodsMap
        );
    }

    /**
     * @param string $type
     * @param string $methodName
     * @param bool $expectedResult
     * @dataProvider isMethodValidForDataFieldProvider
     */
    public function testIsMethodValidForDataField($type, $methodName, $expectedResult)
    {
        $this->assertEquals($this->model->isMethodValidForDataField($type, $methodName), $expectedResult);
    }

    /**
     * @return array
     */
    public function isMethodValidForDataFieldProvider()
    {
        return [
            'MethodsMap#isMethodValidForDataField' => [
                'Magento\Framework\Reflection\MethodsMap',
                'isMethodValidForDataField',
                false,
            ],
            'DataObject#getAttrName' => [
                'Magento\Framework\Reflection\Test\Unit\DataObject',
                'getAttrName',
                true,
            ],
            'DataObject#isActive' => [
                'Magento\Framework\Reflection\Test\Unit\DataObject',
                'isActive',
                true,
            ],
        ];
    }

    /**
     * @param string $type
     * @param string $methodName
     * @param bool $expectedResult
     * @dataProvider isMethodReturnValueRequiredProvider
     */
    public function testIsMethodReturnValueRequired($type, $methodName, $expectedResult)
    {
        $this->assertEquals($this->model->isMethodValidForDataField($type, $methodName), $expectedResult);
    }

    /**
     * @return array
     */
    public function isMethodReturnValueRequiredProvider()
    {
        return [
            'DataObject#getAttrName' => [
                'Magento\Framework\Reflection\Test\Unit\DataObject',
                'getAttrName',
                true,
            ],
            'DataObject#isActive' => [
                'Magento\Framework\Reflection\Test\Unit\DataObject',
                'isActive',
                true,
            ],
            'FieldNamer#getFieldNameForMethodName' => [
                'Magento\Framework\Reflection\FieldNamer',
                'getFieldNameForMethodName',
                false,
            ],
        ];
    }
}
