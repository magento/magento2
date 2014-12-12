<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ProductAlert\Controller\Unsubscribe;

class StockAll extends \Magento\ProductAlert\Controller\Unsubscribe
{
    /**
     * @return void
     */
    public function execute()
    {
        try {
            $this->_objectManager->create(
                'Magento\ProductAlert\Model\Stock'
            )->deleteCustomer(
                $this->_customerSession->getCustomerId(),
                $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getWebsiteId()
            );
            $this->messageManager->addSuccess(__('You will no longer receive stock alerts.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }
        $this->_redirect('customer/account/');
    }
}
