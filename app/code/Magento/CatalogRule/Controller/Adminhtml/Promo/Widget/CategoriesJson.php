<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Widget;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;

class CategoriesJson extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget
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
     * @return Category
     */
    protected function _initCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        $storeId = (int)$this->getRequest()->getParam('store');

        $category = $this->_objectManager->create('Magento\Catalog\Model\Category');
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = $this->_objectManager->get(
                    'Magento\Store\Model\StoreManager'
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
            $block = $this->_view->getLayout()->createBlock(
                'Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree'
            )->setCategoryIds(
                [$categoryId]
            );
            $this->getResponse()->representJson(
                $block->getTreeJson($category)
            );
        }
    }
}
