<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

/**
 * Class Index
 * @deprecated 2.2.0
 * @since 2.0.0
 */
class Index extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Index action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Theme::system_design_theme');
        $this->_view->renderLayout();
    }
}
