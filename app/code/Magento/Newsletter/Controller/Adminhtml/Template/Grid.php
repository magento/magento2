<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

class Grid extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * JSON Grid Action
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $grid = $this->_view->getLayout()->createBlock('Magento\Newsletter\Block\Adminhtml\Template\Grid')->toHtml();
        $this->getResponse()->setBody($grid);
    }
}
