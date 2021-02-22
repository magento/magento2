<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\Component;

use PHPUnit\Framework\TestCase;
use Magento\Catalog\Ui\Component\ColumnFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;
use Magento\Ui\Component\Filters\FilterModifier;

/**
 * ColumnFactory test.
 */
class ColumnFactoryTest extends TestCase
{
    /**
     * @var ColumnFactory
     */
    private $columnFactory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductAttributeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attribute;

    /**
     * @var ContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $context;

    /**
     * @var UiComponentFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $uiComponentFactory;

    /**
     * @var ColumnInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $column;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->attribute = $this->getMockBuilder(ProductAttributeInterface::class)
            ->setMethods(['usesSource'])
            ->getMockForAbstractClass();
        $this->context = $this->getMockForAbstractClass(ContextInterface::class);
        $this->uiComponentFactory = $this->createMock(UiComponentFactory::class);
        $this->column = $this->getMockForAbstractClass(ColumnInterface::class);
        $this->uiComponentFactory->method('create')
            ->willReturn($this->column);

        $this->columnFactory = $this->objectManager->getObject(
            ColumnFactory::class,
            ['componentFactory' => $this->uiComponentFactory]
        );
    }

    /**
     * Tests the create method will return correct object.
     *
     * @return void
     */
    public function testCreatedObject(): void
    {
        $this->context->method('getRequestParam')
            ->with(FilterModifier::FILTER_MODIFIER, [])
            ->willReturn([]);

        $object = $this->columnFactory->create($this->attribute, $this->context);
        $this->assertEquals(
            $this->column,
            $object,
            'Object must be the same which the ui component factory creates.'
        );
    }

    /**
     * Tests create method with not filterable in grid attribute.
     *
     * @param array $filterModifiers
     * @param null|string $filter
     *
     * @return void
     * @dataProvider filterModifiersProvider
     */
    public function testCreateWithNotFilterableInGridAttribute(array $filterModifiers, ?string $filter): void
    {
        $componentFactoryArgument = [
            'data' => [
                'config' => [
                    'label' => __(null),
                    'dataType' => 'text',
                    'add_field' => true,
                    'visible' => null,
                    'filter' => $filter,
                    'component' => 'Magento_Ui/js/grid/columns/column',
                    '__disableTmpl' => ['label' => true]
                ],
            ],
            'context' => $this->context,
        ];

        $this->context->method('getRequestParam')
            ->with(FilterModifier::FILTER_MODIFIER, [])
            ->willReturn($filterModifiers);
        $this->attribute->method('getIsFilterableInGrid')
            ->willReturn(false);
        $this->attribute->method('getAttributeCode')
            ->willReturn('color');

        $this->uiComponentFactory->expects($this->once())
            ->method('create')
            ->with($this->anything(), $this->anything(), $componentFactoryArgument);

        $this->columnFactory->create($this->attribute, $this->context);
    }

    /**
     * Filter modifiers data provider.
     *
     * @return array
     */
    public function filterModifiersProvider(): array
    {
        return [
            'without' => [
                'filter_modifiers' => [],
                'filter' => null,
            ],
            'with' => [
                'filter_modifiers' => [
                    'color' => [
                        'condition_type' => 'notnull',
                    ],
                ],
                'filter' => 'text',
            ],
        ];
    }
}
