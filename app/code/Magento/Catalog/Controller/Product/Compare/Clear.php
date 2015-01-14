<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product\Compare;

class Clear extends \Magento\Catalog\Controller\Product\Compare
{
    /**
     * Remove all items from comparison list
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Catalog\Model\Resource\Product\Compare\Item\Collection $items */
        $items = $this->_itemCollectionFactory->create();

        if ($this->_customerSession->isLoggedIn()) {
            $items->setCustomerId($this->_customerSession->getCustomerId());
        } elseif ($this->_customerId) {
            $items->setCustomerId($this->_customerId);
        } else {
            $items->setVisitorId($this->_customerVisitor->getId());
        }

        try {
            $items->clear();
            $this->messageManager->addSuccess(__('You cleared the comparison list.'));
            $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare')->calculate();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong  clearing the comparison list.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setRefererOrBaseUrl();
    }
}
