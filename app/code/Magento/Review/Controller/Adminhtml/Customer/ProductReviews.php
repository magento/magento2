<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerIdProvider;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Layout;

/**
 * Customer product reviews page.
 */
class ProductReviews extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Review::reviews_all';

    /** @var CustomerIdProvider */
    private $customerIdProvider;

    /**
     * @param Context $context
     * @param CustomerIdProvider $customerIdProvider
     */
    public function __construct(Context $context, CustomerIdProvider $customerIdProvider)
    {
        $this->customerIdProvider = $customerIdProvider;
        parent::__construct($context);
    }

    /**
     * Get customer's product reviews list.
     *
     * @return Layout
     */
    public function execute()
    {
        $customerId = $this->customerIdProvider->getCustomerId();
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $block = $resultLayout->getLayout()->getBlock('admin.customer.reviews');
        $block->setCustomerId($customerId)->setUseAjax(true);

        return $resultLayout;
    }
}
