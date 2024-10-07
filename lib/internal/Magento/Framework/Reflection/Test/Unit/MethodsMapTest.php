<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Api\AttributeTypeResolverInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Reflection\FieldNamer;
use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeCaster;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MethodsMapTest extends TestCase
{
    /**
     * @var MethodsMap
     */
    private $object;

    /** @var SerializerInterface|MockObject */
    private $serializerMock;

    /**
     * Set up helper.
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $cacheMock = $this->getMockBuilder(FrontendInterface::class)
            ->getMockForAbstractClass();
        $cacheMock->expects($this->any())
            ->method('save');
        $cacheMock->expects($this->any())
            ->method('load')
            ->willReturn(null);

        $attributeTypeResolverMock = $this->getMockBuilder(AttributeTypeResolverInterface::class)
            ->getMockForAbstractClass();
        $fieldNamerMock = $this->getMockBuilder(FieldNamer::class)
            ->getMockForAbstractClass();
        $this->object = $objectManager->getObject(
            MethodsMap::class,
            [
                'cache' => $cacheMock,
                'typeProcessor' => new TypeProcessor(),
                'typeResolver' => $attributeTypeResolverMock,
                'fieldNamer' => $fieldNamerMock,
            ]
        );
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $objectManager->setBackwardCompatibleProperty(
            $this->object,
            'serializer',
            $this->serializerMock
        );
    }

    public function testGetMethodReturnType()
    {
        $this->assertEquals(
            'string',
            $this->object->getMethodReturnType(
                FieldNamer::class,
                'getFieldNameForMethodName'
            )
        );
        $this->assertEquals(
            'mixed',
            $this->object->getMethodReturnType(
                TypeCaster::class,
                'castValueToType'
            )
        );
        $this->assertEquals(
            'array',
            $this->object->getMethodReturnType(
                MethodsMap::class,
                'getMethodsMap'
            )
        );
    }

    public function testGetMethodsMap()
    {
        $data = [
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
        ];
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data);
        $methodsMap = $this->object->getMethodsMap(MethodsMap::class);
        $this->assertEquals(
            $data,
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
        $this->assertEquals($this->object->isMethodValidForDataField($type, $methodName), $expectedResult);
    }

    /**
     * @return array
     */
    public static function isMethodValidForDataFieldProvider()
    {
        return [
            'MethodsMap#isMethodValidForDataField' => [MethodsMap::class,
                'isMethodValidForDataField',
                false,
            ],
            'DataObject#getAttrName' => [DataObject::class,
                'getAttrName',
                true,
            ],
            'DataObject#isActive' => [DataObject::class,
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
        $this->assertEquals($this->object->isMethodValidForDataField($type, $methodName), $expectedResult);
    }

    /**
     * @return array
     */
    public static function isMethodReturnValueRequiredProvider()
    {
        return [
            'DataObject#getAttrName' => [DataObject::class,
                'getAttrName',
                true,
            ],
            'DataObject#isActive' => [DataObject::class,
                'isActive',
                true,
            ],
            'FieldNamer#getFieldNameForMethodName' => [FieldNamer::class,
                'getFieldNameForMethodName',
                false,
            ],
        ];
    }
}
