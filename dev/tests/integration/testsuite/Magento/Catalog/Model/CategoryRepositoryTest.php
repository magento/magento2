<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Catalog\Model\CategoryLayoutUpdateManager;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for CategoryRepository model.
 */
class CategoryRepositoryTest extends TestCase
{
    /**
     * @var CategoryLayoutUpdateManager
     */
    private $layoutManager;

    /**
     * @var CategoryRepositoryInterfaceFactory
     */
    private $repositoryFactory;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * Sets up common objects.
     *
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->repositoryFactory = Bootstrap::getObjectManager()->get(CategoryRepositoryInterfaceFactory::class);
        $this->layoutManager = Bootstrap::getObjectManager()->get(CategoryLayoutUpdateManager::class);
        $this->productCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
        $this->categoryCollectionFactory = Bootstrap::getObjectManager()->create(CategoryCollectionFactory::class);
    }

    /**
     * Create subject object.
     *
     * @return CategoryRepositoryInterface
     */
    private function createRepo(): CategoryRepositoryInterface
    {
        return $this->repositoryFactory->create();
    }

    /**
     * Test that custom layout file attribute is saved.
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testCustomLayout(): void
    {
        //New valid value
        $repo = $this->createRepo();
        $category = $repo->get(333);
        $newFile = 'test';
        $this->layoutManager->setCategoryFakeFiles(333, [$newFile]);
        $category->setCustomAttribute('custom_layout_update_file', $newFile);
        $repo->save($category);
        $repo = $this->createRepo();
        $category = $repo->get(333);
        $this->assertEquals($newFile, $category->getCustomAttribute('custom_layout_update_file')->getValue());

        //Setting non-existent value
        $newFile = 'does not exist';
        $category->setCustomAttribute('custom_layout_update_file', $newFile);
        $caughtException = false;
        try {
            $repo->save($category);
        } catch (LocalizedException $exception) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException);
    }

    /**
     * Test removal of categories.
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     * @magentoAppArea adminhtml
     * @return void
     */
    public function testCategoryBehaviourAfterDelete(): void
    {
        $productCollection = $this->productCollectionFactory->create();
        $deletedCategories = ['3', '4', '5', '13'];
        $categoryCollectionIds = $this->categoryCollectionFactory->create()->getAllIds();
        $this->createRepo()->deleteByIdentifier(3);
        $this->assertEquals(
            0,
            $productCollection->addCategoriesFilter(['in' => $deletedCategories])->getSize(),
            'The category-products relations was not deleted after category delete'
        );
        $newCategoryCollectionIds = $this->categoryCollectionFactory->create()->getAllIds();
        $difference = array_diff($categoryCollectionIds, $newCategoryCollectionIds);
        sort($difference);
        $this->assertEquals(
            $deletedCategories,
            $difference,
            'Wrong categories was deleted'
        );
    }
}
