<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

class Grid extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * JSON Grid Action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $grid = $this->_view->getLayout()->createBlock('Magento\Newsletter\Block\Adminhtml\Template\Grid')->toHtml();
        $this->getResponse()->setBody($grid);
    }
}
