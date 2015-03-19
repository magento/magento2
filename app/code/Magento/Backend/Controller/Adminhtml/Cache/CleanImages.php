<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\Exception\LocalizedException;

class CleanImages extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * Clean JS/css files cache
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->_objectManager->create('Magento\Catalog\Model\Product\Image')->clearCache();
        $this->_eventManager->dispatch('clean_catalog_images_cache_after');
        $this->messageManager->addSuccess(__('The image cache was cleaned.'));

        return $this->getDefaultRedirect();
    }

    /**
     * Redirect user to the previous or main page
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function getDefaultRedirect()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/*');
    }
}
