<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\Model\Exception;

class MassRefresh extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * Mass action for cache refresh
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $types = $this->getRequest()->getParam('types');
            $updatedTypes = 0;
            if (!is_array($types)) {
                $types = [];
            }
            $this->_validateTypes($types);
            foreach ($types as $type) {
                $this->_cacheTypeList->cleanType($type);
                $this->_eventManager->dispatch('adminhtml_cache_refresh_type', ['type' => $type]);
                $updatedTypes++;
            }
            if ($updatedTypes > 0) {
                $this->messageManager->addSuccess(__("%1 cache type(s) refreshed.", $updatedTypes));
            }
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('An error occurred while refreshing cache.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/*');
    }
}
