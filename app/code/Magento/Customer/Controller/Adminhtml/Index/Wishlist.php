<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
                $this->_objectManager->create(\Magento\Wishlist\Model\Item::class)->load($itemId)->delete();
            } catch (\Exception $exception) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($exception);
            }
        }

        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
