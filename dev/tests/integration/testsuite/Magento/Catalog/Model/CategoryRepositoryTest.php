<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

use Magento\Backend\Model\Auth;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Framework\Acl\Builder;
use Magento\Framework\Acl\CacheInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Bootstrap as TestBootstrap;

/**
 * Provide tests for CategoryRepository model.
 */
class CategoryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test subject.
     *
     * @var CategoryRepositoryInterface
     */
    private $repository;

    /**
     * @var Auth
     */
    private $authorization;

    /**
     * @var Builder
     */
    private $aclBuilder;

    /**
     * @var CacheInterface
     */
    private $aclCache;

    /**
     * @var CategoryInterfaceFactory
     */
    private $categoryFactory;

    /**
     * Sets up common objects.
     *
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->repository = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $this->authorization = Bootstrap::getObjectManager()->get(Auth::class);
        $this->aclBuilder = Bootstrap::getObjectManager()->get(Builder::class);
        $this->aclCache = Bootstrap::getObjectManager()->get(CacheInterface::class);
        $this->categoryFactory = Bootstrap::getObjectManager()->get(CategoryInterfaceFactory::class);
        $this->authorization->login(TestBootstrap::ADMIN_NAME, TestBootstrap::ADMIN_PASSWORD);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->authorization->logout();
        $this->aclCache->clean();
    }

    /**
     * Test authorization when saving category's design settings.
     *
     * @return CategoryInterface
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSaveDesign()
    {
        $category = $this->repository->get(333);

        //Admin doesn't have access to category's design.
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Catalog::edit_category_design');

        $category->setCustomAttribute('custom_design', 2);
        $category = $this->repository->save($category);
        $customDesignAttribute = $category->getCustomAttribute('custom_design');
        $this->assertTrue(!$customDesignAttribute || !$customDesignAttribute->getValue());

        //Admin has access to category' design.
        $this->aclBuilder->getAcl()
            ->allow(null, ['Magento_Catalog::categories', 'Magento_Catalog::edit_category_design']);

        $category->setCustomAttribute('custom_design', 2);
        $category = $this->repository->save($category);
        $this->assertNotEmpty($category->getCustomAttribute('custom_design'));
        $this->assertEquals(2, $category->getCustomAttribute('custom_design')->getValue());

        return $category;
    }

    /**
     * Test authorization when saving category's design settings with restricted permission.
     *
     * @param CategoryInterface $category
     * @return void
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @depends testSaveDesign
     */
    public function testSaveDesignWithRestrictedPermission(CategoryInterface $category)
    {
        /** @var CategoryInterface $newCategory */
        $newCategory = $this->categoryFactory->create();
        $newCategory->setName('new category without design');
        $newCategory->setParentId($category->getParentId());
        $newCategory->setIsActive(true);
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Catalog::edit_category_design');
        $newCategory->setCustomAttribute('custom_design', 2);
        $newCategory = $this->repository->save($newCategory);
        $customDesignAttribute = $newCategory->getCustomAttribute('custom_design');

        $this->assertTrue(!$customDesignAttribute || !$customDesignAttribute->getValue());
    }
}
