<?php
/**
 * Default no route page action
 * Used if no route page don't configure or available
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
