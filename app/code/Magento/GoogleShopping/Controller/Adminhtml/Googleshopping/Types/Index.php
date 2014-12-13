<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
