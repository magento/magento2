<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing;

use Magento\Customer\Ui\Component\Listing\Columns;

class ColumnsTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Customer\Ui\Component\ColumnFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $columnFactory;

    /** @var \Magento\Customer\Ui\Component\Listing\AttributeRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeRepository;

    /** @var \Magento\Customer\Model\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $attribute;

    /** @var \Magento\Ui\Component\Listing\Columns\ColumnInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $column;

    /** @var \Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater|\PHPUnit_Framework_MockObject_MockObject */
    protected $inlineEditUpdater;

    /** @var Columns */
    protected $component;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        $this->columnFactory = $this->getMock(
            \Magento\Customer\Ui\Component\ColumnFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->attributeRepository = $this->getMock(
            \Magento\Customer\Ui\Component\Listing\AttributeRepository::class,
            [],
            [],
            '',
            false
        );
        $this->attribute = $this->getMock(
            \Magento\Customer\Model\Attribute::class,
            [],
            [],
            '',
            false
        );
        $this->column = $this->getMockForAbstractClass(
            \Magento\Ui\Component\Listing\Columns\ColumnInterface::class,
            [],
            '',
            false
        );

        $this->inlineEditUpdater = $this->getMockBuilder(
            \Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->component = new Columns(
            $this->context,
            $this->columnFactory,
            $this->attributeRepository,
            $this->inlineEditUpdater
        );
    }

    public function testPrepareWithAddColumn()
    {
        $attributeCode = 'attribute_code';

        $this->attributeRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn(
                [
                    $attributeCode => [
                        'attribute_code' => 'billing_attribute_code',
                        'frontend_input' => 'frontend-input',
                        'frontend_label' => 'frontend-label',
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
                        'validation_rules' => [],
                        'required'=> false,
                        'entity_type_code' => 'customer_address',
                    ]
                ]
            );
        $this->columnFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->column);
        $this->column->expects($this->once())
            ->method('prepare');

        $this->component->prepare();
    }

    public function testPrepareWithUpdateColumn()
    {
        $attributeCode = 'billing_attribute_code';
        $backendType = 'backend-type';
        $attributeData = [
            'attribute_code' => 'billing_attribute_code',
            'frontend_input' => 'text',
            'frontend_label' => 'frontend-label',
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
            'validation_rules' => [],
            'required'=> false,
            'entity_type_code' => 'customer',
        ];

        $this->attributeRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn([$attributeCode => $attributeData]);
        $this->columnFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->column);
        $this->column->expects($this->once())
            ->method('prepare');
        $this->column->expects($this->atLeastOnce())
            ->method('getData')
            ->with('config')
            ->willReturn([]);
        $this->column->expects($this->at(3))
            ->method('setData')
            ->with(
                'config',
                [
                    'options' => [
                        [
                            'label' => 'Label',
                            'value' => 'Value'
                        ]
                    ]
                ]
            );
        $this->column->expects($this->at(5))
            ->method('setData')
            ->with(
                'config',
                [
                    'name' => $attributeCode,
                    'dataType' => $backendType,
                    'filter' => 'text',
                    'visible' => true
                ]
            );

        $this->component->addColumn($attributeData, $attributeCode);
        $this->component->prepare();
    }

    public function testPrepareWithUpdateStaticColumn()
    {
        $attributeCode = 'billing_attribute_code';
        $backendType = 'static';
        $attributeData = [
            'attribute_code' => 'billing_attribute_code',
            'frontend_input' => 'text',
            'frontend_label' => 'frontend-label',
            'backend_type' => $backendType,
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
            'validation_rules' => [],
            'required'=> false,
            'entity_type_code' => 'customer',
        ];
        $this->inlineEditUpdater->expects($this->once())
            ->method('applyEditing')
            ->with($this->column, 'text', [], false);

        $this->attributeRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn([$attributeCode => $attributeData]);
        $this->columnFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->column);
        $this->column->expects($this->once())
            ->method('prepare');
        $this->column->expects($this->atLeastOnce())
            ->method('getData')
            ->with('config')
            ->willReturn([
                'editor' => 'text'
            ]);
        $this->column->expects($this->at(3))
            ->method('setData')
            ->with(
                'config',
                [
                    'editor' => 'text',
                    'options' => [
                        [
                            'label' => 'Label',
                            'value' => 'Value'
                        ]
                    ]
                ]
            );
        $this->column->expects($this->at(6))
            ->method('setData')
            ->with(
                'config',
                [
                    'editor' => 'text',
                    'visible' => true
                ]
            );

        $this->component->addColumn($attributeData, $attributeCode);
        $this->component->prepare();
    }
}
