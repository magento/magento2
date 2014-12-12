<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class Grid extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
     * Grid for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout('false');
        $this->_view->renderLayout();
    }
}
