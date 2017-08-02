<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

/**
 * Class \Magento\Customer\Controller\Adminhtml\Index\Orders
 *
 * @since 2.0.0
 */
class Orders extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer orders grid
     *
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
