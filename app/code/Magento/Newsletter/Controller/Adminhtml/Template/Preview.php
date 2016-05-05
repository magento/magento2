<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

class Preview extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * Preview Newsletter template
     *
     * @return void|$this
     */
    public function execute()
    {
        $this->_view->loadLayout();

        $data = $this->getRequest()->getParams();
        $isEmptyRequestData = empty($data) || !isset($data['id']);
        $isEmptyPreviewData = !$this->_getSession()->hasPreviewData() || empty($this->_getSession()->getPreviewData());
        
        if ($isEmptyRequestData && $isEmptyPreviewData) {
            $this->_forward('noroute');
            return $this;
        }

        // set default value for selected store
        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManager');
        $defaultStore = $storeManager->getDefaultStoreView();
        if (!$defaultStore) {
            $allStores = $storeManager->getStores();
            if (isset($allStores[0])) {
                $defaultStore = $allStores[0];
            }
        }
        $data['preview_store_id'] = $defaultStore ? $defaultStore->getId() : null;
        $this->_view->getLayout()->getBlock('preview_form')->setFormData($data);
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Newsletter Templates'));
        $this->_view->renderLayout();
    }
}
