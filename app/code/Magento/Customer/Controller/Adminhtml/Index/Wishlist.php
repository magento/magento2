<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class Wishlist extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Wishlist Action
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();
        $itemId = (int)$this->getRequest()->getParam('delete');
        if ($customerId && $itemId) {
            try {
                $this->_objectManager->create('Magento\Wishlist\Model\Item')->load($itemId)->delete();
            } catch (\Exception $exception) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($exception);
            }
        }

        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
