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
        $attributeCode = 'attribute-code';

        $this->attributeRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn([$attributeCode => $this->attributeMetadata]);
        $this->attributeMetadata->expects($this->once())
            ->method('getBackendType')
            ->willReturn('backend-type');
        $this->attributeMetadata->expects($this->once())
            ->method('getIsUsedInGrid')
            ->willReturn(true);
        $this->attributeMetadata->expects($this->once())
            ->method('getIsFilterableInGrid')
            ->willReturn(true);
        $this->attributeMetadata->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->filterFactory->expects($this->once())
            ->method('create')
            ->with($attributeCode, $this->attributeMetadata, $this->context)
            ->willReturn($this->filter);
        $this->filter->expects($this->once())
            ->method('prepare');

        $this->component->prepare();
        $this->assertSame($this->filter, $this->component->getComponent($attributeCode));
    }

    public function testPrepareWithAlreadyAddedComponent()
    {
        $attributeCode = 'attribute-code';
        $this->component->addComponent($attributeCode, $this->filter);

        $this->attributeRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn([$attributeCode => $this->attributeMetadata]);
        $this->attributeMetadata->expects($this->once())
            ->method('getIsUsedInGrid')
            ->willReturn(true);
        $this->attributeMetadata->expects($this->once())
            ->method('getIsFilterableInGrid')
            ->willReturn(false);

        $this->component->prepare();
        $this->assertEquals(null, $this->component->getComponent($attributeCode));
    }
}
