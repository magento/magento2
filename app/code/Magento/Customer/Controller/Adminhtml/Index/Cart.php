<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Customer\Controller\RegistryConstants;

class Cart extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Handle and then get cart grid contents
     *
     * @return void
     */
    public function execute()
    {
        $this->_initCustomer();
        $websiteId = $this->getRequest()->getParam('website_id');

        // delete an item from cart
        $deleteItemId = $this->getRequest()->getPost('delete');
        if ($deleteItemId) {
            /** @var \Magento\Sales\Model\QuoteRepository $quoteRepository */
            $quoteRepository = $this->_objectManager->create('Magento\Sales\Model\QuoteRepository');
            /** @var \Magento\Sales\Model\Quote $quote */
            try {
                $quote = $quoteRepository->getForCustomer(
                    $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
                );
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $quote = $quoteRepository->create();
            }
            $quote->setWebsite(
                $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getWebsite($websiteId)
            );
            $item = $quote->getItemById($deleteItemId);
            if ($item && $item->getId()) {
                $quote->removeItem($deleteItemId);
                $quoteRepository->save($quote->collectTotals());
            }
        }

        $this->_view->loadLayout();
        $this->prepareDefaultCustomerTitle();
        $this->_view->getLayout()->getBlock('admin.customer.view.edit.cart')->setWebsiteId($websiteId);
        $this->_view->renderLayout();
    }
}
