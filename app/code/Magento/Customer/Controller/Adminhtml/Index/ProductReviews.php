<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

class ProductReviews extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Get customer's product reviews list
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('admin.customer.reviews');
        $block->setCustomerId($customerId)->setUseAjax(true);
        return $resultLayout;
    }
}
