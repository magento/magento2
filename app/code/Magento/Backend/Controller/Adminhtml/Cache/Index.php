<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

/**
 * Class \Magento\Backend\Controller\Adminhtml\Cache\Index
 *
 */
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
