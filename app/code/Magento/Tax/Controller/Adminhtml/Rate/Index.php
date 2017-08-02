<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Controller\Adminhtml\Rate;

/**
 * Class \Magento\Tax\Controller\Adminhtml\Rate\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Tax\Controller\Adminhtml\Rate
{
    /**
     * Show Main Grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        $resultPage = $this->initResultPage();
        $resultPage->addBreadcrumb(__('Manage Tax Rates'), __('Manage Tax Rates'));
        $resultPage->getConfig()->getTitle()->prepend(__('Tax Zones and Rates'));
        return $resultPage;
    }
}
