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
use Magento\Framework\Acl\Builder;
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

    /**
     * Sets up common objects.
     *
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->repo = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $this->auth = Bootstrap::getObjectManager()->get(Auth::class);
        $this->aclBuilder = Bootstrap::getObjectManager()->get(Builder::class);
        $this->categoryFactory = Bootstrap::getObjectManager()->get(CategoryInterfaceFactory::class);
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
     */
    public function testSaveDesign()
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
}
