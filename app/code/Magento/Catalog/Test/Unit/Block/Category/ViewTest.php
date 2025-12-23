<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Category;

use Magento\Catalog\Block\Breadcrumbs;
use Magento\Catalog\Block\Category\View;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Layer\State;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Category View Block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @covers \Magento\Catalog\Block\Category\View
 */
class ViewTest extends TestCase
{
    /**
     * @var View
     */
    private View $block;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var CatalogData|MockObject
     */
    private $catalogData;

    /**
     * Set up the test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $context = $this->createMock(Context::class);
        $this->config = $this->createMock(Config::class);
        $context->expects($this->once())->method('getPageConfig')
            ->willReturn($this->config);
        $layerResolver = $this->createMock(LayerResolver::class);
        $this->registry = $this->createMock(Registry::class);
        $categoryHelper =  $this->createMock(CategoryHelper::class);
        $this->catalogData = $this->createMock(CatalogData::class);

        $this->block = new View(
            $context,
            $layerResolver,
            $this->registry,
            $categoryHelper,
            [],
            $this->catalogData
        );
    }

    /**
     * Unit test for getIdentities() method
     *
     * @covers \Magento\Catalog\Block\Category\View::getIdentities()
     * @return void
     */
    public function testGetIdentities()
    {
        $categoryTag = ['catalog_category_1'];
        $currentCategoryMock = $this->createMock(Category::class);
        $currentCategoryMock->expects($this->once())->method('getIdentities')->willReturn($categoryTag);
        $this->block->setCurrentCategory($currentCategoryMock);
        $this->assertSame($categoryTag, $this->block->getIdentities());
    }

    /**
     * Test breadcrumbs generation
     *
     * @covers \Magento\Catalog\Block\Category\View::_prepareLayout()
     * @return void
     */
    public function testBreadcrumbs()
    {
        $layoutMock = $this->createMock(LayoutInterface::class);
        $breadCrumbs = $this->createMock(Breadcrumbs::class);
        $title = $this->createMock(Title::class);
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->addMethods(['getMetaTitle'])
            ->getMock();

        $breadCrumbs->expects($this->once())
            ->method('getTitleSeparator')
            ->willReturn(' - ');

        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->willReturn($breadCrumbs);

        $category->expects($this->once())
            ->method('getMetaTitle')
            ->willReturn(null);
        $this->registry->expects($this->once())
            ->method('registry')
            ->willReturn($category);

        $title->expects($this->once())
            ->method('set')
            ->willReturnSelf();
        $this->config->expects($this->once())
            ->method('getTitle')
            ->willReturn($title);

        $this->catalogData->expects($this->once())
            ->method('getBreadcrumbPath')
            ->willReturn([['label' => 'label1'], ['label' => 'label2']]);

        $result = $this->block->setLayout($layoutMock);
        $this->assertSame($result, $this->block);
    }

    /**
     * Test _prepareLayout() with Meta title, description and keywords set in category
     *
     * @covers \Magento\Catalog\Block\Category\View::_prepareLayout()
     * @return void
     */
    public function testPrepareLayoutWithSeoFields(): void
    {
        $layoutMock = $this->createMock(LayoutInterface::class);
        $breadCrumbs = $this->createMock(Breadcrumbs::class);
        $title = $this->createMock(Title::class);
        $abstractBlockMock = $this->createMock(AbstractBlock::class);
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->addMethods(['getMetaTitle', 'getMetaDescription', 'getMetaKeywords'])
            ->getMock();

        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->willReturn($breadCrumbs);
        $this->registry->expects($this->once())
            ->method('registry')
            ->willReturn($category);
        $category->expects($this->once())
            ->method('getMetaTitle')
            ->willReturn('Title-1');
        $title->expects($this->once())
            ->method('set')
            ->willReturnSelf();
        $this->config->expects($this->once())
            ->method('getTitle')
            ->willReturn($title);
        $category->expects($this->once())
            ->method('getMetaDescription')
            ->willReturn('Meta description');
        $this->config->expects($this->once())
            ->method('setDescription')
            ->with('Meta description');
        $category->expects($this->once())
            ->method('getMetaKeywords')
            ->willReturn('Keyword-1');
        $this->config->expects($this->once())
            ->method('setKeywords')
            ->with('Keyword-1');
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->willReturn($abstractBlockMock);

        $result = $this->block->setLayout($layoutMock);
        $this->assertSame($result, $this->block);
    }

    /**
     * Unit test to cover getProductListHtml()
     *
     * @covers \Magento\Catalog\Block\Category\View::getProductListHtml()
     * @return void
     */
    public function testGetProductListHtml(): void
    {
        $expectedHtml = '<div>product list</div>';
        $layoutMock = $this->createMock(LayoutInterface::class);

        $layoutMock->expects($this->once())
            ->method('getChildName')
            ->with(null, 'product_list')
            ->willReturn('test_child_product_list');
        $layoutMock->expects($this->once())
            ->method('renderElement')
            ->with('test_child_product_list')
            ->willReturn($expectedHtml);

        $this->block->setLayout($layoutMock);
        $this->assertSame($expectedHtml, $this->block->getProductListHtml());
    }

    /**
     * Unit test to cover getCmsBlockHtml()
     *
     * @covers \Magento\Catalog\Block\Category\View::getCmsBlockHtml()
     * @return void
     */
    public function testGetCmsBlockHtml(): void
    {
        $expectedHtml = '<div>cms block</div>';
        $layoutMock = $this->createMock(LayoutInterface::class);
        $title = $this->createMock(Title::class);
        $abstractBlockMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toHtml'])
            ->addMethods(['setBlockId'])
            ->getMockForAbstractClass();
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->addMethods(['getLandingPage', 'getMetaTitle'])
            ->getMock();

        $category->expects($this->once())
            ->method('getMetaTitle')
            ->willReturn('Title-1');
        $this->config->expects($this->once())
            ->method('getTitle')
            ->willReturn($title);
        $title->expects($this->once())
            ->method('set')
            ->willReturnSelf();
        $layoutMock->expects($this->exactly(2))
            ->method('createBlock')
            ->willReturn($abstractBlockMock);
        $this->registry->expects($this->once())
            ->method('registry')
            ->willReturn($category);
        $category->expects($this->once())
            ->method('getLandingPage')
            ->willReturn('Landing-Page');
        $abstractBlockMock->expects($this->once())
            ->method('setBlockId')
            ->willReturnSelf();
        $abstractBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($expectedHtml);

        $this->block->setLayout($layoutMock);
        $this->assertSame($expectedHtml, $this->block->getCmsBlockHtml());
    }

    /**
     * Unit test to cover getCmsBlockHtml() when block html data is set
     *
     * @covers \Magento\Catalog\Block\Category\View::getCmsBlockHtml()
     * @return void
     */
    public function testGetCmsBlockHtmlWhenBlockHtmlDataIsSet(): void
    {
        $expectedHtml = '<div>cms block</div>';
        $this->block->setData('cms_block_html', $expectedHtml);
        $this->assertSame($expectedHtml, $this->block->getCmsBlockHtml());
    }

    /**
     * Unit test for isProductMode()
     *
     * @covers \Magento\Catalog\Block\Category\View::isProductMode()
     * @dataProvider isProductModeDataProvider
     * @param string $mode
     * @param bool $expectedResult
     * @return void
     */
    public function testIsProductMode(string $mode, bool $expectedResult): void
    {
        $currentCategoryMock = $this->createMock(Category::class);
        $currentCategoryMock->expects($this->once())->method('getDisplayMode')->willReturn($mode);
        $this->block->setCurrentCategory($currentCategoryMock);
        $this->assertSame($expectedResult, $this->block->isProductMode());
    }

    /**
     * Unit test for isMixedMode()
     *
     * @covers \Magento\Catalog\Block\Category\View::isMixedMode()
     * @dataProvider isMixedModeDataProvider
     * @param string $mode
     * @param bool $expectedResult
     * @return void
     */
    public function testIsMixedMode(string $mode, bool $expectedResult): void
    {
        $currentCategoryMock = $this->createMock(Category::class);
        $currentCategoryMock->expects($this->once())->method('getDisplayMode')->willReturn($mode);
        $this->block->setCurrentCategory($currentCategoryMock);
        $this->assertSame($expectedResult, $this->block->isMixedMode());
    }

    /**
     * Data provider for testIsProductMode()
     *
     * @return array
     */
    public static function isProductModeDataProvider(): array
    {
        return [
            'mode_products_only' => [Category::DM_PRODUCT, true],
            'mode_page_only' => [Category::DM_PAGE, false],
            'mode_products_and_page' => [Category::DM_MIXED, false]
        ];
    }

    /**
     * Data provider for isMixedMode()
     *
     * @return array
     */
    public static function isMixedModeDataProvider(): array
    {
        return [
            'mode_products_only' => [Category::DM_PRODUCT, false],
            'mode_page_only' => [Category::DM_PAGE, false],
            'mode_products_and_page' => [Category::DM_MIXED, true]
        ];
    }

    /**
     * Test getCurrentCategory() retrieves category from registry and caches it on the block
     *
     * @covers \Magento\Catalog\Block\Category\View::getCurrentCategory()
     * @return void
     */
    public function testGetCurrentCategoryRetrievesFromRegistryAndCaches(): void
    {
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['registry'])
            ->getMock();

        $registryMock->expects($this->once())
            ->method('registry')
            ->with('current_category')
            ->willReturn($category);

        $this->setProtectedProperty($this->block, '_coreRegistry', $registryMock);
        $this->assertFalse($this->block->hasData('current_category'));

        $result = $this->block->getCurrentCategory();

        $this->assertSame($category, $result);
        $this->assertSame($category, $this->block->getData('current_category'));
    }

    /**
     * Test getCurrentCategory() returns existing data without calling registry
     *
     * @covers \Magento\Catalog\Block\Category\View::getCurrentCategory()
     * @return void
     */
    public function testGetCurrentCategoryReturnsExistingDataWithoutCallingRegistry(): void
    {
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block->setData('current_category', $category);

        $registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['registry'])
            ->getMock();

        $registryMock->expects($this->never())->method('registry');

        $this->setProtectedProperty($this->block, '_coreRegistry', $registryMock);
        $result = $this->block->getCurrentCategory();
        $this->assertSame($category, $result);
    }

    /**
     * Test isContentMode() returns expected result, when category display mode is set to different values
     *
     * @covers \Magento\Catalog\Block\Category\View::isContentMode()
     * @dataProvider displayModeDataProvider
     * @param string $mode
     * @param bool $expectedResult
     * @return void
     */
    public function testIsContentModeForAllDisplayModes($mode, $expectedResult): void
    {
        $categoryMock = $this->getMockBuilder(Category::class)
            ->onlyMethods(['getDisplayMode'])
            ->disableOriginalConstructor()
            ->getMock();

        $categoryMock->method('getDisplayMode')->willReturn($mode);
        $this->block->setCurrentCategory($categoryMock);

        $this->assertSame($expectedResult, $this->block->isContentMode());
    }

    /**
     * Data provider for testIsContentModeReturnsFalseWhenCategoryDisplayModeIsNotPage()
     *
     * @return array
     */
    public static function displayModeDataProvider(): array
    {
        return [
            'display_mode_page' => [Category::DM_PAGE, true],
            'display_mode_product' => [Category::DM_PRODUCT, false],
            'display_mode_mixed' => [Category::DM_MIXED, false]
        ];
    }

    /**
     * Test isContentMode() returns expected result for different combinations of
     * anchor/non-anchor category with/without applied filters
     *
     * @covers \Magento\Catalog\Block\Category\View::isContentMode()
     * @dataProvider pageAnchorDataProvider
     * @param bool $isAnchor
     * @param bool $hasFilter
     * @param bool $hasState
     * @param bool $expectedResult
     * @return void
     */
    public function testIsContentModeForDisplayModePageCombinations(
        bool $isAnchor,
        bool $hasFilter,
        bool $hasState,
        bool $expectedResult
    ): void {
        $categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDisplayMode'])
            ->addMethods(['getIsAnchor'])
            ->getMock();
        $catalogLayerMock = $this->getMockBuilder(Layer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getState'])
            ->getMock();

        $categoryMock->method('getDisplayMode')->willReturn(Category::DM_PAGE);
        $categoryMock->method('getIsAnchor')->willReturn($isAnchor);
        $this->block->setCurrentCategory($categoryMock);

        if ($isAnchor) {
            $stateMock = $this->getMockBuilder(State::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getFilters'])
                ->getMock();
            if ($hasState && $hasFilter) {
                $filterMock = $this->createMock(Item::class);
                $stateMock->method('getFilters')->willReturn([$filterMock]);
            }
            $catalogLayerMock->method('getState')->willReturn($stateMock);
        } else {
            $catalogLayerMock->method('getState')->willReturn(null);
        }

        $this->setProtectedProperty($this->block, '_catalogLayer', $catalogLayerMock);

        $this->assertSame($expectedResult, $this->block->isContentMode());
    }

    /**
     * Data provider for testIsContentModeForPageAnchorCombinations
     *
     * @return array
     */
    public static function pageAnchorDataProvider(): array
    {
        return [
            'non_anchor'                       => [false, false, false, true],
            'anchor_without_filters_and_state' => [true, false, false, true],
            'anchor_with_filters_and_state'    => [true, true, true, false],
        ];
    }

    /**
     * Helper to set protected/private property on object
     *
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     * @return void
     */
    private function setProtectedProperty($object, string $propertyName, $value): void
    {
        $reflection = new \ReflectionClass($object);
        while (!$reflection->hasProperty($propertyName) && $reflection->getParentClass() !== false) {
            $reflection = $reflection->getParentClass();
        }
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
