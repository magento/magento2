<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Index
 *
 */
class Index extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Orders grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Orders'));
        return $resultPage;
    }
}
