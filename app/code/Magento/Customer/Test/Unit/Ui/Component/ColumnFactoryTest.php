<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Ui\Component\ColumnFactory;
use Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test ColumnFactory Class
 */
class ColumnFactoryTest extends TestCase
{
    /** @var OptionInterface|MockObject */
    protected $attributeOption;

    /** @var ContextInterface|MockObject */
    protected $context;

    /** @var UiComponentFactory|MockObject */
    protected $componentFactory;

    /** @var AttributeMetadataInterface|MockObject */
    protected $attributeMetadata;

    /** @var ColumnInterface|MockObject */
    protected $column;

    /** @var InlineEditUpdater|MockObject */
    protected $inlineEditUpdater;

    /** @var ColumnFactory */
    protected $columnFactory;

    protected function setUp(): void
    {
        $this->context = $this->getMockForAbstractClass(
            ContextInterface::class,
            [],
            '',
            false
        );
        $this->componentFactory = $this->createPartialMock(
            UiComponentFactory::class,
            ['create']
        );
        $this->attributeMetadata = $this->getMockForAbstractClass(
            AttributeMetadataInterface::class,
            [],
            '',
            false
        );
        $this->column = $this->getMockForAbstractClass(
            ColumnInterface::class,
            [],
            '',
            false
        );
        $this->attributeOption = $this->getMockForAbstractClass(
            OptionInterface::class,
            [],
            '',
            false
        );

        $this->inlineEditUpdater = $this->getMockBuilder(
            InlineEditUpdater::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->columnFactory = new ColumnFactory($this->componentFactory, $this->inlineEditUpdater);
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
                    'align' => 'left',
                    'visible' => true,
                    'options' =>  [
                        [
                            'label' => 'Label',
                            'value' => 'Value'
                        ]
                    ],
                    'component' => 'Magento_Ui/js/grid/columns/column',
                ],
            ],
            'context' => $this->context,
        ];
        $attributeData = [
            'attribute_code' => 'billing_attribute_code',
            'frontend_input' => 'text',
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
            'entity_type_code' => 'customer',
            'validation_rules' => [],
            'required' => false,
        ];
        $this->inlineEditUpdater->expects($this->once())
            ->method('applyEditing')
            ->with($this->column, 'text', []);
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
