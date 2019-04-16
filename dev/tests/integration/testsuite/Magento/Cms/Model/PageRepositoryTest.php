<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Model;

use Magento\Backend\Model\Auth;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\Acl\Builder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Bootstrap as TestBootstrap;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for page repository.
 */
class PageRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test subject.
     *
     * @var PageRepositoryInterface
     */
    private $repo;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var Builder
     */
    private $aclBuilder;

    /**
     * @var PageCollectionFactory
     */
    private $pageCollectionFactory;

    /**
     * Sets up common objects.
     *
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->repo = Bootstrap::getObjectManager()->create(PageRepositoryInterface::class);
        $this->auth = Bootstrap::getObjectManager()->get(Auth::class);
        $this->criteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->aclBuilder = Bootstrap::getObjectManager()->get(Builder::class);
        $this->pageCollectionFactory = Bootstrap::getObjectManager()->get(PageCollectionFactory::class);
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
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSaveDesign()
    {
        $pagesCollection = $this->pageCollectionFactory->create();
        $pagesCollection->addFieldToFilter('identifier', ['eq' => 'page_design_blank']);
        $page = $pagesCollection->getFirstItem();

        $this->auth->login(TestBootstrap::ADMIN_NAME, TestBootstrap::ADMIN_PASSWORD);

        //Admin doesn't have access to page's design.
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Cms::save_design');

        $page->setCustomTheme('test');
        $page = $this->repo->save($page);
        $this->assertNotEquals('test', $page->getCustomTheme());

        //Admin has access to page' design.
        $this->aclBuilder->getAcl()->allow(null, ['Magento_Cms::save', 'Magento_Cms::save_design']);

        $page->setCustomTheme('test');
        $page = $this->repo->save($page);
        $this->assertEquals('test', $page->getCustomTheme());
    }
}
