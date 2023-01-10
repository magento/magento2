<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Controller\Adminhtml;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\Page\CustomLayout\CustomLayoutManager;
use Magento\Cms\Model\Page\CustomLayoutManagerInterface;
use Magento\Framework\Acl\Builder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Bootstrap;
use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Model\UrlRewrite;

/**
 * Test the saving CMS pages design via admin area interface.
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageDesignTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Magento_Cms::save';

    /**
     * @var string
     */
    protected $uri = 'backend/cms/page/save';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    /**
     * @var Builder
     */
    private $aclBuilder;

    /**
     * @var GetPageByIdentifierInterface
     */
    private $pageRetriever;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string[]
     */
    private $pagesToDelete = [];

    /**
     * @var PostDataProcessor
     */
    private $postDataProcessor;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        BootstrapHelper::getObjectManager()->configure([
            'preferences' => [
                CustomLayoutManagerInterface::class => CustomLayoutManager::class
            ]
        ]);
        parent::setUp();

        $this->aclBuilder = BootstrapHelper::getObjectManager()->get(Builder::class);
        $this->pageRetriever = BootstrapHelper::getObjectManager()->get(GetPageByIdentifierInterface::class);
        $this->scopeConfig = BootstrapHelper::getObjectManager()->get(ScopeConfigInterface::class);
        $this->pagesToDelete = [];
        $this->postDataProcessor = BootstrapHelper::getObjectManager()->get(PostDataProcessor::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $pageIds = [];
        foreach ($this->pagesToDelete as $identifier) {
            $pageIds[] = $identifier;
            $page = $this->pageRetriever->execute($identifier, 0);
            $page->delete();
        }
        $this->removeUrlRewrites();
    }

    /**
     * Removes url rewrites created during test execution.
     *
     * @return void
     */
    private function removeUrlRewrites(): void
    {
        if (!empty($this->pagesToDelete)) {
            $urlRewriteCollectionFactory = BootstrapHelper::getObjectManager()->get(
                UrlRewriteCollectionFactory::class
            );
            /** @var UrlRewriteCollection $urlRewriteCollection */
            $urlRewriteCollection = $urlRewriteCollectionFactory->create();
            $urlRewriteCollection->addFieldToFilter('request_path', ['in' => $this->pagesToDelete]);
            $urlRewrites = $urlRewriteCollection->getItems();
            /** @var UrlRewrite $urlRewrite */
            foreach ($urlRewrites as $urlRewrite) {
                try {
                    $urlRewrite->delete();
                } catch (\Exception $exception) {
                    // already removed
                }
            }
        }
    }

    /**
     * Check whether additional authorization is required for the design fields.
     *
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testSaveDesign(): void
    {
        //Expected list of sessions messages collected throughout the controller calls.
        $sessionMessages = ['You are not allowed to change CMS pages design settings'];
        //Test page data.
        $id = 'test-page' .rand(1111, 9999);
        $requestData = [
            PageInterface::IDENTIFIER => $id,
            PageInterface::TITLE => 'Page title',
            PageInterface::CUSTOM_THEME => '1',
            PageInterface::PAGE_LAYOUT => 'empty'
        ];

        //Creating a new page with design properties without the required permissions.
        $this->aclBuilder->getAcl()->deny(
            Bootstrap::ADMIN_ROLE_ID,
            'Magento_Cms::save_design'
        );
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->dispatch($this->uri);
        $this->assertSessionMessages(
            self::equalTo($sessionMessages),
            MessageInterface::TYPE_ERROR
        );

        //Trying again with the permissions.
        $this->aclBuilder->getAcl()->allow(
            Bootstrap::ADMIN_ROLE_ID,
            ['Magento_Cms::save', 'Magento_Cms::save_design']
        );
        $this->getRequest()->setDispatched(false);
        $this->dispatch($this->uri);
        /** @var Page $page */
        $page = BootstrapHelper::getObjectManager()->create(PageInterface::class);
        $page->load($id, PageInterface::IDENTIFIER);
        $this->assertNotEmpty($page->getId());
        $this->assertEquals(1, $page->getCustomTheme());
        $requestData['page_id'] = $page->getId();
        $this->getRequest()->setPostValue($requestData);

        //Updating the page without the permissions but not touching design settings.
        $this->aclBuilder->getAcl()->deny(
            Bootstrap::ADMIN_ROLE_ID,
            'Magento_Cms::save_design'
        );
        $this->getRequest()->setDispatched(false);
        $this->dispatch($this->uri);
        $this->assertSessionMessages(self::equalTo($sessionMessages), MessageInterface::TYPE_ERROR);

        //Updating design settings without the permissions.
        $requestData[PageInterface::CUSTOM_THEME] = '2';
        $this->getRequest()->setPostValue($requestData);
        $this->getRequest()->setDispatched(false);
        $this->dispatch($this->uri);
        $sessionMessages[] = $sessionMessages[0];
        $this->assertSessionMessages(
            self::equalTo($sessionMessages),
            MessageInterface::TYPE_ERROR
        );
        $this->pagesToDelete = [$id];
    }

    /**
     * Check that default design values are accepted without the permissions.
     *
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testSaveDesignWithDefaults(): void
    {
        //Test page data.
        $id = 'test-page' .rand(1111, 9999);
        $defaultLayout = $this->scopeConfig->getValue('web/default_layouts/default_cms_layout');
        $requestData = [
            PageInterface::IDENTIFIER => $id,
            PageInterface::TITLE => 'Page title',
            PageInterface::PAGE_LAYOUT => $defaultLayout
        ];
        //Creating a new page with design properties without the required permissions but with default values.
        $this->aclBuilder->getAcl()->deny(
            Bootstrap::ADMIN_ROLE_ID,
            'Magento_Cms::save_design'
        );
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->dispatch($this->uri);

        //Validating saved page
        /** @var Page $page */
        $page = BootstrapHelper::getObjectManager()->create(PageInterface::class);
        $page->load($id, PageInterface::IDENTIFIER);
        $this->assertNotEmpty($page->getId());
        $this->assertNotNull($page->getPageLayout());
        $this->assertEquals($defaultLayout, $page->getPageLayout());
        $this->pagesToDelete = [$id];
    }

    /**
     * Test that custom layout update fields are dealt with properly.
     *
     * @magentoDataFixture Magento/Cms/_files/pages_with_layout_xml.php
     * @throws \Throwable
     * @return void
     */
    public function testSaveLayoutXml(): void
    {
        $page = $this->pageRetriever->execute('test_custom_layout_page_1', 0);
        $requestData = [
            Page::PAGE_ID => $page->getId(),
            PageInterface::IDENTIFIER => 'test_custom_layout_page_1',
            PageInterface::TITLE => 'Page title',
            PageInterface::CUSTOM_LAYOUT_UPDATE_XML => $page->getCustomLayoutUpdateXml(),
            PageInterface::LAYOUT_UPDATE_XML => $page->getLayoutUpdateXml(),
            'layout_update_selected' => '_existing_'
        ];

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->dispatch($this->uri);
        $this->getRequest()->setDispatched(false);

        $updated = $this->pageRetriever->execute('test_custom_layout_page_1', 0);
        $this->assertEquals($updated->getCustomLayoutUpdateXml(), $page->getCustomLayoutUpdateXml());
        $this->assertEquals($updated->getLayoutUpdateXml(), $page->getLayoutUpdateXml());

        $requestData = [
            Page::PAGE_ID => $page->getId(),
            PageInterface::IDENTIFIER => 'test_custom_layout_page_1',
            PageInterface::TITLE => 'Page title',
            PageInterface::CUSTOM_LAYOUT_UPDATE_XML => $page->getCustomLayoutUpdateXml(),
            PageInterface::LAYOUT_UPDATE_XML => $page->getLayoutUpdateXml(),
            'layout_update_selected' => '_no_update_'
        ];
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->dispatch($this->uri);
        $this->getRequest()->setDispatched(false);

        $updated = $this->pageRetriever->execute('test_custom_layout_page_1', 0);
        $this->assertEmpty($updated->getCustomLayoutUpdateXml());
        $this->assertEmpty($updated->getLayoutUpdateXml());
        $this->pagesToDelete = ['test_custom_layout_page_1'];
    }

    /**
     * Test create CMS page with invalid URL
     *
     * @return void
     */
    public function testSaveWithInlavidIdentifier(): void
    {
        $identifier = 'admin';
        $reservedWords = 'admin, soap, rest, graphql, standard';
        //Expected list of sessions messages collected throughout the controller calls.
        $sessionMessages = [sprintf(
            'URL key "%s" matches a reserved endpoint name (%s). Use another URL key.',
            $identifier,
            $reservedWords
        )];
        $requestData = [
            PageInterface::IDENTIFIER => $identifier,
            PageInterface::TITLE => 'page title',
            PageInterface::CUSTOM_THEME => '1',
            PageInterface::PAGE_LAYOUT => 'empty',
        ];

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->dispatch($this->uri);
        $this->assertSessionMessages(
            self::equalTo($sessionMessages),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test create CMS page with invalid layout update
     *
     * @return void
     */
    public function testSaveWithCustomLayoutUpdate(): void
    {
        $requestData = [
            PageInterface::IDENTIFIER => 'randomidentified',
            PageInterface::TITLE => 'page title',
            PageInterface::CUSTOM_THEME => '1',
            PageInterface::PAGE_LAYOUT => 'empty',
            PageInterface::CUSTOM_LAYOUT_UPDATE_XML => '<container />',
            PageInterface::LAYOUT_UPDATE_XML => '<container />',
        ];
        $this->assertFalse($this->postDataProcessor->validate($requestData));
    }
}
