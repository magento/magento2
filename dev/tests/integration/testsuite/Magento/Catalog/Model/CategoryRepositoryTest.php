<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Backend\Model\Auth;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\RulesFactory;
use PHPUnit\Framework\TestCase;

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
     * @var RulesFactory
     */
    private $rulesFactory;

    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * Sets up common objects.
     *
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->repo = Bootstrap::getObjectManager()->create(CategoryRepositoryInterface::class);
        $this->rulesFactory = Bootstrap::getObjectManager()->get(RulesFactory::class);
        $this->roleFactory = Bootstrap::getObjectManager()->get(RoleFactory::class);
        $this->auth = Bootstrap::getObjectManager()->get(Auth::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->auth->logout();
    }

    /**
     * Test authorization when saving category's design settings.
     *
     * @magentoDataFixture Magento/Catalog/_files/category.php
     * @magentoDataFixture Magento/User/_files/user_with_new_role.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSaveDesign()
    {
        $category = $this->repo->get(333);
        /** @var Role $role */
        $role = $this->roleFactory->create();
        $role->load('new_role', 'role_name');
        $this->auth->login('admin_with_role', '12345abc');

        //Admin doesn't have access to category's design.
        /** @var Rules $rules */
        $rules = $this->rulesFactory->create();
        $rules->setRoleId($role->getId());
        $rules->setResources(['Magento_Catalog::categories']);
        $rules->saveRel();

        $category->setCustomAttribute('custom_design', 2);
        $category = $this->repo->save($category);
        $this->assertEmpty($category->getCustomAttribute('custom_design'));

        //Admin has access to category' design.
        /** @var Rules $rules */
        $rules = $this->rulesFactory->create();
        $rules->setRoleId($role->getId());
        $rules->setResources(['Magento_Catalog::categories', 'Magento_Catalog::edit_category_design']);
        $rules->saveRel();

        $category->setCustomAttribute('custom_design', 2);
        $category = $this->repo->save($category);
        $this->assertNotEmpty($category->getCustomAttribute('custom_design'));
        $this->assertEquals(2, $category->getCustomAttribute('custom_design')->getValue());
    }
}
