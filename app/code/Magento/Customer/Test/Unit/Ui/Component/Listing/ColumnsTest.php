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

    /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeMetadata;

    /** @var \Magento\Ui\Component\Listing\Columns\ColumnInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $column;

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
        $this->attributeMetadata = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\AttributeMetadataInterface',
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

        $this->component = new Columns(
            $this->context,
            $this->columnFactory,
            $this->attributeRepository
        );
    }

    public function testPrepareWithAddColumn()
    {
        $attributeCode = 'attribute_code';

        $this->attributeRepository->expects($this->once())
            ->method('getList')
            ->willReturn(
                [
                    $attributeCode => $this->attributeMetadata
                ]
            );
        $this->attributeMetadata->expects($this->atLeastOnce())
            ->method('getBackendType')
            ->willReturn('backend-type');
        $this->attributeMetadata->expects($this->atLeastOnce())
            ->method('getIsUsedInGrid')
            ->willReturn(true);
        $this->attributeMetadata->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->columnFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->column);
        $this->column->expects($this->once())
            ->method('prepare');

        $this->component->prepare();
    }

    public function testPrepareWithUpdateColumn()
    {
        $attributeCode = 'attribute_code';
        $backendType = 'backend-type';

        $this->attributeRepository->expects($this->once())
            ->method('getList')
            ->willReturn(
                [
                    $attributeCode => $this->attributeMetadata
                ]
            );
        $this->attributeMetadata->expects($this->atLeastOnce())
            ->method('getBackendType')
            ->willReturn($backendType);
        $this->attributeMetadata->expects($this->atLeastOnce())
            ->method('getIsUsedInGrid')
            ->willReturn(true);
        $this->attributeMetadata->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->columnFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->column);
        $this->column->expects($this->once())
            ->method('prepare');
        $this->attributeMetadata->expects($this->once())
            ->method('getIsVisibleInGrid')
            ->willReturn(true);
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

        $this->component->addColumn($this->attributeMetadata, $attributeCode);
        $this->component->prepare();
    }

    public function testPrepareWithUpdateStaticColumn()
    {
        $attributeCode = 'attribute_code';
        $backendType = 'static';

        $this->attributeRepository->expects($this->once())
            ->method('getList')
            ->willReturn(
                [
                    $attributeCode => $this->attributeMetadata
                ]
            );
        $this->attributeMetadata->expects($this->atLeastOnce())
            ->method('getBackendType')
            ->willReturn($backendType);
        $this->attributeMetadata->expects($this->never())
            ->method('getIsUsedInGrid');
        $this->attributeMetadata->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->columnFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->column);
        $this->column->expects($this->once())
            ->method('prepare');
        $this->attributeMetadata->expects($this->once())
            ->method('getIsVisibleInGrid')
            ->willReturn(true);
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

        $this->component->addColumn($this->attributeMetadata, $attributeCode);
        $this->component->prepare();
    }
}
