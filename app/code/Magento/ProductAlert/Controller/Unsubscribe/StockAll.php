<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Controller\Unsubscribe;

use Magento\ProductAlert\Controller\Unsubscribe as UnsubscribeController;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\ProductAlert\Controller\Unsubscribe\StockAll
 *
 */
class StockAll extends UnsubscribeController
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->_objectManager->create(\Magento\ProductAlert\Model\Stock::class)
                ->deleteCustomer(
                    $this->customerSession->getCustomerId(),
                    $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)
                        ->getStore()
                        ->getWebsiteId()
                );
            $this->messageManager->addSuccess(__('You will no longer receive stock alerts.'));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Unable to update the alert subscription.'));
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('customer/account/');
    }
}
