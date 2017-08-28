<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

/**
 * Class \Magento\Backend\Controller\Adminhtml\Cache\FlushSystem
 *
 */
class FlushSystem extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * Flush all magento cache
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->clean();
        }
        $this->_eventManager->dispatch('adminhtml_cache_flush_system');
        $this->messageManager->addSuccess(__("The Magento cache storage has been flushed."));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/*');
    }
}
