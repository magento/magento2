<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Attribute\LayoutUpdateManager;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Http\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Catalog\Model\CategoryLayoutUpdateManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Responsible for testing category view action on strorefront.
 *
 * @see \Magento\Catalog\Controller\Category\View
 * @magentoAppArea frontend
 */
class CategoryTest extends AbstractController
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->configure([
            'preferences' => [LayoutUpdateManager::class => CategoryLayoutUpdateManager::class]
        ]);

        $this->categoryCollectionFactory = $this->objectManager->create(CollectionFactory::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
        $this->session = $this->objectManager->get(Session::class);
        $this->httpContext = $this->objectManager->get(Context::class);
    }

    /**
     * @inheritdoc
     */
    public function assert404NotFound()
    {
        parent::assert404NotFound();

        $this->assertNull($this->registry->registry('current_category'));
    }

    /**
     * @return array
     */
    public function getViewActionDataProvider(): array
    {
        return [
            'category without children' => [
                'categoryId' => 5,
                ['catalog_category_view_type_layered', 'catalog_category_view_type_layered_without_children'],
                [
                    '%acategorypath-category-1-category-1-1-category-1-1-1%a',
                    '%acategory-category-1-1-1%a',
                    '%a<title>Category 1.1.1 - Category 1.1 - Category 1</title>%a',
                    '%a<h1%a>%SCategory 1.1.1%S</h1>%a',
                    '%aSimple Product Two%a',
                    '%a$45.67%a'
                ],
            ],
            'anchor category' => [
                'categoryId' => 4,
                ['catalog_category_view_type_layered'],
                [
                    '%acategorypath-category-1-category-1-1%a',
                    '%acategory-category-1-1%a',
                    '%a<title>Category 1.1 - Category 1</title>%a',
                    '%a<h1%a>%SCategory 1.1%S</h1>%a',
                    '%aSimple Product%a',
                    '%a$10.00%a',
                    '%aSimple Product Two%a',
                    '%a$45.67%a'
                ],
            ]
        ];
    }

    /**
     * @dataProvider getViewActionDataProvider
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories_with_product_ids.php
     * @magentoDbIsolation disabled
     * @param int $categoryId
     * @param array $expectedHandles
     * @param array $expectedContent
     * @return void
     */
    public function testViewAction(int $categoryId, array $expectedHandles, array $expectedContent): void
    {
        $this->dispatch("catalog/category/view/id/{$categoryId}");
        /** @var $currentCategory Category */
        $currentCategory = $this->registry->registry('current_category');
        $this->assertInstanceOf(Category::class, $currentCategory);
        $this->assertEquals($categoryId, $currentCategory->getId(), 'Category in registry.');

        $lastCategoryId = $this->session->getLastVisitedCategoryId();
        $this->assertEquals($categoryId, $lastCategoryId, 'Last visited category.');

        /* Layout updates */
        $handles = $this->layout->getUpdate()->getHandles();
        foreach ($expectedHandles as $expectedHandleName) {
            $this->assertContains($expectedHandleName, $handles);
        }

        $responseBody = $this->getResponse()->getBody();
        /* Response content */
        foreach ($expectedContent as $expectedText) {
            $this->assertStringMatchesFormat($expectedText, $responseBody);
        }
    }

    /**
     * @return void
     */
    public function testViewActionNoCategoryId(): void
    {
        $this->dispatch('catalog/category/view/');

        $this->assert404NotFound();
    }

    /**
     * @return void
     */
    public function testViewActionNotExistingCategory(): void
    {
        $this->dispatch('catalog/category/view/id/8');

        $this->assert404NotFound();
    }

    /**
     * Checks that disabled category is not available in storefront
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/inactive_category.php
     * @return void
     */
    public function testViewActionDisabledCategory(): void
    {
        $this->dispatch('catalog/category/view/id/111');

        $this->assert404NotFound();
    }

    /**
     * Check that custom layout update files is employed.
     *
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/categories_with_product_ids.php
     * @return void
     */
    public function testViewWithCustomUpdate(): void
    {
        //Setting a fake file for the category.
        $file = 'test-file';
        $categoryId = 5;
        /** @var CategoryLayoutUpdateManager $layoutManager */
        $layoutManager = Bootstrap::getObjectManager()->get(CategoryLayoutUpdateManager::class);
        $layoutManager->setCategoryFakeFiles($categoryId, [$file]);
        /** @var CategoryRepositoryInterface $categoryRepo */
        $categoryRepo = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $category = $categoryRepo->get($categoryId);
        //Updating the custom attribute.
        $category->setCustomAttribute('custom_layout_update_file', $file);
        $categoryRepo->save($category);

        //Viewing the category
        $this->dispatch("catalog/category/view/id/$categoryId");
        //Layout handles must contain the file.
        $handles = Bootstrap::getObjectManager()->get(\Magento\Framework\View\LayoutInterface::class)
            ->getUpdate()
            ->getHandles();
        $this->assertContains("catalog_category_view_selectable_{$categoryId}_{$file}", $handles);
    }

    /**
     * Checks that pagination value can be changed to a new one if remember pagination enabled and already have saved
     * some value
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoConfigFixture default/catalog/frontend/remember_pagination 1
     *
     * @return void
     */
    public function testViewWithRememberPaginationAndPreviousValue(): void
    {
        $this->session->setData(ToolbarModel::LIMIT_PARAM_NAME, 16);
        $newPaginationValue = 24;
        $this->getRequest()->setParams([ToolbarModel::LIMIT_PARAM_NAME => $newPaginationValue]);
        $this->dispatch("catalog/category/view/id/333");
        $block = $this->layout->getBlock('product_list_toolbar');
        $this->assertNotFalse($block);
        $this->assertEquals($newPaginationValue, $block->getLimit());
        $this->assertEquals($newPaginationValue, $this->session->getData(ToolbarModel::LIMIT_PARAM_NAME));
        $this->assertEquals($newPaginationValue, $this->httpContext->getValue(ToolbarModel::LIMIT_PARAM_NAME));
    }

    /**
     * Test to generate category page without duplicate html element ids
     *
     * @magentoDataFixture Magento/Catalog/_files/category_with_three_products.php
     * @magentoDataFixture Magento/Catalog/_files/catalog_category_product_reindex_all.php
     * @magentoDataFixture Magento/Catalog/_files/catalog_product_category_reindex_all.php
     * @magentoDbIsolation disabled
     */
    public function testViewWithoutDuplicateHmlElementIds(): void
    {
        $category = $this->loadCategory('Category 999', Store::DEFAULT_STORE_ID);
        $this->dispatch('catalog/category/view/id/' . $category->getId());

        $responseHtml = $this->getResponse()->getBody();
        $htmlElementIds = ['modes-label', 'mode-list', 'toolbar-amount', 'sorter', 'limiter'];
        foreach ($htmlElementIds as $elementId) {
            $matches = [];
            $idAttribute = "id=\"$elementId\"";
            preg_match_all("/$idAttribute/mx", $responseHtml, $matches);
            $this->assertCount(1, $matches[0]);
            $this->assertEquals($idAttribute, $matches[0][0]);
        }
    }

    /**
     * Loads category by id
     *
     * @param string $categoryName
     * @param int $storeId
     * @return CategoryInterface
     */
    private function loadCategory(string $categoryName, int $storeId): CategoryInterface
    {
        /** @var Collection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        /** @var CategoryInterface $category */
        $category = $categoryCollection->setStoreId($storeId)
            ->addAttributeToSelect('display_mode', 'left')
            ->addAttributeToFilter(CategoryInterface::KEY_NAME, $categoryName)
            ->setPageSize(1)
            ->getFirstItem();
        $category->setStoreId($storeId);

        return $category;
    }
}
