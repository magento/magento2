<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Ui\Component\Filters;

/**
 * Unit tests for \Magento\Ui\Component\Filters class
 */
class FiltersTest extends \PHPUnit\Framework\TestCase
{
    /** @var Filters|\PHPUnit\Framework\MockObject\MockObject */
    private $filters;

    /** @var \Magento\Framework\View\Element\UiComponentInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $uiComponentInterface;

    /** @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit\Framework\MockObject\MockObject */
    private $uiComponentFactory;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $context;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->uiComponentInterface = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->uiComponentFactory = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filters = $objectManager->getObject(
            Filters::class,
            [
                'columnFilters' => ['select' => $this->uiComponentInterface],
                'uiComponentFactory' => $this->uiComponentFactory,
                'context' => $this->context,
            ]
        );
    }

    public function testUpdate()
    {
        $componentName = 'component_name';
        $componentConfig = [0, 1, 2];
        $columnInterface = $this->getMockBuilder(\Magento\Ui\Component\Listing\Columns\ColumnInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'getName', 'getConfiguration'])
            ->getMockForAbstractClass();
        $columnInterface->expects($this->atLeastOnce())->method('getData')->with('config/filter')->willReturn('text');
        $columnInterface->expects($this->atLeastOnce())->method('getName')->willReturn($componentName);
        $columnInterface->expects($this->once())->method('getConfiguration')->willReturn($componentConfig);
        $filterComponent = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData', 'prepare'])
            ->getMockForAbstractClass();
        $filterComponent->expects($this->once())->method('setData')->with('config', $componentConfig)
            ->willReturnSelf();
        $filterComponent->expects($this->once())->method('prepare')->willReturnSelf();
        $this->uiComponentFactory->expects($this->once())->method('create')
            ->with($componentName, 'filterInput', ['context' => $this->context])
            ->willReturn($filterComponent);

        $this->filters->update($columnInterface);
        /** Verify that filter is already set and it wouldn't be set again */
        $this->filters->update($columnInterface);
    }
}
