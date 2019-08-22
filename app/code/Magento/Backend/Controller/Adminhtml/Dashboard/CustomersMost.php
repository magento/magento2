<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

class CustomersMost extends AjaxBlock
{
    /**
     * Gets the list of most active customers
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $output = $this->layoutFactory->create()
            ->createBlock(\Magento\Backend\Block\Dashboard\Tab\Customers\Most::class)
            ->toHtml();
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($output);
    }
}
