<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Tax\Controller\Adminhtml\Rate implements HttpGetActionInterface
{
    /**
     * Show Main Grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->initResultPage();
        $resultPage->addBreadcrumb(__('Manage Tax Rates'), __('Manage Tax Rates'));
        $resultPage->getConfig()->getTitle()->prepend(__('Tax Zones and Rates'));
        return $resultPage;
    }
}
