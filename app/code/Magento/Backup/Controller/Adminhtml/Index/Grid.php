<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

class Grid extends \Magento\Backup\Controller\Adminhtml\Index
{
    /**
     * Backup list action
     *
     * @return void
     */
    public function execute()
    {
        $this->renderLayot(false);
        $this->_view->renderLayout();
    }
}
