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
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Store\ExecuteInStoreContext;
use PHPUnit\Framework\TestCase;

/**
 * Category title check
 *
 * @magentoAppArea frontend
 * @see \Magento\Theme\Block\Html\Title
 */
class TitleTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var Registry */
    private $registry;

    /** @var PageFactory */
    private $pageFactory;

    /** @var ExecuteInStoreContext */
    private $executeInStoreContext;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryRepository = $this->objectManager->get(CategoryRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->pageFactory = $this->objectManager->get(PageFactory::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->executeInStoreContext = $this->objectManager->get(ExecuteInStoreContext::class);
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
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento/Store/_files/store.php
     * @return void
     */
    public function testCategoryNameOnStoreView(): void
    {
        $id = 333;
        $categoryNameForSecondStore = 'Category Name For Second Store';
        $this->executeInStoreContext->execute(
            'test',
            [$this, 'updateCategoryName'],
            $this->categoryRepository->get($id),
            $categoryNameForSecondStore
        );
        $this->registerCategory($this->categoryRepository->get($id));
        $this->assertStringContainsString('Category 1', $this->getBlockTitle(), 'Wrong category name');
        $this->registerCategory($this->categoryRepository->get($id, $this->storeManager->getStore('test')->getId()));
        $this->assertStringContainsString($categoryNameForSecondStore, $this->getBlockTitle(), 'Wrong category name');
    }

    /**
     * Update category name
     *
     * @param CategoryInterface $category
     * @param string $categoryName
     * @return void
     */
    public function updateCategoryName(CategoryInterface $category, string $categoryName): void
    {
        $category->setName($categoryName);
        $this->categoryRepository->save($category);
    }

    /**
     * Get title block
     *
     * @return string
     */
    private function getBlockTitle(): string
    {
        $page = $this->pageFactory->create();
        $page->addHandle([
            'default',
            'catalog_category_view',
        ]);
        $page->getLayout()->generateXml();
        $block = $page->getLayout()->getBlock('page.main.title');
        $this->assertNotFalse($block);

        return $block->stripTags($block->toHtml());
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
