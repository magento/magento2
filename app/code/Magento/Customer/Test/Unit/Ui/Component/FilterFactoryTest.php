<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component;

use Magento\Customer\Ui\Component\FilterFactory;

class FilterFactoryTest extends \PHPUnit_Framework_TestCase
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
        $this->filter = $this->getMockForAbstractClass(
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
        $this->attributeMetadata->expects($this->atLeastOnce())
            ->method('getOptions')
            ->willReturn([$this->attributeOption]);
        $this->attributeMetadata->expects($this->atLeastOnce())
            ->method('getFrontendLabel')
            ->willReturn('Label');
        $this->componentFactory->expects($this->once())
            ->method('create')
            ->with($filterName, 'filterInput', $config)
            ->willReturn($this->filter);
        $this->attributeOption->expects($this->once())
            ->method('getValue')
            ->willReturn('Value');
        $this->attributeOption->expects($this->once())
            ->method('getLabel')
            ->willReturn('Label');

        $this->assertSame(
            $this->filter,
            $this->filterFactory->create($filterName, $this->attributeMetadata, $this->context)
        );
    }
}
