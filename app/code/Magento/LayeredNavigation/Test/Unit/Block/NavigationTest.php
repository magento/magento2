<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\LayeredNavigation\Test\Unit\Block;

use Magento\Catalog\Model\Category;

class NavigationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $catalogLayerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $filterListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $visibilityFlagMock;

    /**
     * @var \Magento\LayeredNavigation\Block\Navigation
     */
    protected $model;

    protected function setUp(): void
    {
        $this->catalogLayerMock = $this->createMock(\Magento\Catalog\Model\Layer::class);
        $this->filterListMock = $this->createMock(\Magento\Catalog\Model\Layer\FilterList::class);
        $this->visibilityFlagMock = $this->createMock(\Magento\Catalog\Model\Layer\AvailabilityFlagInterface::class);

        /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Layer\Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->willReturn($this->catalogLayerMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\LayeredNavigation\Block\Navigation::class,
            [
                'layerResolver' => $layerResolver,
                'filterList' => $this->filterListMock,
                'visibilityFlag' => $this->visibilityFlagMock
            ]
        );
        $this->layoutMock = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
    }

    public function testGetStateHtml()
    {
        $stateHtml = 'I feel good';
        $this->filterListMock->expects($this->any())->method('getFilters')->willReturn([]);
        $this->layoutMock->expects($this->at(0))->method('getChildName')
            ->with(null, 'state')
            ->willReturn('state block');

        $this->layoutMock->expects($this->once())->method('renderElement')
            ->with('state block', true)
            ->willReturn($stateHtml);

        $this->model->setLayout($this->layoutMock);
        $this->assertEquals($stateHtml, $this->model->getStateHtml());
    }

    /**
     * @covers \Magento\LayeredNavigation\Block\Navigation::getLayer()
     * @covers \Magento\LayeredNavigation\Block\Navigation::getFilters()
     * @covers \Magento\LayeredNavigation\Block\Navigation::canShowBlock()
     */
    public function testCanShowBlock()
    {
        // getFilers()
        $filters = ['To' => 'be', 'or' => 'not', 'to' => 'be'];

        $this->filterListMock->expects($this->exactly(2))->method('getFilters')
            ->with($this->catalogLayerMock)
            ->willReturn($filters);
        $this->assertEquals($filters, $this->model->getFilters());

        // canShowBlock()
        $enabled = true;
        $this->visibilityFlagMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with($this->catalogLayerMock, $filters)
            ->willReturn($enabled);

        $category = $this->createMock(Category::class);
        $this->catalogLayerMock->expects($this->atLeastOnce())->method('getCurrentCategory')->willReturn($category);
        $category->expects($this->once())->method('getDisplayMode')->willReturn(Category::DM_PRODUCT);

        $this->assertEquals($enabled, $this->model->canShowBlock());
    }

    /**
     * Test canShowBlock() with different category display types.
     *
     * @param string $mode
     * @param bool $result
     *
     * @dataProvider canShowBlockDataProvider
     */
    public function testCanShowBlockWithDifferentDisplayModes(string $mode, bool $result)
    {
        $filters = ['To' => 'be', 'or' => 'not', 'to' => 'be'];

        $this->filterListMock->expects($this->atLeastOnce())->method('getFilters')
            ->with($this->catalogLayerMock)
            ->willReturn($filters);
        $this->assertEquals($filters, $this->model->getFilters());

        $this->visibilityFlagMock
            ->expects($this->any())
            ->method('isEnabled')
            ->with($this->catalogLayerMock, $filters)
            ->willReturn(true);

        $category = $this->createMock(Category::class);
        $this->catalogLayerMock->expects($this->atLeastOnce())->method('getCurrentCategory')->willReturn($category);
        $category->expects($this->once())->method('getDisplayMode')->willReturn($mode);
        $this->assertEquals($result, $this->model->canShowBlock());
    }

    /**
     * @return array
     */
    public function canShowBlockDataProvider()
    {
        return [
            [
                Category::DM_PRODUCT,
                true,
            ],
            [
                Category::DM_PAGE,
                false,
            ],
            [
                Category::DM_MIXED,
                true,
            ],
        ];
    }

    public function testGetClearUrl()
    {
        $this->filterListMock->expects($this->any())->method('getFilters')->willReturn([]);
        $this->model->setLayout($this->layoutMock);
        $this->layoutMock->expects($this->once())->method('getChildName')->willReturn('sample block');

        $blockMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\AbstractBlock::class,
            [],
            '',
            false
        );
        $clearUrl = 'very clear URL';
        $blockMock->setClearUrl($clearUrl);

        $this->layoutMock->expects($this->once())->method('getBlock')->willReturn($blockMock);
        $this->assertEquals($clearUrl, $this->model->getClearUrl());
    }
}
