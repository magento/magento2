<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

/**
 * Class Index
 * @deprecated 100.2.0
 */
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
        $this->_view->getLayout()->getBlock('page.title')->setPageTitle('Themes');
        $this->_view->renderLayout();
    }
}
