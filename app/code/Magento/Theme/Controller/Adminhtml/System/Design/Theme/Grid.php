<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

/**
 * Class Grid
 * @deprecated 100.2.0
 */
class Grid extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Grid ajax action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
