<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Backend\Model\Auth;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Acl\Builder;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Bootstrap as TestBootstrap;

/**
 * Provide tests for CategoryRepository model.
 */
class CategoryRepositoryTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var CategoryRepositoryInterface
     */
    private $repo;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var Builder
     */
    private $aclBuilder;

    /**
     * @var CategoryInterfaceFactory
     */
    private $categoryFactory;

    /** @var CollectionFactory */
    private $productCollectionFactory;

    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CategoryCollectionFactory */
    private $categoryCollectionFactory;

    /**
     * Sets up common objects.
     *
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->repo = $this->objectManager->create(CategoryRepositoryInterface::class);
        $this->auth = $this->objectManager->get(Auth::class);
        $this->aclBuilder = $this->objectManager->get(Builder::class);
        $this->categoryFactory = $this->objectManager->get(CategoryInterfaceFactory::class);
        $this->productCollectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->categoryCollectionFactory = $this->objectManager->create(CategoryCollectionFactory::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->auth->logout();
        $this->aclBuilder->resetRuntimeAcl();
    }

    /**
     * Test authorization when saving category's design settings.
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testSaveDesign(): void
    {
        $category = $this->repo->get(333);
        $this->auth->login(TestBootstrap::ADMIN_NAME, TestBootstrap::ADMIN_PASSWORD);

        //Admin doesn't have access to category's design.
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Catalog::edit_category_design');

        $category->setCustomAttribute('custom_design', 2);
        $category = $this->repo->save($category);
        $customDesignAttribute = $category->getCustomAttribute('custom_design');
        $this->assertTrue(!$customDesignAttribute || !$customDesignAttribute->getValue());

        //Admin has access to category' design.
        $this->aclBuilder->getAcl()
            ->allow(null, ['Magento_Catalog::categories', 'Magento_Catalog::edit_category_design']);

        $category->setCustomAttribute('custom_design', 2);
        $category = $this->repo->save($category);
        $this->assertNotEmpty($category->getCustomAttribute('custom_design'));
        $this->assertEquals(2, $category->getCustomAttribute('custom_design')->getValue());

        //Creating a new one
        /** @var CategoryInterface $newCategory */
        $newCategory = $this->categoryFactory->create();
        $newCategory->setName('new category without design');
        $newCategory->setParentId($category->getParentId());
        $newCategory->setIsActive(true);
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Catalog::edit_category_design');
        $newCategory->setCustomAttribute('custom_design', 2);
        $newCategory = $this->repo->save($newCategory);
        $customDesignAttribute = $newCategory->getCustomAttribute('custom_design');
        $this->assertTrue(!$customDesignAttribute || !$customDesignAttribute->getValue());
    }

    /**
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
        $this->repo->deleteByIdentifier(3);
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
