<?php
/**
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
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();
        $websiteId = $this->getRequest()->getParam('website_id');

        // delete an item from cart
        $deleteItemId = $this->getRequest()->getPost('delete');
        if ($deleteItemId) {
            /** @var \Magento\Quote\Model\QuoteRepository $quoteRepository */
            $quoteRepository = $this->_objectManager->create('Magento\Quote\Model\QuoteRepository');
            /** @var \Magento\Quote\Model\Quote $quote */
            try {
                $quote = $quoteRepository->getForCustomer($customerId);
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

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('admin.customer.view.edit.cart')->setWebsiteId($websiteId);
        return $resultLayout;
    }
}
