<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
        $grid = $this->_view->getLayout()->createBlock(
            \Magento\Newsletter\Block\Adminhtml\Template\Grid::class
        )->toHtml();
        $this->getResponse()->setBody($grid);
    }
}
