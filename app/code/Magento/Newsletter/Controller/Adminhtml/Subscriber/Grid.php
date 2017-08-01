<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Subscriber;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Subscriber\Grid
 *
 * @since 2.0.0
 */
class Grid extends \Magento\Newsletter\Controller\Adminhtml\Subscriber
{
    /**
     * Managing newsletter grid
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
