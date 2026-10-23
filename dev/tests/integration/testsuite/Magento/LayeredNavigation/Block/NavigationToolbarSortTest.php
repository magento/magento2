<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LayeredNavigation\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[
    AppArea('frontend'),
    AppIsolation(true),
    DbIsolation(false),
]
class NavigationToolbarSortTest extends TestCase
{
    private const CATEGORY_NAVIGATION_BLOCK = 'Magento\LayeredNavigation\Block\Navigation\Category';

    #[
        DataFixture(
            CategoryFixture::class,
            ['custom_attributes' => ['default_sort_by' => 'name']],
            'category'
        ),
    ]
    public function testToolbarUsesListSortConfigWhenNavigationRendersBeforeList(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtureCategory = $objectManager->get(DataFixtureStorageManager::class)->getStorage()->get('category');
        $category = $objectManager->get(CategoryRepositoryInterface::class)->get($fixtureCategory->getId());

        $layer = $objectManager->get(Resolver::class)->get();
        $layer->setCurrentCategory($category);

        $layout = $objectManager->get(LayoutInterface::class);
        $navigation = $layout->createBlock(self::CATEGORY_NAVIGATION_BLOCK, 'catalog.leftnav');
        $list = $layout->createBlock(ListProduct::class, 'category.products.list');
        $toolbar = $layout->createBlock(Toolbar::class, 'product_list_toolbar');
        $layout->setChild($list->getNameInLayout(), $toolbar->getNameInLayout(), 'toolbar');

        $navigation->toHtml();

        $this->assertSame('name', $toolbar->getCurrentOrder());
        $this->assertSame('name', $list->getSortBy());
    }
}
