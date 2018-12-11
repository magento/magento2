<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Group;

/**
 * Class Index
 *
 * @package Magento\Customer\Controller\Adminhtml\Group
 */
class Index extends \Magento\Customer\Controller\Adminhtml\Group
{
    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("Customer Groups"));
        return $resultPage;
    }
}
