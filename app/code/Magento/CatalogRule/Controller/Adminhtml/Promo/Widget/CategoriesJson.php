<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Widget;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Categories json widget for catalog rule
 */
class CategoriesJson extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget implements HttpPostActionInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(Context $context, Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize category object in registry
     *
     * @return Category|bool
     */
    protected function _initCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        $storeId = (int)$this->getRequest()->getParam('store');

        $category = $this->_objectManager->create(\Magento\Catalog\Model\Category::class);
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = $this->_objectManager->get(
                    \Magento\Store\Model\StoreManager::class
                )->getStore(
                    $storeId
                )->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('catalog/*/', ['_current' => true, 'id' => null]);
                    return false;
                }
            }
        }

        $this->_coreRegistry->register('category', $category);
        $this->_coreRegistry->register('current_category', $category);

        return $category;
    }

    /**
     * Get tree node (Ajax version)
     *
     * @return void
     */
    public function execute()
    {
        $categoryId = (int)$this->getRequest()->getPost('id');
        if ($categoryId) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!($category = $this->_initCategory())) {
                return;
            }
            $selected = $this->getRequest()->getPost('selected', '');
            $block = $this->_view->getLayout()->createBlock(
                \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree::class
            )->setCategoryIds(
                explode(',', $selected)
            );
            $this->getResponse()->representJson(
                $block->getTreeJson($category)
            );
        }
    }
}
