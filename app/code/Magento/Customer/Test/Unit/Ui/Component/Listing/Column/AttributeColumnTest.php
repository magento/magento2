<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Customer\Ui\Component\Listing\Column\AttributeColumn;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeColumnTest extends TestCase
{
    /** @var AttributeColumn */
    protected $component;

    /** @var ContextInterface|MockObject */
    protected $context;

    /** @var UiComponentFactory|MockObject */
    protected $uiComponentFactory;

    /** @var AttributeRepository|MockObject */
    protected $attributeRepository;

    /** @var AttributeMetadataInterface|MockObject */
    protected $attributeMetadata;

    protected function setup(): void
    {
        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->never())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->createMock(UiComponentFactory::class);
        $this->attributeRepository = $this->createMock(
            AttributeRepository::class
        );
        $this->attributeMetadata = $this->getMockForAbstractClass(
            AttributeMetadataInterface::class,
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
