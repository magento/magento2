<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Magento\Backend\Controller\Adminhtml\Cache implements HttpGetActionInterface
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
