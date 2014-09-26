<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class UrlRewriteTest extends \Magento\Backend\Utility\Controller
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

        $this->getRequest()->setPost(
            [
                'description' => 'Some URL rewrite description',
                'options' => 'R',
                'request_path' => 'some_new_path',
                'store_id' => 1,
                'cms_page' => $page->getId()
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
