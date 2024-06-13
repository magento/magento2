<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Ui\Component\Filters class
 */
class FiltersTest extends TestCase
{
    /** @var Filters|MockObject */
    private $filters;

    /** @var UiComponentInterface|MockObject */
    private $uiComponentInterface;

    /** @var UiComponentFactory|MockObject */
    private $uiComponentFactory;

    /** @var ContextInterface|MockObject */
    private $context;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->uiComponentInterface = $this->getMockBuilder(UiComponentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->uiComponentFactory = $this->getMockBuilder(UiComponentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->filters = $objectManager->getObject(
            Filters::class,
            [
                'columnFilters' => ['select' => $this->uiComponentInterface],
                'uiComponentFactory' => $this->uiComponentFactory,
                'context' => $this->context,
            ]
        );
    }

    /**
     * Test to Update filter component according to $component
     *
     * @param string $filterType
     * @param string $filterName
     * @param bool $userDefined
     * @dataProvider updateDataProvider
     */
    public function testUpdate(string $filterType, string $filterName, bool $userDefined)
    {
        $componentName = 'component_name';
        $componentConfig = [0, 1, 2];
        $columnInterface = $this->getMockBuilder(ColumnInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData', 'getName', 'getConfiguration'])
            ->getMockForAbstractClass();
        $columnInterface->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturnOnConsecutiveCalls(
                $filterType,
                ['userDefined' => $userDefined],
                $filterType,
                ['userDefined' => $userDefined]
            );
        $columnInterface->expects($this->atLeastOnce())->method('getName')->willReturn($componentName);
        $columnInterface->expects($this->once())->method('getConfiguration')->willReturn($componentConfig);
        $filterComponent = $this->getMockBuilder(UiComponentInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setData', 'prepare'])
            ->getMockForAbstractClass();
        $filterComponent->expects($this->once())->method('setData')->with('config', $componentConfig)
            ->willReturnSelf();
        $filterComponent->expects($this->once())->method('prepare')->willReturnSelf();
        $this->uiComponentFactory->expects($this->once())->method('create')
            ->with($componentName, $filterName, ['context' => $this->context, 'userDefined' => $userDefined])
            ->willReturn($filterComponent);

        $this->filters->update($columnInterface);
        /** Verify that filter is already set and it wouldn't be set again */
        $this->filters->update($columnInterface);
    }

    /**
     * @return array
     */
    public static function updateDataProvider(): array
    {
        return [
            ['text', 'filterInput', false],
            ['datetimeRange', 'filterDate', true],
        ];
    }
}
