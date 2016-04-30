<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

class Index extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * Display cache management grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Backend::system_cache');
        $resultPage->getConfig()->getTitle()->prepend(__('Cache Management'));
        return $resultPage;
    }
}
