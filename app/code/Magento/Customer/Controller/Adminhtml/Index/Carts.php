<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

/**
 * Class \Magento\Customer\Controller\Adminhtml\Index\Carts
 *
 */
class Carts extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Get shopping carts from all websites for specified client
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
