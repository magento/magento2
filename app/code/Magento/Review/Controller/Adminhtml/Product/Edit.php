<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

class Edit extends \Magento\Review\Controller\Adminhtml\Product
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Review::catalog_reviews_ratings_reviews_all');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Customer Reviews'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Edit Review'));

        $this->_addContent($this->_view->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Edit'));

        $this->_view->renderLayout();
    }
}
