<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Model;

use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Backend\Model\Auth;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Authorization\Model\Rules;
use Magento\Authorization\Model\RulesFactory;
use PHPUnit\Framework\TestCase;

class PageRepositoryTest extends TestCase
{
    /**
     * Test subject.
     *
     * @var PageRepositoryInterface
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
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * Sets up common objects.
     *
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->repo = Bootstrap::getObjectManager()->create(PageRepositoryInterface::class);
        $this->rulesFactory = Bootstrap::getObjectManager()->get(RulesFactory::class);
        $this->roleFactory = Bootstrap::getObjectManager()->get(RoleFactory::class);
        $this->auth = Bootstrap::getObjectManager()->get(Auth::class);
        $this->criteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
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
     * Test authorization when saving page's design settings.
     *
     * @magentoDataFixture Magento/Cms/_files/pages.php
     * @magentoDataFixture Magento/User/_files/user_with_new_role.php
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSaveDesign()
    {
        $pages = $this->repo->getList(
            $this->criteriaBuilder->addFilter('identifier', 'page_design_blank')->create()
        )->getItems();
        $page = array_pop($pages);
        /** @var Role $role */
        $role = $this->roleFactory->create();
        $role->load('new_role', 'role_name');
        $this->auth->login('admin_with_role', '12345abc');

        //Admin doesn't have access to page's design.
        /** @var Rules $rules */
        $rules = $this->rulesFactory->create();
        $rules->setRoleId($role->getId());
        $rules->setResources(['Magento_Cms::save']);
        $rules->saveRel();

        $page->setCustomTheme('test');
        $page = $this->repo->save($page);
        $this->assertNotEquals('test', $page->getCustomTheme());

        //Admin has access to page' design.
        /** @var Rules $rules */
        $rules = $this->rulesFactory->create();
        $rules->setRoleId($role->getId());
        $rules->setResources(['Magento_Cms::save', 'Magento_Cms::save_design']);
        $rules->saveRel();

        $page->setCustomTheme('test');
        $page = $this->repo->save($page);
        $this->assertEquals('test', $page->getCustomTheme());
    }
}
