<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Controller\RegistryConstants;

class Wishlist extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Wishlist Action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initCustomer();
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $itemId = (int)$this->getRequest()->getParam('delete');
        if ($customerId && $itemId) {
            try {
                $this->_objectManager->create('Magento\Wishlist\Model\Item')->load($itemId)->delete();
            } catch (\Exception $exception) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($exception);
            }
        }

        $this->_view->getLayout()->getUpdate()->addHandle(strtolower($this->_request->getFullActionName()));
        $this->_view->loadLayoutUpdates();
        $this->prepareDefaultCustomerTitle();
        $this->_view->generateLayoutXml();
        $this->_view->generateLayoutBlocks();
        $this->_view->renderLayout();
    }
}
