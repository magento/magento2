<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Ui\Component\Listing\Column\AttributeColumn;

class AttributeColumnTest extends \PHPUnit\Framework\TestCase
{
    /** @var AttributeColumn */
    protected $component;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $context;

    /** @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $uiComponentFactory;

    /** @var \Magento\Customer\Ui\Component\Listing\AttributeRepository|\PHPUnit\Framework\MockObject\MockObject */
    protected $attributeRepository;

    /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $attributeMetadata;

    protected function setup(): void
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->createMock(\Magento\Framework\View\Element\UiComponentFactory::class);
        $this->attributeRepository = $this->createMock(
            \Magento\Customer\Ui\Component\Listing\AttributeRepository::class
        );
        $this->attributeMetadata = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AttributeMetadataInterface::class,
            [],
            '',
            false
        );

        $this->component = new AttributeColumn(
            $this->context,
            $this->uiComponentFactory,
            $this->attributeRepository
        );
        $this->component->setData('name', 'gender');
    }

    public function testPrepareDataSource()
    {
        $genderOptionId = 1;
        $genderOptionLabel = 'Male';

        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'name' => 'testName'
                    ],
                    [
                        'gender' => $genderOptionId
                    ]
                ]
            ]
        ];
        $expectedSource = [
            'data' => [
                'items' => [
                    [
                        'name' => 'testName'
                    ],
                    [
                        'gender' => $genderOptionLabel
                    ]
                ]
            ]
        ];

        $this->attributeRepository->expects($this->once())
            ->method('getMetadataByCode')
            ->with('gender')
            ->willReturn([
                'attribute_code' => 'billing_attribute_code',
                'frontend_input' => 'frontend-input',
                'frontend_label' => 'frontend-label',
                'backend_type' => 'backend-type',
                'options' => [
                    [
                        'label' => $genderOptionLabel,
                        'value' => $genderOptionId
                    ]
                ],
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
            ]);

        $dataSource = $this->component->prepareDataSource($dataSource);

        $this->assertEquals($expectedSource, $dataSource);
    }
}
