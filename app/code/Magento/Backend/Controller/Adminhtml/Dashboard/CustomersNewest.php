<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

/**
 * Class \Magento\Backend\Controller\Adminhtml\Dashboard\CustomersNewest
 *
 */
class CustomersNewest extends AjaxBlock
{
    /**
     * Gets latest customers list
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $output = $this->layoutFactory->create()
            ->createBlock(\Magento\Backend\Block\Dashboard\Tab\Customers\Newest::class)
            ->toHtml();
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($output);
    }
}
