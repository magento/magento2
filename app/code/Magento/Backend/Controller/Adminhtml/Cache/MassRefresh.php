<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

class MassRefresh extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * Mass action for cache refresh
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
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

        return $this->getDefaultResult();
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function getDefaultResult()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/*');
    }
}
