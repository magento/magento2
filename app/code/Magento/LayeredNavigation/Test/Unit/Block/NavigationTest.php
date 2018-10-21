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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogLayerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $visibilityFlagMock;

    /**
     * @var \Magento\LayeredNavigation\Block\Navigation
     */
    protected $model;

    protected function setUp()
    {
        $this->catalogLayerMock = $this->createMock(\Magento\Catalog\Model\Layer::class);
        $this->filterListMock = $this->createMock(\Magento\Catalog\Model\Layer\FilterList::class);
        $this->visibilityFlagMock = $this->createMock(\Magento\Catalog\Model\Layer\AvailabilityFlagInterface::class);

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Layer\Resolver $layerResolver */
        $layerResolver = $this->getMockBuilder(\Magento\Catalog\Model\Layer\Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create'])
            ->getMock();
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->will($this->returnValue($this->catalogLayerMock));

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
        $this->filterListMock->expects($this->any())->method('getFilters')->will($this->returnValue([]));
        $this->layoutMock->expects($this->at(0))->method('getChildName')
            ->with(null, 'state')
            ->will($this->returnValue('state block'));

        $this->layoutMock->expects($this->once())->method('renderElement')
            ->with('state block', true)
            ->will($this->returnValue($stateHtml));

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
            ->will($this->returnValue($filters));
        $this->assertEquals($filters, $this->model->getFilters());

        // canShowBlock()
        $enabled = true;
        $this->visibilityFlagMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with($this->catalogLayerMock, $filters)
            ->will($this->returnValue($enabled));

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
            ->will($this->returnValue($filters));
        $this->assertEquals($filters, $this->model->getFilters());

        $this->visibilityFlagMock
            ->expects($this->any())
            ->method('isEnabled')
            ->with($this->catalogLayerMock, $filters)
            ->will($this->returnValue(true));

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
        $this->filterListMock->expects($this->any())->method('getFilters')->will($this->returnValue([]));
        $this->model->setLayout($this->layoutMock);
        $this->layoutMock->expects($this->once())->method('getChildName')->will($this->returnValue('sample block'));

        $blockMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\AbstractBlock::class,
            [],
            '',
            false
        );
        $clearUrl = 'very clear URL';
        $blockMock->setClearUrl($clearUrl);

        $this->layoutMock->expects($this->once())->method('getBlock')->will($this->returnValue($blockMock));
        $this->assertEquals($clearUrl, $this->model->getClearUrl());
    }
}
