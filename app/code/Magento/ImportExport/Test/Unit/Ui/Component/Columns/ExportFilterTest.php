<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Ui\Component\Columns;

use Exception;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\AttributeFilterType;
use Magento\ImportExport\Ui\Component\Columns\ExportFilter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExportFilterTest extends TestCase
{
    /**
     * @var string
     */
    private $name = 'filter';

    /**
     * @var MockObject
     */
    private $context;

    /**
     * @var MockObject
     */
    private $attributeFilterType;

    /**
     * @var ExportFilter
     */
    private $component;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->attributeFilterType = $this->createMock(AttributeFilterType::class);

        $objectManager = new ObjectManager($this);
        $this->component = $objectManager->getObject(ExportFilter::class, [
            'context' => $this->context,
            'attributeFilterType' => $this->attributeFilterType,
            'data' => [
                'name' => $this->name
            ]
        ]);
    }

    /**
     * @param array $dataSource
     * @param array $attrDescription
     * @param array $expected
     * @dataProvider prepareDataSourceDataProvider
     */
    public function testPrepareDataSource(
        array $dataSource,
        array $attrDescription,
        array $expected
    ) {
        $source = $this->createConfiguredMock(SourceInterface::class, [
            'getAllOptions' => $attrDescription['options']
        ]);
        $attribute = $this->createConfiguredMock(Attribute::class, [
            'usesSource' => $attrDescription['usesSource'],
            'getSource' => $source
        ]);
        $collection = $this->createConfiguredMock(AbstractCollection::class, [
            'getItemById' => $attribute
        ]);
        $dataProvider = $this->createConfiguredMock(AbstractDataProvider::class, [
            'getCollection' => $collection
        ]);

        $this->context->method('getDataProvider')->willReturn($dataProvider);

        $getAttributeFilterType = $this->attributeFilterType->method('getAttributeFilterType');
        if ($attrDescription['type'] instanceof Exception) {
            $getAttributeFilterType->willThrowException($attrDescription['type']);
        } else {
            $getAttributeFilterType->willReturn($attrDescription['type']);
        }

        $this->assertEquals($expected, $this->component->prepareDataSource($dataSource));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function prepareDataSourceDataProvider() :array
    {
        return [
            [
                'dataSource' => [
                    'data' => [
                        'items' => [
                            [
                                'attribute_id' => 1
                            ]
                        ]
                    ]
                ],
                'attrDescription' => [
                    'type' => new LocalizedException(__('We can\'t determine the attribute filter type.')),
                    'usesSource' => false,
                    'options' => []
                ],
                'expected' => [
                    'data' => [
                        'items' => [
                            [
                                'attribute_id' => 1,
                                $this->name => __('We can\'t determine the attribute filter type.')
                            ]
                        ]
                    ]
                ]
            ],
            [
                'dataSource' => [
                    'data' => [
                        'items' => [
                            [
                                'attribute_id' => 2
                            ]
                        ]
                    ]
                ],
                'attrDescription' => [
                    'type' => Export::FILTER_TYPE_INPUT,
                    'usesSource' => false,
                    'options' => []
                ],
                'expected' => [
                    'data' => [
                        'items' => [
                            [
                                'attribute_id' => 2,
                                $this->name => [
                                    'type' => Export::FILTER_TYPE_INPUT
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'dataSource' => [
                    'data' => [
                        'items' => [
                            [
                                'attribute_id' => 3
                            ]
                        ]
                    ]
                ],
                'attrDescription' => [
                    'type' => Export::FILTER_TYPE_SELECT,
                    'usesSource' => true,
                    'options' => [
                        [
                            'value' => '',
                            'label' => ''
                        ]
                    ]
                ],
                'expected' => [
                    'data' => [
                        'items' => [
                            [
                                'attribute_id' => 3,
                                $this->name => __('We can\'t filter an attribute with no attribute options.')
                            ]
                        ]
                    ]
                ]
            ],
            [
                'dataSource' => [
                    'data' => [
                        'items' => [
                            [
                                'attribute_id' => 4
                            ]
                        ]
                    ]
                ],
                'attrDescription' => [
                    'type' => Export::FILTER_TYPE_SELECT,
                    'usesSource' => true,
                    'options' => [
                        [
                            'value' => '',
                            'label' => ''
                        ],
                        [
                            'value' => 'Value',
                            'label' => 'Label'
                        ]
                    ]
                ],
                'expected' => [
                    'data' => [
                        'items' => [
                            [
                                'attribute_id' => 4,
                                $this->name => [
                                    'type' => Export::FILTER_TYPE_SELECT,
                                    'options' => [
                                        [
                                            'value' => 'Value',
                                            'label' => 'Label'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
