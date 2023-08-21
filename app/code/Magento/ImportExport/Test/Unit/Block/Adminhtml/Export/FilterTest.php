<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Block\Adminhtml\Export;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\View\Element\Html\Date;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Layout;
use Magento\ImportExport\Block\Adminhtml\Export\Filter;
use Magento\ImportExport\Model\ResourceModel\Export\AttributeGridCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD)
 */
class FilterTest extends TestCase
{
    /**
     * @var Filter|MockObject
     */
    private $filter;

    /**
     * @var Layout|MockObject
     */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $context = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getFileSystem', 'getEscaper', 'getLocaleDate', 'getLayout'])
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem = $this->createMock(Filesystem::class);
        $context->expects($this->any())->method('getFileSystem')->willReturn($filesystem);
        $escaper = $this->createPartialMock(Escaper::class, ['escapeHtml']);
        $escaper->expects($this->any())->method('escapeHtml')->willReturnArgument(0);
        $context->expects($this->any())->method('getEscaper')->willReturn($escaper);
        $timeZone = $this->createMock(Timezone::class);
        $timeZone->expects($this->any())->method('getDateFormat')->willReturn('M/d/yy');
        $context->expects($this->any())->method('getLocaleDate')->willReturn($timeZone);
        $this->layout = $this->createMock(Layout::class);
        $context->expects($this->any())->method('getLayout')->willReturn($this->layout);
        $backendHelper = $this->createMock(Data::class);
        $importExportData = $this->createMock(\Magento\ImportExport\Helper\Data::class);
        $attributeGridCollectionFactory = $this->createMock(AttributeGridCollectionFactory::class);
        $this->filter = new Filter(
            $context,
            $backendHelper,
            $importExportData,
            [],
            $attributeGridCollectionFactory
        );
    }

    /**
     * Test date filter
     *
     * @param array $attributeData
     * @param array $values
     * @param array $expect
     * @dataProvider dateFilterDataProvider
     */
    public function testDateFilter(array $attributeData, array $values, array $expect): void
    {
        $type = Date::class;
        $block = $block = $this->getMockBuilder($type)
            ->addMethods(['setValue'])
            ->onlyMethods(['getHtml'])
            ->disableOriginalConstructor()
            ->getMock();
        $block->expects($this->exactly(2))
            ->method('setValue')
            ->withConsecutive(...$expect)
            ->willReturnSelf();
        $this->layout->expects($this->once())
            ->method('createBlock')
            ->with($type)
            ->willReturn($block);
        $attribute = $this->getAttributeMock($attributeData);
        $column = new DataObject();
        $column->addData($values);
        $isExport = true;
        $result = $this->filter->decorateFilter(null, $attribute, $column, $isExport);
        $this->assertNotNull($result);
    }

    /**
     * @return array[]
     */
    public function dateFilterDataProvider(): array
    {
        return  [
            [
                [
                    'attribute_code' =>'updated_at',
                    'frontend_input' => '',
                    'options' => [],
                    'filter_options' => [],
                    'backend_type' => 'datetime',
                ],
                ['values' => ['updated_at' => ['12/12/12', '12/15/12']]],
                [
                    ['12/12/12'],
                    ['12/15/12']
                ]
            ],
        ];
    }

    /**
     * Test select filter
     *
     * @param array $attributeData
     * @param array $values
     * @param array $expect
     * @dataProvider selectFilterDataProvider
     */
    public function testSelectFilter(array $attributeData, array $values, array $expect): void
    {
        $html = '<select></select>';
        $type = Select::class;
        $block = $block = $this->getMockBuilder($type)
            ->onlyMethods(['getHtml'])
            ->disableOriginalConstructor()
            ->getMock();
        $block->expects($this->once())
            ->method('getHtml')
            ->willReturn($html);
        $this->layout->expects($this->once())
            ->method('createBlock')
            ->with($type)
            ->willReturn($block);
        $attribute = $this->getAttributeMock($attributeData);
        $column = new DataObject();
        $column->addData($values);
        $isExport = true;
        $result = $this->filter->decorateFilter(null, $attribute, $column, $isExport);
        $this->assertEquals($html, $result);
        $this->assertSame($expect['value'], $block->getValue());
        $this->assertEquals($expect['options'], $block->getOptions());
    }

    /**
     * @return array[]
     */
    public function selectFilterDataProvider(): array
    {
        return  [
            [
                [
                    'attribute_code' => 'color',
                    'frontend_input' => 'select',
                    'filter_options' => ['6' => 'Green', '7' => 'Blue'],
                    'backend_type' => 'select',
                ],
                ['values' => ['color' => '6']],
                [
                    'value' => '6',
                    'options' => [
                        [
                            'label' => '-- Not Selected --',
                            'value' => ''
                        ],
                        [
                            'label' => 'Green',
                            'value' => '6'
                        ],
                        [
                            'label' => 'Blue',
                            'value' => '7'
                        ]
                    ]
                ]
            ],
            [
                [
                    'attribute_code' => 'color',
                    'frontend_input' => 'select',
                    'filter_options' => ['6' => 'Green', '7' => 'Blue'],
                    'backend_type' => 'select',
                ],
                ['values' => ['color' => '']],
                [
                    'value' => null,
                    'options' => [
                        [
                            'label' => '-- Not Selected --',
                            'value' => ''
                        ],
                        [
                            'label' => 'Green',
                            'value' => '6'
                        ],
                        [
                            'label' => 'Blue',
                            'value' => '7'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Test input filter
     *
     * @param array $attributeData
     * @param array $values
     * @param array $expect
     * @dataProvider inputFilterDataProvider
     */
    public function testInputFilter(array $attributeData, array $values, array $expect): void
    {
        $this->layout->expects($this->never())
            ->method('createBlock');
        $attribute = $this->getAttributeMock($attributeData);
        $column = new DataObject();
        $column->addData($values);
        $isExport = true;
        $result = $this->filter->decorateFilter(null, $attribute, $column, $isExport);
        $tag = simplexml_load_string($result);
        $attributes = [];
        foreach ($tag->attributes() as $name => $value) {
            $attributes[$name] = "$value";
        }
        $this->assertEquals($expect, array_intersect_key($expect, $attributes));
    }

    /**
     * @return array[]
     */
    public function inputFilterDataProvider(): array
    {
        return  [
            [
                [
                    'attribute_code' => 'category_ids',
                    'frontend_input' => '',
                    'options' => [],
                    'filter_options' => [],
                    'backend_type' => 'varchar',
                ],
                ['values' => ['category_ids' => '1']],
                [
                    'name' => 'export_filter[category_ids]',
                    'value' => '1',
                ]
            ],
        ];
    }

    /**
     * Test number filter
     *
     * @param array $attributeData
     * @param array $values
     * @param array $expect
     * @dataProvider numberFilterDataProvider
     */
    public function testNumberFilter(array $attributeData, array $values, array $expect): void
    {
        $this->layout->expects($this->never())
            ->method('createBlock');
        $attribute = $this->getAttributeMock($attributeData);
        $column = new DataObject();
        $column->addData($values);
        $isExport = true;
        $result = $this->filter->decorateFilter(null, $attribute, $column, $isExport);
        $this->assertStringContainsString($expect[0], $result);
        $this->assertStringContainsString($expect[1], $result);
    }

    /**
     * @return array[]
     */
    public function numberFilterDataProvider(): array
    {
        return [
            [
                [
                    'attribute_code' => 'cost',
                    'frontend_input' => '',
                    'options' => [],
                    'filter_options' => [],
                    'backend_type' => 'decimal',
                ],
                ['values' => ['cost' => ['3', '5']]],
                [
                    'value="3"',
                    'value="5"',
                ]
            ],
        ];
    }

    /**
     * @param array $data
     * @return Attribute
     */
    private function getAttributeMock(array $data): Attribute
    {
        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attribute->addData($data);

        return $attribute;
    }

    /**
     * Test for protected method prepareForm()
     *
     * @todo to implement it.
     */
    public function testPrepareForm()
    {
        $this->markTestSkipped('This test has not been implemented yet.');
    }
}
