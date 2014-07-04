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
namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Framework\View\Element\BlockInterface;

/**
 * Catalog category widgets controller for CMS WYSIWYG
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Widget extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Chooser Source action
     *
     * @return void
     */
    public function chooserAction()
    {
        $this->getResponse()->setBody($this->_getCategoryTreeBlock()->toHtml());
    }

    /**
     * Categories tree node (Ajax version)
     *
     * @return void
     */
    public function categoriesJsonAction()
    {
        $categoryId = (int)$this->getRequest()->getPost('id');
        if ($categoryId) {
            $selected = $this->getRequest()->getPost('selected', '');
            $category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);
            if ($category->getId()) {
                $this->_coreRegistry->register('category', $category);
                $this->_coreRegistry->register('current_category', $category);
            }
            $categoryTreeBlock = $this->_getCategoryTreeBlock()->setSelectedCategories(explode(',', $selected));
            $this->getResponse()->representJson($categoryTreeBlock->getTreeJson($category));
        }
    }

    /**
     * @return BlockInterface
     */
    protected function _getCategoryTreeBlock()
    {
        return $this->_view->getLayout()->createBlock(
            'Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser',
            '',
            array(
                'data' => array(
                    'id' => $this->getRequest()->getParam('uniq_id'),
                    'use_massaction' => $this->getRequest()->getParam('use_massaction', false)
                )
            )
        );
    }
}
