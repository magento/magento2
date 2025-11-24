<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Widget\Controller\Adminhtml\Widget\Instance implements HttpGetActionInterface
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
