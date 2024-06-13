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
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends TestCase
{
    /**
     * @var View
     */
    protected $block;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

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

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $categoryTag = ['catalog_category_1'];
        $currentCategoryMock = $this->createMock(Category::class);
        $currentCategoryMock->expects($this->once())->method('getIdentities')->willReturn($categoryTag);
        $this->block->setCurrentCategory($currentCategoryMock);
        $this->assertEquals($categoryTag, $this->block->getIdentities());
    }

    public function testBreadcrumbs()
    {
        $layoutMock = $this->createMock(LayoutInterface::class);
        $beadCrumbs = $this->createMock(Breadcrumbs::class);
        $title = $this->createMock(Title::class);
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->addMethods(['getMetaTitle'])
            ->getMock();

        $beadCrumbs->expects($this->once())
            ->method('getTitleSeparator')
            ->willReturn(' - ');

        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->willReturn($beadCrumbs);

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

        $this->block->setLayout($layoutMock);
    }
}
