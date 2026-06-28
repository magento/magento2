<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Test\Unit\Block;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\AvailabilityFlagInterface;
use Magento\Catalog\Model\Layer\FilterList;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\LayoutInterface;
use Magento\LayeredNavigation\Block\Navigation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class NavigationTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var MockObject
     */
    protected $catalogLayerMock;

    /**
     * @var MockObject
     */
    protected $filterListMock;

    /**
     * @var MockObject
     */
    protected $layoutMock;

    /**
     * @var MockObject
     */
    protected $visibilityFlagMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var Navigation
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->catalogLayerMock = $this->createMock(Layer::class);
        $this->filterListMock = $this->createMock(FilterList::class);
        $this->visibilityFlagMock = $this->createMock(AvailabilityFlagInterface::class);
        $this->visibilityFlagMock = $this->getMockForAbstractClass(AvailabilityFlagInterface::class);
        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRouteName'])
            ->getMock();

        /** @var MockObject|Resolver $layerResolver */
        $layerResolver = $this->createPartialMock(Resolver::class, ['get', 'create']);
        $layerResolver->expects($this->any())
            ->method($this->anything())
            ->willReturn($this->catalogLayerMock);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Navigation::class,
            [
                'layerResolver' => $layerResolver,
                'filterList' => $this->filterListMock,
                'visibilityFlag' => $this->visibilityFlagMock,
                '_request' => $this->requestMock
            ]
        );
        $this->layoutMock = $this->createMock(LayoutInterface::class);
    }

    /**
     * @return void
     */
    public function testGetStateHtml(): void
    {
        $stateHtml = 'I feel good';
        $this->filterListMock->expects($this->any())->method('getFilters')->willReturn([]);
        $this->layoutMock
            ->method('getChildName')
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
     *
     * @return void
     */
    public function testCanShowBlock(): void
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
     * @return void
     */
    #[DataProvider('canShowBlockDataProvider')]
    public function testCanShowBlockWithDifferentDisplayModes(string $mode, string $routeName, bool $result): void
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
        $this->requestMock->expects($this->any())->method('getRouteName')->willReturn($routeName);

        $category->expects($this->once())->method('getDisplayMode')->willReturn($mode);
        $this->assertEquals($result, $this->model->canShowBlock());
    }

    /**
     * @return array
     */
    public static function canShowBlockDataProvider(): array
    {
        return [
            [
                Category::DM_PRODUCT,
                'catalog',
                true
            ],
            [
                Category::DM_PAGE,
                'catalog',
                false
            ],
            [
                Category::DM_MIXED,
                'catalog',
                true
            ],
            [
                Category::DM_PRODUCT,
                'catalogsearch',
                true
            ],
            [
                Category::DM_PAGE,
                'catalogsearch',
                true
            ],
            [
                Category::DM_MIXED,
                'catalogsearch',
                true
            ],
        ];
    }

    /**
     * @return void
     */
    public function testGetClearUrl(): void
    {
        $this->filterListMock->expects($this->any())->method('getFilters')->willReturn([]);
        $this->model->setLayout($this->layoutMock);
        $this->layoutMock->expects($this->once())->method('getChildName')->willReturn('sample block');

        $blockMock = $this->createPartialMockWithReflection(AbstractBlock::class, ['getClearUrl']);
        $clearUrl = 'very clear URL';
        $blockMock->method('getClearUrl')->willReturn($clearUrl);

        $this->layoutMock->expects($this->once())->method('getBlock')->willReturn($blockMock);
        $this->assertEquals($clearUrl, $this->model->getClearUrl());
    }
}
