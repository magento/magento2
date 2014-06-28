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

namespace Magento\Catalog\Test\Page\Category;

use Mtf\Page\Page;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;
use Magento\Backend\Test\Block\FormPageActions;

/**
 * Class CatalogCategoryEdit
 * Edit category page in backend
 */
class CatalogCategoryEdit extends Page
{
    /**
     * URL for edit category page
     */
    const MCA = 'catalog/category/edit/id/';

    /**
     * Category Edit Form on the Backend
     *
     * @var string
     */
    protected $formBlock = '#category-edit-container';

    /**
     * Categories tree block
     *
     * @var string
     */
    protected $treeBlock = '.categories-side-col';

    /**
     * Get messages block
     *
     * @var string
     */
    protected $messagesBlock = '#messages .messages';

    /**
     * Backend abstract block
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Form page actions block
     *
     * @var string
     */
    protected $pageActionsBlock = '.page-main-actions';

    /**
     * Init page. Set page url
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Open page using browser and waiting until loader will be disappeared
     *
     * @param array $params
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function open(array $params = array())
    {
        parent::open();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Get Category edit form
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Category\Edit\CategoryForm
     */
    public function getFormBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogAdminhtmlCategoryEditCategoryForm(
            $this->_browser->find($this->formBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Category Tree container on the Backend
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Category\Tree
     */
    public function getTreeBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogAdminhtmlCategoryTree(
            $this->_browser->find($this->treeBlock, Locator::SELECTOR_CSS),
            $this->getTemplateBlock()
        );
    }

    /**
     * Get messages block
     *
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages(
            $this->_browser->find($this->messagesBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get abstract block
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    public function getTemplateBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendTemplate(
            $this->_browser->find($this->templateBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Get Form page actions block
     *
     * @return FormPageActions
     */
    public function getPageActionsBlock()
    {
        return Factory::getBlockFactory()->getMagentoBackendFormPageActions(
            $this->_browser->find($this->pageActionsBlock)
        );
    }
}
