<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Api\ExtensionAttribute\Config;
use Magento\Framework\DataObject;
use Magento\Framework\Reflection\AttributeTypeResolver;
use Magento\Framework\Reflection\TypeProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeTypeResolverTest extends TestCase
{
    /**
     * @var AttributeTypeResolver
     */
    protected $model;

    /**
     * @var TypeProcessor|MockObject
     */
    protected $typeProcessor;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * Set up helper.
     */
    protected function setUp(): void
    {
        $this->typeProcessor = $this->createMock(TypeProcessor::class);
        $this->configMock = $this->createMock(Config::class);
        $this->model = new AttributeTypeResolver($this->typeProcessor, $this->configMock);
    }

    public function testResolveObjectTypeWithNonObjectValue()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Provided value is not object type');
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
                    'type' => DataObject::class,
                ],
            ]
        ];

        $this->typeProcessor->expects($this->once())
            ->method('getArrayItemType')
            ->with(DataObject::class)
            ->willReturn(DataObject::class);

        $this->configMock->expects($this->once())->method('get')->willReturn($config);
        $this->assertEquals(
            DataObject::class,
            $this->model->resolveObjectType($code, $value, $context)
        );
    }

    public function testResolveObjectTypeWithConfiguredAttributeAndNonExistedClass()
    {
        $this->expectException('LogicException');
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

        $this->expectExceptionMessage(
            'The "\Some\Class" class doesn\'t exist and the namespace must be specified. Verify and try again.'
        );
    }
}
