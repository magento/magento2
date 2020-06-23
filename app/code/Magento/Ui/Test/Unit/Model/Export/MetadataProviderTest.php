<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Model\Export;

use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MetadataProviderTest extends TestCase
{
    /**
     * @var MetadataProvider
     */
    private $model;

    /**
     * @var Filter|MockObject
     */
    private $filter;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDate;

    /**
     * @var ResolverInterface|MockObject
     */
    private $localeResolver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->localeDate = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->localeResolver = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->localeResolver->expects($this->any())
            ->method('getLocale')
            ->willReturn(null);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            MetadataProvider::class,
            [
                'filter' => $this->filter,
                'localeDate' => $this->localeDate,
                'localeResolver' => $this->localeResolver,
                'data' => ['component_name' => ['field']],
            ]
        );
    }

    /**
     * @param array $columnLabels
     * @param array $expected
     *
     * @return void
     * @dataProvider getColumnsDataProvider
     * @throws \Exception
     */
    public function testGetHeaders(array $columnLabels, array $expected): void
    {
        $componentName = 'component_name';
        $columnName = 'column_name';

        $component = $this->prepareColumns($componentName, $columnName, $columnLabels[0]);
        $result = $this->model->getHeaders($component);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getColumnsDataProvider(): array
    {
        return [
            [['ID'], ['ID']],
            [['Name'], ['Name']],
            [['Id'], ['Id']],
            [['id'], ['id']],
            [['IDTEST'], ['IDTEST']],
            [['ID TEST'], ['ID TEST']],
        ];
    }

    public function testGetFields()
    {
        $componentName = 'component_name';
        $columnName = 'column_name';
        $columnLabel = 'column_label';

        $component = $this->prepareColumns($componentName, $columnName, $columnLabel);

        $result = $this->model->getFields($component);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals($columnName, $result[0]);
    }

    /**
     * @param string $componentName
     * @param string $columnName
     * @param string $columnLabel
     * @param string $columnActionsName
     * @param string $columnActionsLabel
     *
     * @return UiComponentInterface|MockObject
     */
    protected function prepareColumns(
        $componentName,
        $columnName,
        $columnLabel,
        $columnActionsName = 'actions_name',
        $columnActionsLabel = 'actions_label'
    ) {
        /** @var UiComponentInterface|MockObject $component */
        $component = $this->getMockBuilder(UiComponentInterface::class)
            ->getMockForAbstractClass();

        /** @var Columns|MockObject $columns */
        $columns = $this->getMockBuilder(Columns::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Column|MockObject $column */
        $column = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Column|MockObject $columnActions */
        $columnActions = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->getMock();

        $component->expects($this->any())
            ->method('getName')
            ->willReturn($componentName);
        $component->expects($this->atLeastOnce())
            ->method('getChildComponents')
            ->willReturn([$columns]);

        $columns->expects($this->atLeastOnce())
            ->method('getChildComponents')
            ->willReturn([$column, $columnActions]);

        $column->expects($this->any())
            ->method('getName')
            ->willReturn($columnName);
        $column->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['config/label', null, $columnLabel],
                    ['config/dataType', null, 'data_type'],
                ]
            );

        $columnActions->expects($this->any())
            ->method('getName')
            ->willReturn($columnActionsName);
        $columnActions->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['config/label', null, $columnActionsLabel],
                    ['config/dataType', null, 'actions'],
                ]
            );

        return $component;
    }

    /**
     * @param string $key
     * @param array $fields
     * @param array $options
     * @param array $expected
     *
     * @dataProvider getRowDataProvider
     */
    public function testGetRowData($key, $fields, $options, $expected)
    {
        /** @var DocumentInterface|MockObject $document */
        $document = $this->getMockBuilder(DocumentInterface::class)
            ->getMockForAbstractClass();

        $attribute = $this->getMockBuilder(AttributeInterface::class)
            ->getMockForAbstractClass();

        $document->expects($this->once())
            ->method('getCustomAttribute')
            ->with($fields[0])
            ->willReturn($attribute);

        $attribute->expects($this->once())
            ->method('getValue')
            ->willReturn($key);

        $result = $this->model->getRowData($document, $fields, $options);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getRowDataProvider()
    {
        return [
            [
                'key' => 'key_1',
                'fields' => ['column'],
                'options' => [
                    'column' => [
                        'key_1' => 'value_1',
                    ],
                ],
                'expected' => [
                    'value_1',
                ],
            ],
            [
                'key' => 'key_2',
                'fields' => ['column'],
                'options' => [
                    'column' => [
                        'key_1' => 'value_1',
                    ],
                ],
                'expected' => [
                    'key_2',
                ],
            ],
            [
                'key' => 'key_1',
                'fields' => ['column'],
                'options' => [],
                'expected' => [
                    'key_1',
                ],
            ],
        ];
    }

    /**
     * @param string $filter
     * @param array $filterOptions
     * @param array $columnsOptions
     * @param array $expected
     *
     * @throws LocalizedException
     * @dataProvider getOptionsDataProvider
     */
    public function testGetOptions(string $filter, array $filterOptions, array $columnsOptions, array $expected)
    {
        $component = $this->prepareColumnsWithOptions($filter, $filterOptions, $columnsOptions);

        $this->filter->expects($this->exactly(2))
            ->method('getComponent')
            ->willReturn($component);

        $result = $this->model->getOptions();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $filter
     * @param array $filterOptions
     *
     * @param array $columnsOptions
     *
     * @return UiComponentInterface|MockObject
     */
    protected function prepareColumnsWithOptions(string $filter, array $filterOptions, array $columnsOptions)
    {
        /** @var UiComponentInterface|MockObject $component */
        $component = $this->getMockBuilder(UiComponentInterface::class)
            ->getMockForAbstractClass();

        $listingTopComponent = $this->getMockBuilder(UiComponentInterface::class)
            ->getMockForAbstractClass();

        $filters = $this->getMockBuilder(Filters::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Columns|MockObject $columns */
        $columns = $this->getMockBuilder(Columns::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Column|MockObject $column */
        $column = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Column|MockObject $columnActions */
        $columnActions = $this->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->getMock();

        $component->expects($this->any())
            ->method('getName')
            ->willReturn('columns_component_name');
        $component->expects($this->atLeastOnce())
            ->method('getChildComponents')
            ->willReturn(['columns' => $columns, 'listing_top' => $listingTopComponent]);

        $listingTopComponent->expects($this->once())
            ->method('getChildComponents')
            ->willReturn([$filters]);

        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filters->expects($this->once())
            ->method('getChildComponents')
            ->willReturn([$select]);

        $select->expects($this->any())
            ->method('getName')
            ->willReturn($filter);
        $select->expects($this->any())
            ->method('getData')
            ->with('config/options')
            ->willReturn($filterOptions);

        $columns->expects($this->atLeastOnce())
            ->method('getChildComponents')
            ->willReturn([$column, $columnActions]);

        $column->expects($this->any())
            ->method('getName')
            ->willReturn('column_name');

        $optionSource = $this->getMockBuilder(OptionSourceInterface::class)
            ->getMockForAbstractClass();
        $optionSource->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($columnsOptions);

        $column->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['config/label', null, 'column_label'],
                    ['config/dataType', null, 'data_type'],
                    ['options', null, $optionSource],
                ]
            );

        $column->expects($this->once())
            ->method('hasData')
            ->willReturn(true)
            ->with('options');

        $columnActions->expects($this->any())
            ->method('getName')
            ->willReturn('column_actions_name');
        $columnActions->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['config/label', null, 'column_actions_label'],
                    ['config/dataType', null, 'actions'],
                ]
            );

        return $component;
    }

    /**
     * @return array
     */
    public function getOptionsDataProvider()
    {
        return [
            [
                'filter' => 'filter_name',
                'filterOptions' => [
                    [
                        'value' => 'value_1',
                        'label' => 'label_1',
                    ]
                ],
                'columnsOptions' => [
                    [
                        'value' => 'value_1',
                        'label' => 'label_1',
                    ]
                ],
                'expected' => [
                    'filter_name' => [
                        'value_1' => 'label_1',
                    ],
                    'column_name' => [
                        'value_1' => 'label_1',
                    ]
                ],
            ],
            [
                'filter' => 'filter_name',
                'filterOptions' => [
                    [
                        'value' => [
                            [
                                'value' => 'value_2',
                                'label' => 'label_2',
                            ],
                        ],
                        'label' => 'label_1',
                    ]
                ],
                'columnsOptions' => [
                    [
                        'value' => [
                            [
                                'value' => 'value_2',
                                'label' => 'label_2',
                            ],
                        ],
                        'label' => 'label_1',
                    ]
                ],
                'expected' => [
                    'filter_name' => [
                        'value_2' => 'label_1label_2',
                    ],
                    'column_name' => [
                        'value_2' => 'label_1label_2',
                    ]
                ],
            ],
            [
                'filter' => 'filter_name',
                'filterOptions' => [
                    [
                        'value' => [
                            [
                                'value' => [
                                    [
                                        'value' => 'value_3',
                                        'label' => 'label_3',
                                    ]
                                ],
                                'label' => 'label_2',
                            ],
                        ],
                        'label' => 'label_1',
                    ]
                ],
                'columnsOptions' => [],
                'expected' => [
                    'filter_name' => [
                        'value_3' => 'label_1label_2label_3',
                    ],
                    'column_name' => []
                ],
            ],
        ];
    }

    /**
     * Test for convertDate function
     *
     * @param string $fieldValue
     * @param string $expected
     *
     * @dataProvider convertDateProvider
     * @covers       \Magento\Ui\Model\Export\MetadataProvider::convertDate()
     * @throws \Exception
     */
    public function testConvertDate($fieldValue, $expected)
    {
        $componentName = 'component_name';
        /** @var DocumentInterface|MockObject $document */
        $document = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $document->expects($this->once())
            ->method('getData')
            ->with('field')
            ->willReturn($fieldValue);

        $this->localeDate->expects($this->once())
            ->method('date')
            ->willReturn(new \DateTime($fieldValue, new \DateTimeZone('UTC')));

        $document->expects($this->once())
            ->method('setData')
            ->with('field', $expected);

        $this->model->convertDate($document, $componentName);
    }

    /**
     * Data provider for testConvertDate
     *
     * @return array
     */
    public function convertDateProvider()
    {
        return [
            [
                'fieldValue' => '@1534505233',
                'expected' => 'Aug 17, 2018 11:27:13 AM',
            ],
            [
                'fieldValue' => '@1534530000',
                'expected' => 'Aug 17, 2018 06:20:00 PM',
            ],
        ];
    }
}
