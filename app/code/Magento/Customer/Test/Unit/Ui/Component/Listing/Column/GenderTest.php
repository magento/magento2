<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Ui\Component\Listing\Column\Gender;

class GenderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Gender */
    protected $component;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $uiComponentFactory;

    /** @var CustomerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerMetadata;

    /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeMetadata;

    /** @var \Magento\Customer\Api\Data\OptionInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $genderOption;

    public function setup()
    {
        $this->context = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false
        );
        $this->uiComponentFactory = $this->getMock(
            'Magento\Framework\View\Element\UiComponentFactory',
            [],
            [],
            '',
            false
        );
        $this->customerMetadata = $this->getMockForAbstractClass(
            'Magento\Customer\Api\CustomerMetadataInterface',
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
        $this->genderOption = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\OptionInterface',
            [],
            '',
            false
        );

        $this->component = new Gender(
            $this->context,
            $this->uiComponentFactory,
            $this->customerMetadata
        );
        $this->component->setData('name', 'gender');
    }

    public function testPrepareDataSourceWithoutItems()
    {
        $dataSource = [
            'data' => [

            ]
        ];
        $this->customerMetadata->expects($this->never())
            ->method('getAttributeMetadata')
            ->with('gender');

        $this->component->prepareDataSource($dataSource);
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


        $this->customerMetadata->expects($this->once())
            ->method('getAttributeMetadata')
            ->with('gender')
            ->willReturn($this->attributeMetadata);
        $this->attributeMetadata->expects($this->once())
            ->method('getOptions')
            ->willReturn([$this->genderOption]);
        $this->genderOption->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $this->genderOption->expects($this->once())
            ->method('getValue')
            ->willReturn($genderOptionId);
        $this->genderOption->expects($this->once())
            ->method('getLabel')
            ->willReturn($genderOptionLabel);

        $this->component->prepareDataSource($dataSource);

        $this->assertEquals($expectedSource, $dataSource);
    }
}
