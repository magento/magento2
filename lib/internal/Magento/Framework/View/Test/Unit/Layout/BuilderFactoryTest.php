<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout\Builder;
use Magento\Framework\View\Layout\BuilderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var BuilderFactory
     */
    protected $buildFactory;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->buildFactory = $this->objectManagerHelper->getObject(
            BuilderFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'typeMap' => [
                    [
                        'type' => 'invalid_type',
                        'class' => BuilderFactory::class,
                    ],
                ]
            ]
        );
    }

    /**
     * @param string $type
     * @param array $arguments
     *
     * @dataProvider createDataProvider
     */
    public function testCreate($type, $arguments, $layoutBuilderClass)
    {
        $layoutBuilderMock = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($layoutBuilderClass, $arguments)
            ->willReturn($layoutBuilderMock);

        $this->buildFactory->create($type, $arguments);
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            'layout_type' => [
                'type' => BuilderFactory::TYPE_LAYOUT,
                'arguments' => ['key' => 'val'],
                'layoutBuilderClass' => Builder::class,
            ]
        ];
    }

    public function testCreateInvalidData()
    {
        $this->expectException('InvalidArgumentException');
        $this->buildFactory->create('some_wrong_type', []);
    }

    public function testCreateWithNonBuilderClass()
    {
        $this->expectException('InvalidArgumentException');
        $wrongClass = $this->getMockBuilder(BuilderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($wrongClass);

        $this->buildFactory->create('invalid_type', []);
    }
}
