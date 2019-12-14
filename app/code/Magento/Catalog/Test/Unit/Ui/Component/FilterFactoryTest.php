<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\Component;

use PHPUnit\Framework\TestCase;
use Magento\Catalog\Ui\Component\FilterFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class FilterFactoryTest extends TestCase
{
    /**
     * @var FilterFactory
     */
    private $filterFactory;

    /**
     * @var UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentFactoryMock;

    /**
     * Setup environment for test
     */
    protected function setUp()
    {
        $objectManager = new ObjectManagerHelper($this);

        $this->componentFactoryMock = $this->createMock(UiComponentFactory::class);

        $this->filterFactory = $objectManager->getObject(
            FilterFactory::class,
            [
                'componentFactory' => $this->componentFactoryMock
            ]
        );
    }

    /**
     * Test create() with use source attribute
     */
    public function testCreateWithUseSourceAttribute()
    {
        $contextMock = $this->createMock(ContextInterface::class);
        $attributeMock = $this->getMockBuilder(ProductAttributeInterface::class)
            ->setMethods(['usesSource', 'getSource'])
            ->getMockForAbstractClass();
        $attributeMock->method('getAttributeCode')->willReturn('color');
        $attributeMock->method('getDefaultFrontendLabel')->willReturn('Color');
        $attributeMock->method('usesSource')->willReturn(true);
        $attributeMock->method('getSourceModel')->willReturn('getSourceModel value');
        $attributeMock->method('getFrontendInput')->willReturn('select');
        $sourceMock = $this->createMock(SourceInterface::class);
        $attributeMock->method('getSource')->willReturn($sourceMock);
        $sourceMock->method('getAllOptions')->willReturn(
            [
                [
                    'value' => 1,
                    'label' => 'Black',
                ],
                [
                    'value' => 2,
                    'label' => 'White',
                ]
            ]
        );
        $this->componentFactoryMock->expects($this->once())
            ->method('create')
            ->with('color', 'filterSelect', [
                'data' => [
                    'config' => [
                        'options' => [
                            [
                                'value' => 1,
                                'label' => 'Black',
                            ],
                            [
                                'value' => 2,
                                'label' => 'White',
                            ]
                        ],
                        'caption' => (string)__('Select...'),
                        'dataScope' => 'color',
                        'label' => (string)__('Color'),
                    ]
                ],
                'context' => $contextMock
            ]);

        $this->filterFactory->create($attributeMock, $contextMock);
    }
}
