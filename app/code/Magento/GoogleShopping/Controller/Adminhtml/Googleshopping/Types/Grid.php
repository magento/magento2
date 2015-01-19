<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
