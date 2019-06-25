<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Get most viewed products controller.
 */
class ProductsViewed extends AjaxBlock implements HttpPostActionInterface
{
    /**
     * Gets most viewed products list
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $output = $this->layoutFactory->create()
            ->createBlock(\Magento\Backend\Block\Dashboard\Tab\Products\Viewed::class)
            ->toHtml();
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($output);
    }
}
