<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component;

use Magento\Customer\Ui\Component\FilterFactory;

class FilterFactoryTest extends \PHPUnit\Framework\TestCase
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
    protected $filter;

    /** @var FilterFactory */
    protected $filterFactory;

    protected function setUp()
    {
        $this->context = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            '',
            false
        );
        $this->componentFactory = $this->createPartialMock(
            \Magento\Framework\View\Element\UiComponentFactory::class,
            ['create']
        );
        $this->attributeMetadata = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AttributeMetadataInterface::class,
            [],
            '',
            false
        );
        $this->filter = $this->getMockForAbstractClass(
            \Magento\Ui\Component\Listing\Columns\ColumnInterface::class,
            [],
            '',
            false
        );
        $this->attributeOption = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\OptionInterface::class,
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
                    '__disableTmpl' => 'true',
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
