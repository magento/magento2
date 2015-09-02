<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component;

use Magento\Customer\Ui\Component\ColumnFactory;

class ColumnFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Api\Data\OptionInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeOption;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $componentFactory;

    /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeMetadata;

    /** @var \Magento\Ui\Component\Listing\Columns\ColumnInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $column;

    /** @var ColumnFactory */
    protected $columnFactory;

    public function setUp()
    {
        $this->context = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false
        );
        $this->componentFactory = $this->getMock(
            'Magento\Framework\View\Element\UiComponentFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->attributeMetadata = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\AttributeMetadataInterface',
            [],
            '',
            false
        );
        $this->column = $this->getMockForAbstractClass(
            'Magento\Ui\Component\Listing\Columns\ColumnInterface',
            [],
            '',
            false
        );
        $this->attributeOption = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\OptionInterface',
            [],
            '',
            false
        );

        $this->columnFactory = new ColumnFactory($this->componentFactory);
    }

    public function testCreate()
    {
        $columnName = 'created_at';
        $config = [
            'data' => [
                'js_config' => [
                    'component' => 'Magento_Ui/js/grid/columns/column',
                ],
                'config' => [
                    'label' => __('Label'),
                    'dataType' => 'text',
                    'editor' => 'text',
                    'align' => 'left',
                    'visible' => true,
                    'options' =>  [
                        [
                            'label' => 'Label',
                            'value' => 'Value'
                        ]
                    ]
                ],
            ],
            'context' => $this->context,
        ];
        $attributeData = [
            'attribute_code' => 'billing_attribute_code',
            'frontend_input' => 'frontend-input',
            'frontend_label' => 'Label',
            'backend_type' => 'backend-type',
            'options' => [
                [
                    'label' => 'Label',
                    'value' => 'Value'
                ]
            ],
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => true,
        ];

        $this->componentFactory->expects($this->once())
            ->method('create')
            ->with($columnName, 'column', $config)
            ->willReturn($this->column);

        $this->assertSame(
            $this->column,
            $this->columnFactory->create($attributeData, $columnName, $this->context)
        );
    }
}
