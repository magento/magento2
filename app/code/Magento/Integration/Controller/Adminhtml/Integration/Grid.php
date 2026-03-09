<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Integration\Controller\Adminhtml\Integration as IntegrationAction;

class Grid extends IntegrationAction implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * AJAX integrations grid.
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
