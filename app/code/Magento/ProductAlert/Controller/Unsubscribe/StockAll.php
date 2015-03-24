<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Controller\Unsubscribe;

class StockAll extends \Magento\ProductAlert\Controller\Unsubscribe
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $this->_objectManager->create(
            'Magento\ProductAlert\Model\Stock'
        )->deleteCustomer(
            $this->_customerSession->getCustomerId(),
            $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getWebsiteId()
        );
        $this->messageManager->addSuccess(__('You will no longer receive stock alerts.'));

        return $this->getDefaultRedirect();
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function getDefaultRedirect()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('customer/account/');
    }
}
