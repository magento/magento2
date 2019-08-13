<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Controller\Adminhtml;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page;
use Magento\Framework\Acl\Builder;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test the saving CMS pages via admin area interface.
 *
 * @magentoAppArea adminhtml
 */
class PageSaveTest extends AbstractBackendController
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
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->aclBuilder = Bootstrap::getObjectManager()->get(Builder::class);
    }

    /**
     * Check whether additional authorization is required for the design fields.
     *
     * @magentoDbIsolation enabled
     * @return void
     */
    public function testSaveDesign(): void
    {
        //Expected list of sessions messages collected throughout the controller calls.
        $sessionMessages = ['You are not allowed to change CMS pages design settings'];
        //Test page data.
        $id = 'test-page';
        $requestData = [
            PageInterface::IDENTIFIER => $id,
            PageInterface::TITLE => 'Page title',
            PageInterface::CUSTOM_THEME => '1'
        ];

        //Creating a new page with design properties without the required permissions.
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Cms::save_design');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->dispatch($this->uri);
        $this->assertSessionMessages(
            self::equalTo($sessionMessages),
            MessageInterface::TYPE_ERROR
        );

        //Trying again with the permissions.
        $this->aclBuilder->getAcl()->allow(null, ['Magento_Cms::save', 'Magento_Cms::save_design']);
        $this->getRequest()->setDispatched(false);
        $this->dispatch($this->uri);
        /** @var Page $page */
        $page = Bootstrap::getObjectManager()->create(PageInterface::class);
        $page->load($id, PageInterface::IDENTIFIER);
        $this->assertNotEmpty($page->getId());
        $this->assertEquals(1, $page->getCustomTheme());
        $requestData['page_id'] = $page->getId();
        $this->getRequest()->setPostValue($requestData);

        //Updating the page without the permissions but not touching design settings.
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Cms::save_design');
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
    }
}
