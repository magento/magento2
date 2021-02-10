<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for view category block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class ViewTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Registry */
    private $registry;

    /** @var GetCategoryByName */
    private $getCategoryByName;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var LayoutInterface */
    private $layout;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->registry = $this->objectManager->get(Registry::class);
        $this->getCategoryByName = $this->objectManager->get(GetCategoryByName::class);
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('current_category');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category_with_cms_block.php
     *
     * @return void
     */
    public function testCmsBlockDisplayedOnCategory(): void
    {
        $storeId = (int)$this->storeManager->getStore('default')->getId();
        $categoryId = $this->getCategoryByName->execute('Category with cms block')->getId();
        $category = $this->categoryRepository->get($categoryId, $storeId);
        $this->registerCategory($category);
        $block = $this->layout->createBlock(View::class)->setTemplate('Magento_Catalog::category/cms.phtml');
        $this->assertStringContainsString('<h1>Fixture Block Title</h1>', $block->toHtml());
    }

    /**
     * Register category in registry
     *
     * @param CategoryInterface $category
     * @return void
     */
    private function registerCategory(CategoryInterface $category): void
    {
        $this->registry->unregister('current_category');
        $this->registry->register('current_category', $category);
    }
}
