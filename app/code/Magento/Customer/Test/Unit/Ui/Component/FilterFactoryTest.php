<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Ui\Component\FilterFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test FilterFactory Class
 */
class FilterFactoryTest extends TestCase
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
    protected $filter;

    /** @var FilterFactory */
    protected $filterFactory;

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
        $this->filter = $this->getMockForAbstractClass(
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

        $this->filterFactory = new FilterFactory($this->componentFactory);
    }

    public function testCreate()
    {
        $filterName = 'created_at';
        $config = [
            'data' => [
                'config' => [
                    'dataScope' => $filterName,
                    'label' => __('Label'),
                    'options' => [['value' => 'Value', 'label' => 'Label']],
                    'caption' => __('Select...'),
                ],
            ],
            'context' => $this->context,
        ];
        $attributeData = [
            'attribute_code' => $filterName,
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
            ->with($filterName, 'filterInput', $config)
            ->willReturn($this->filter);

        $this->assertSame(
            $this->filter,
            $this->filterFactory->create($attributeData, $this->context)
        );
    }
}
