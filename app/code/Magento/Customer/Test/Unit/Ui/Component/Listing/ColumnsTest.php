<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

    /** @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $indexerRegistry;

    /** @var \Magento\Framework\Indexer\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $indexer;

    /** @var \Magento\Framework\Indexer\StateInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $indexerState;

    /** @var Columns */
    protected $component;

    public function setUp()
    {
        $this->context = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false
        );
        $this->columnFactory = $this->getMock(
            'Magento\Customer\Ui\Component\ColumnFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->attributeRepository = $this->getMock(
            'Magento\Customer\Ui\Component\Listing\AttributeRepository',
            [],
            [],
            '',
            false
        );
        $this->attribute = $this->getMock(
            'Magento\Customer\Model\Attribute',
            [],
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
        $this->indexerRegistry = $this->getMock('Magento\Framework\Indexer\IndexerRegistry', [], [], '', false);
        $this->indexer = $this->getMockForAbstractClass(
            'Magento\Framework\Indexer\IndexerInterface',
            [],
            '',
            false
        );
        $this->indexerState = $this->getMockForAbstractClass(
            'Magento\Framework\Indexer\StateInterface',
            [],
            '',
            false
        );

        $this->component = new Columns(
            $this->context,
            $this->columnFactory,
            $this->attributeRepository,
            $this->indexerRegistry
        );
    }

    public function testPrepareWithAddColumn()
    {
        $attributeCode = 'attribute_code';

        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with('customer_grid')
            ->willReturn($this->indexer);
        $this->indexer->expects($this->once())
            ->method('getState')
            ->willReturn($this->indexerState);
        $this->indexerState
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn('valid');

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
        ];

        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with('customer_grid')
            ->willReturn($this->indexer);
        $this->indexer->expects($this->once())
            ->method('getState')
            ->willReturn($this->indexerState);
        $this->indexerState
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn('valid');
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
        $this->column->expects($this->once())
            ->method('setData')
            ->with(
                'config',
                [
                    'name' => $attributeCode,
                    'dataType' => $backendType,
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
            'frontend_input' => 'frontend-input',
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
        ];

        $this->indexerRegistry->expects($this->once())
            ->method('get')
            ->with('customer_grid')
            ->willReturn($this->indexer);
        $this->indexer->expects($this->once())
            ->method('getState')
            ->willReturn($this->indexerState);
        $this->indexerState
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn('valid');
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
        $this->column->expects($this->once())
            ->method('setData')
            ->with(
                'config',
                [
                    'visible' => true
                ]
            );

        $this->component->addColumn($attributeData, $attributeCode);
        $this->component->prepare();
    }
}
