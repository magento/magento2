<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BuilderFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\View\Layout\BuilderFactory
     */
    protected $buildFactory;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $this->buildFactory = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Layout\BuilderFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'typeMap' => [
                    [
                        'type' => 'invalid_type',
                        'class' => \Magento\Framework\View\Layout\BuilderFactory::class,
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
        $layoutBuilderMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Builder::class)
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
                'type' => \Magento\Framework\View\Layout\BuilderFactory::TYPE_LAYOUT,
                'arguments' => ['key' => 'val'],
                'layoutBuilderClass' => \Magento\Framework\View\Layout\Builder::class,
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInvalidData()
    {
        $this->buildFactory->create('some_wrong_type', []);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWithNonBuilderClass()
    {
        $wrongClass = $this->getMockBuilder(\Magento\Framework\View\Layout\BuilderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($wrongClass);

        $this->buildFactory->create('invalid_type', []);
    }
}
