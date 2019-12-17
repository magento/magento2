<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Controller\Adminhtml\Customer;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Customer product reviews page.
 */
class ProductReviews extends \Magento\Customer\Controller\Adminhtml\Index implements HttpPostActionInterface
{
    /**
     * Get customer's product reviews list.
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $customerId = (int)$this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('admin.customer.reviews');
        $block->setCustomerId($customerId)->setUseAjax(true);

        return $resultLayout;
    }
}
