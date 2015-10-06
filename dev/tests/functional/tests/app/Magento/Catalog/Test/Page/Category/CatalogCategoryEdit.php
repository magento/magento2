<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Page\Category;

use Magento\Backend\Test\Block\FormPageActions;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Page\Page;

/**
 * Edit category page in backend.
 */
class CatalogCategoryEdit extends Page
{
    /**
     * URL for edit category page.
     */
    const MCA = 'catalog/category/edit/id/';

    /**
     * Category Edit Form on the Backend.
     *
     * @var string
     */
    protected $formBlock = '#category-edit-container';

    /**
     * Categories tree block.
     *
     * @var string
     */
    protected $treeBlock = '.categories-side-col';

    /**
     * Get messages block.
     *
     * @var string
     */
    protected $messagesBlock = '#messages .messages';

    /**
     * Backend abstract block.
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Form page actions block.
     *
     * @var string
     */
    protected $pageActionsBlock = '.page-main-actions';

    /**
     * Init page. Set page url.
     *
     * @return void
     */
    protected function initUrl()
    {
        $this->url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Open page using browser and waiting until loader will be disappeared.
     *
     * @param array $params
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function open(array $params = [])
    {
        parent::open();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Get Category edit form.
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Category\Edit\CategoryForm
     */
    public function getFormBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogAdminhtmlCategoryEditCategoryForm(
            $this->browser->find($this->formBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Category Tree container on the Backend.
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Category\Tree
     */
    public function getTreeBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogAdminhtmlCategoryTree(
            $this->browser->find($this->treeBlock, Locator::SELECTOR_CSS),
            $this->getTemplateBlock()
        );
    }

    /**
     * Get messages block.
     *
     * @return \Magento\Backend\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendMessages(
            $this->browser->find($this->messagesBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get abstract block.
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    public function getTemplateBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendTemplate(
            $this->browser->find($this->templateBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get Form page actions block.
     *
     * @return FormPageActions
     */
    public function getPageActionsBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendFormPageActions(
            $this->browser->find($this->pageActionsBlock)
        );
    }
}
