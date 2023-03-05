<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;
use Magento\Wishlist\Controller\AbstractIndex;

class Share extends AbstractIndex
{
    /**
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        protected readonly Session $customerSession
    ) {
        parent::__construct($context);
    }

    /**
     * Prepare wishlist for share
     *
     * @return void|Page
     */
    public function execute()
    {
        if ($this->customerSession->authenticate()) {
            /** @var Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            return $resultPage;
        }
    }
}
