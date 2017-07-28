<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

/**
 * Class Grid
 * @deprecated 2.2.0
 * @since 2.0.0
 */
class Grid extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Grid ajax action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
