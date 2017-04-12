<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection\Test\Unit;

use \Magento\Framework\Reflection\AttributeTypeResolver;

class AttributeTypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeTypeResolver
     */
    protected $model;

    /**
     * @var \Magento\Framework\Reflection\TypeProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeProcessor;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->typeProcessor = $this->getMock(\Magento\Framework\Reflection\TypeProcessor::class, [], [], '', false);
        $this->configMock = $this->getMock(\Magento\Framework\Api\ExtensionAttribute\Config::class, [], [], '', false);
        $this->model = new AttributeTypeResolver($this->typeProcessor, $this->configMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Provided value is not object type
     */
    public function testResolveObjectTypeWithNonObjectValue()
    {
        $code = 'some_code';
        $value = 'string';
        $context = 'Some\Class';
        $this->model->resolveObjectType($code, $value, $context);
    }

    public function testResolveObjectTypeWithoutConfiguredAttribute()
    {
        $code = 'some_code';
        $value = new \stdClass();
        $context = 'Some\Class';

        $this->configMock->expects($this->once())->method('get')->willReturn([]);
        $this->assertEquals('stdClass', $this->model->resolveObjectType($code, $value, $context));
    }

    public function testResolveObjectTypeWithConfiguredAttribute()
    {
        $code = 'some_code';
        $value = new \stdClass();
        $context = '\Some\Class';
        $config = [
            'Some\Class' => [
                'some_code' => [
                    'type' => \Magento\Framework\DataObject::class,
                ],
            ]
        ];

        $this->typeProcessor->expects($this->once())
            ->method('getArrayItemType')
            ->with(\Magento\Framework\DataObject::class)
            ->willReturn(\Magento\Framework\DataObject::class);

        $this->configMock->expects($this->once())->method('get')->willReturn($config);
        $this->assertEquals(
            \Magento\Framework\DataObject::class,
            $this->model->resolveObjectType($code, $value, $context)
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Class "\Some\Class" does not exist. Please note that namespace must be specified.
     */
    public function testResolveObjectTypeWithConfiguredAttributeAndNonExistedClass()
    {
        $code = 'some_code';
        $value = new \stdClass();
        $context = '\Some\Class';
        $config = [
            'Some\Class' => [
                'some_code' => [
                    'type' => '\Some\Class',
                ]
            ]
        ];

        $this->typeProcessor->expects($this->once())
            ->method('getArrayItemType')
            ->with('\Some\Class')
            ->willReturn('\Some\Class');

        $this->configMock->expects($this->once())->method('get')->willReturn($config);
        $this->model->resolveObjectType($code, $value, $context);
    }
}
