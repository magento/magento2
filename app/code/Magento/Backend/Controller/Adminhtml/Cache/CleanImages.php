<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\Model\Exception;

class CleanImages extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * Clean JS/css files cache
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->_objectManager->create('Magento\Catalog\Model\Product\Image')->clearCache();
            $this->_eventManager->dispatch('clean_catalog_images_cache_after');
            $this->messageManager->addSuccess(__('The image cache was cleaned.'));
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('An error occurred while clearing the image cache.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/*');
    }
}
