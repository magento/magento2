<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Block\Html\Breadcrumbs as ThemeBreadcrumbs;
use PHPUnit\Framework\TestCase;

/**
 * Checks the behavior of breadcrumbs on the category view page.
 *
 * @magentoAppArea frontend
 */
class BreadcrumbsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var LayoutInterface */
    private $layout;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var Registry */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryRepository = $this->objectManager->create(CategoryRepositoryInterface::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
    }

    /**
     * Checks the order of categories in breadcrumbs.
     *
     * @magentoDataFixture Magento/Catalog/_files/category_tree.php
     * @return void
     */
    public function testCategoriesSequence(): void
    {
        $category = $this->categoryRepository->get(402);
        $this->registry->register('current_category', $category);
        $themeBreadcrumbs = $this->layout->createBlock(ThemeBreadcrumbs::class, 'breadcrumbs');
        $this->layout->createBlock(Breadcrumbs::class);
        $html = $themeBreadcrumbs->toHtml();

        $actualCategories = preg_replace('/\s+/', '', strip_tags($html));
        $expectedCategories = __('Home') . 'Category1' . 'Category1.1' . 'Category1.1.1';
        self::assertEquals(
            $expectedCategories,
            $actualCategories,
            'The order of categories in breadcrumbs is not correct!'
        );
    }
}
