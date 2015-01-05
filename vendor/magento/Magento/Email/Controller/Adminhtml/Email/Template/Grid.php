<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

class Grid extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * Grid action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
