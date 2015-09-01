<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing;

use Magento\Customer\Ui\Component\Listing\Filters;

class FiltersTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Customer\Ui\Component\FilterFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $filterFactory;

    /** @var \Magento\Customer\Ui\Component\Listing\AttributeRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeRepository;

    /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeMetadata;

    /** @var \Magento\Ui\Component\Listing\Columns\ColumnInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $filter;

    /** @var Filters */
    protected $component;

    public function setUp()
    {
        $this->context = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false
        );
        $this->filterFactory = $this->getMock(
            'Magento\Customer\Ui\Component\FilterFactory',
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
        $this->attributeMetadata = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\AttributeMetadataInterface',
            [],
            '',
            false
        );
        $this->filter = $this->getMockForAbstractClass(
            'Magento\Ui\Component\Listing\Columns\ColumnInterface',
            [],
            '',
            false
        );

        $this->component = new Filters(
            $this->context,
            $this->filterFactory,
            $this->attributeRepository
        );
    }

    public function testPrepare()
    {
        $attributeCode = 'billing_attribute_code';
        $attributeData = [
            'attribute_code' => $attributeCode,
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

        $this->attributeRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn([$attributeCode => $attributeData]);
        $this->filterFactory->expects($this->once())
            ->method('create')
            ->with($attributeData, $this->context)
            ->willReturn($this->filter);
        $this->filter->expects($this->once())
            ->method('prepare');

        $this->component->prepare();
        $this->assertSame($this->filter, $this->component->getComponent($attributeCode));
    }

    public function testPrepareWithAlreadyAddedComponent()
    {
        $attributeCode = 'billing_attribute_code';
        $attributeData = [
            'attribute_code' => $attributeCode,
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
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => true,
        ];

        $this->component->addComponent($attributeCode, $this->filter);

        $this->attributeRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn([$attributeCode => $attributeData]);

        $this->component->prepare();
        $this->assertEquals(null, $this->component->getComponent($attributeCode));
    }
}
