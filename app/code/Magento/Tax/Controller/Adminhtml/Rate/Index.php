<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

class Index extends \Magento\Tax\Controller\Adminhtml\Rate
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
