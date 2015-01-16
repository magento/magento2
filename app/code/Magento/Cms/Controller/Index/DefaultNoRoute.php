<?php
/**
 * Default no route page action
 * Used if no route page don't configure or available
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Index;

class DefaultNoRoute extends \Magento\Framework\App\Action\Action
{
    /**
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
        $this->getResponse()->setHeader('Status', '404 File not found');

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
