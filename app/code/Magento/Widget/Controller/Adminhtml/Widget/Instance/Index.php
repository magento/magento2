<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

/**
 * Class \Magento\Widget\Controller\Adminhtml\Widget\Instance\Index
 *
 */
class Index extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Widget Instances Grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Widgets'));
        $this->_view->renderLayout();
    }
}
