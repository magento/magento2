<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class UrlRewriteTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Check save cms page rewrite
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testSaveActionCmsPage()
    {
        $page = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Cms\Model\Page');
        $page->load('page_design_blank', 'identifier');

        $this->getRequest()->setPostValue(
            [
                'description' => 'Some URL rewrite description',
                'options' => 'R',
                'request_path' => 'some_new_path',
                'store_id' => 1,
                'cms_page' => $page->getId(),
            ]
        );
        $this->dispatch('backend/admin/url_rewrite/save');

        $this->assertSessionMessages(
            $this->contains('The URL Rewrite has been saved.'),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('backend/admin/url_rewrite/index'));
    }
}
