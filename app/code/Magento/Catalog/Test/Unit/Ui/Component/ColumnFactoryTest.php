<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\Component;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Ui\Component\ColumnFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test to Create columns factory on product grid page
 */
class ColumnFactoryTest extends TestCase
{
    /**
     * @var ColumnFactory
     */
    private $columnFactory;

    /**
     * @var Attribute|MockObject
     */
    private $attribute;

    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var UiComponentFactory|MockObject
     */
    private $uiComponentFactory;

    /**
     * @var ColumnInterface|MockObject
     */
    private $column;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $timezone;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attribute = $this->createMock(Attribute::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->uiComponentFactory = $this->createMock(UiComponentFactory::class);
        $this->column = $this->createMock(ColumnInterface::class);
        $this->uiComponentFactory->method('create')
            ->willReturn($this->column);
        $this->timezone = $this->createMock(TimezoneInterface::class);

        $this->columnFactory = new ColumnFactory($this->uiComponentFactory, $this->timezone);
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
     * Filter modifiers data provider
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

    /**
     * Test to create date column
     *
     * @param string $frontendInput
     * @param bool $showsTime
     * @param string $expectedDateFormat
     * @param string $expectedTimezone
     * @dataProvider createDateColumnDataProvider
     */
    public function testCreateDateColumn(
        string $frontendInput,
        bool $showsTime,
        string $expectedDateFormat,
        string $expectedTimezone
    ) {
        $attributeCode = 'attribute_code';
        $dateFormat = 'date_format';
        $dateTimeFormat = 'datetime_format';
        $defaultTimezone = 'default_timezone';
        $configTimezone = 'config_timezone';
        $label = 'Date label';

        $expectedConfig = [
            'data' => [
                'config' => [
                    'label' => __($label),
                    'dataType' => 'date',
                    'add_field' => true,
                    'visible' => true,
                    'filter' => 'dateRange',
                    'component' => 'Magento_Ui/js/grid/columns/date',
                    'timezone' => $expectedTimezone,
                    'dateFormat' => $expectedDateFormat,
                    'options' => [
                        'showsTime' => $showsTime
                    ]
                ],
            ],
            'context' => $this->context,
        ];

        $this->attribute->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->attribute->method('getDefaultFrontendLabel')
            ->willReturn($label);
        $this->attribute->method('getIsFilterableInGrid')
            ->willReturn(true);
        $this->attribute->method('getIsVisibleInGrid')
            ->willReturn(true);
        $this->attribute->method('getFrontendInput')
            ->willReturn($frontendInput);

        $this->timezone->method('getDateFormat')
            ->with(\IntlDateFormatter::MEDIUM)
            ->willReturn($dateFormat);
        $this->timezone->method('getDateTimeFormat')
            ->with(\IntlDateFormatter::MEDIUM)
            ->willReturn($dateTimeFormat);
        $this->timezone->method('getDefaultTimezone')
            ->willReturn($defaultTimezone);
        $this->timezone->method('getConfigTimezone')
            ->willReturn($configTimezone);

        $this->uiComponentFactory->expects($this->once())
            ->method('create')
            ->with($attributeCode, 'column', $expectedConfig)
            ->willReturn($this->column);

        $this->assertEquals(
            $this->column,
            $this->columnFactory->create($this->attribute, $this->context)
        );
    }

    /**
     * Data provider to create date column test
     *
     * @return array
     */
    public function createDateColumnDataProvider(): array
    {
        return [
            [
                'frontendInput' => 'date',
                'showsTime' => false,
                'dateFormat' => 'date_format',
                'expectedTimezone' => 'default_timezone',
            ],
            [
                'frontendInput' => 'datetime',
                'showsTime' => true,
                'expectedDateFormat' => 'datetime_format',
                'expectedTimezone' => 'config_timezone',
            ],
        ];
    }

    public function testCreateAttributeWithSource(): void
    {
        $this->context->method('getRequestParam')
            ->with(FilterModifier::FILTER_MODIFIER, [])
            ->willReturn([]);
        $attributeCode = 'color';
        $this->attribute->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $label = 'Color';
        $this->attribute->expects($this->atLeastOnce())
            ->method('getDefaultFrontendLabel')
            ->willReturn($label);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getFrontendInput')
            ->willReturn('select');
        $this->attribute->expects($this->atLeastOnce())
            ->method('getIsVisibleInGrid')
            ->willReturn(true);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getIsFilterableInGrid')
            ->willReturn(true);
        $this->attribute->expects($this->atLeastOnce())
            ->method('usesSource')
            ->willReturn(true);
        $source = $this->createMock(AbstractSource::class);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getSource')
            ->willReturn($source);
        $options = [
            ['label' => ''],
            ['label' => 'admin1'],
            ['label' => 'admin2'],
        ];
        $source->expects($this->atLeastOnce())
            ->method('getAllOptions')
            ->with(true, true)
            ->willReturn($options);

        $expectedConfig = [
            'label' => __($label),
            'dataType' => 'select',
            'add_field' => true,
            'visible' => true,
            'filter' => 'select',
            'component' => 'Magento_Ui/js/grid/columns/select',
            'options' => array_map(
                function (array $option) {
                    $option['__disableTmpl'] = true;
                    return $option;
                },
                $options
            ),
        ];
        $expectedArguments = [
            'data' => ['config' => $expectedConfig],
            'context' => $this->context,
        ];
        $this->uiComponentFactory->expects($this->once())
            ->method('create')
            ->with($attributeCode, 'column', $expectedArguments)
            ->willReturn($this->column);

        $this->assertEquals(
            $this->column,
            $this->columnFactory->create($this->attribute, $this->context)
        );
    }
}
