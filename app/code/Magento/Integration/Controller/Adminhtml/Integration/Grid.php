<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

/**
 * Class \Magento\Integration\Controller\Adminhtml\Integration\Grid
 *
 * @since 2.0.0
 */
class Grid extends \Magento\Integration\Controller\Adminhtml\Integration
{
    /**
     * AJAX integrations grid.
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
