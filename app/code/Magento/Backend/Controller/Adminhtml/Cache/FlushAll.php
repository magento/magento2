<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class FlushAll extends \Magento\Backend\Controller\Adminhtml\Cache implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Backend::flush_cache_storage';

    /**
     * Flush cache storage
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->_eventManager->dispatch('adminhtml_cache_flush_all');
        /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
        $this->messageManager->addSuccessMessage(__("You flushed the cache storage."));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/*');
    }
}
