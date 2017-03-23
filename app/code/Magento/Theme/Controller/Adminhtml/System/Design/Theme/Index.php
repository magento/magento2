<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

class Index extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Theme::system_design_theme');
        $this->_view->renderLayout();
    }
}
