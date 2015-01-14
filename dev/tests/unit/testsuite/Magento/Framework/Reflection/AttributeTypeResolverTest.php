<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Reflection;

class AttributeTypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeTypeResolver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $reader;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->typeProcessor = $this->getMock('\Magento\Framework\Reflection\TypeProcessor', [], [], '', false);
        $this->reader = $this->getMock('\Magento\Framework\Api\Config\Reader', [], [], '', false);
        $this->model = new AttributeTypeResolver($this->typeProcessor, $this->reader);
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

        $this->reader->expects($this->once())->method('read')->willReturn([]);
        $this->assertEquals('stdClass', $this->model->resolveObjectType($code, $value, $context));
    }

    public function testResolveObjectTypeWithConfiguredAttribute()
    {
        $code = 'some_code';
        $value = new \stdClass();
        $context = '\Some\Class';
        $config = ['Some\Class' => ['some_code' => '\Magento\Framework\Object']];

        $this->typeProcessor->expects($this->once())
            ->method('getArrayItemType')
            ->with('\Magento\Framework\Object')
            ->willReturn('\Magento\Framework\Object');

        $this->reader->expects($this->once())->method('read')->willReturn($config);
        $this->assertEquals('\Magento\Framework\Object', $this->model->resolveObjectType($code, $value, $context));
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
        $config = ['Some\Class' => ['some_code' => '\Some\Class']];

        $this->typeProcessor->expects($this->once())
            ->method('getArrayItemType')
            ->with('\Some\Class')
            ->willReturn('\Some\Class');

        $this->reader->expects($this->once())->method('read')->willReturn($config);
        $this->model->resolveObjectType($code, $value, $context);
    }
}
