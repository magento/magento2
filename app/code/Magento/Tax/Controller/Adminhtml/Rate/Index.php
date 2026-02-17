<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
