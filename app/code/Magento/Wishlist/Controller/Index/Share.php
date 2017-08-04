<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Wishlist\Controller\Index\Share
 *
 */
class Share extends \Magento\Wishlist\Controller\AbstractIndex
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Prepare wishlist for share
     *
     * @return void|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->customerSession->authenticate()) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            return $resultPage;
        }
    }
}
