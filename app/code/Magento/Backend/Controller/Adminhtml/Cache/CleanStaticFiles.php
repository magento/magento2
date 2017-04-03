<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\Controller\ResultFactory;

class CleanStaticFiles extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * Clean static files cache
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->_objectManager->get(\Magento\Framework\App\State\CleanupFiles::class)->clearMaterializedViewFiles();
        $this->_eventManager->dispatch('clean_static_files_cache_after');
        $this->messageManager->addSuccess(__('The static files cache has been cleaned.'));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*');
    }
}
