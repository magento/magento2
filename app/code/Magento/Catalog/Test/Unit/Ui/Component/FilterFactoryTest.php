<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\Component;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\Component\FilterFactory;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterFactoryTest extends TestCase
{
    /**
     * Stub attribute
     */
    private const STUB_ATTRIBUTE = [
        'attribute_code' => 'color',
        'default_frontend_label' => 'Color',
        'uses_source' => 'Color',
        'source_model' => 'getSourceModel value',
        'frontend_input' => 'select',
        'all_options' => [
            [
                'value' => 1,
                'label' => 'Black',
            ],
            [
                'value' => 2,
                'label' => 'White',
            ]
        ]
    ];

    /**
     * @var FilterFactory
     */
    private $filterFactory;

    /**
     * @var UiComponentFactory|MockObject
     */
    private $componentFactoryMock;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

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
        $contextMock = $this->getMockForAbstractClass(ContextInterface::class);
        $attributeMock = $this->getMockBuilder(ProductAttributeInterface::class)
            ->setMethods(['usesSource', 'getSource'])
            ->getMockForAbstractClass();
        $attributeMock->method('getAttributeCode')->willReturn(self::STUB_ATTRIBUTE['attribute_code']);
        $attributeMock->method('getDefaultFrontendLabel')
            ->willReturn(self::STUB_ATTRIBUTE['default_frontend_label']);
        $attributeMock->method('usesSource')->willReturn(self::STUB_ATTRIBUTE['uses_source']);
        $attributeMock->method('getSourceModel')->willReturn(self::STUB_ATTRIBUTE['source_model']);
        $attributeMock->method('getFrontendInput')->willReturn(self::STUB_ATTRIBUTE['frontend_input']);
        $sourceMock = $this->getMockForAbstractClass(SourceInterface::class);
        $attributeMock->method('getSource')->willReturn($sourceMock);
        $sourceMock->method('getAllOptions')->willReturn(self::STUB_ATTRIBUTE['all_options']);
        $this->componentFactoryMock->expects($this->once())
            ->method('create')
            ->with(self::STUB_ATTRIBUTE['attribute_code'], 'filterSelect', [
                'data' => [
                    'config' => [
                        'options' => self::STUB_ATTRIBUTE['all_options'],
                        'caption' => (string)__('Select...'),
                        'dataScope' => self::STUB_ATTRIBUTE['attribute_code'],
                        'label' => self::STUB_ATTRIBUTE['default_frontend_label'],
                    ]
                ],
                'context' => $contextMock
            ]);

        $this->filterFactory->create($attributeMock, $contextMock);
    }
}
