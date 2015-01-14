<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class Index extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * List of all maps (items)
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Attribute Maps'), __('Attribute Maps'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Google Content Attributes'));
        $this->_view->renderLayout();
    }
}
