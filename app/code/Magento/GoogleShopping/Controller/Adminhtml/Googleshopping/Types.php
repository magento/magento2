<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping;

use Magento\Framework\App\RequestInterface;

/**
 * GoogleShopping Admin Item Types Controller
 */
class Types extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Dispatches controller_action_postdispatch_adminhtml Event
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $response = parent::dispatch($request);
        if (!$this->_actionFlag->get('', self::FLAG_NO_POST_DISPATCH)) {
            $this->_eventManager->dispatch(
                'controller_action_postdispatch_adminhtml',
                ['controller_action' => $this]
            );
        }
        return $response;
    }

    /**
     * Initialize attribute set mapping object
     *
     * @return $this
     */
    protected function _initItemType()
    {
        $this->_coreRegistry->register(
            'current_item_type',
            $this->_objectManager->create('Magento\GoogleShopping\Model\Type')
        );
        $typeId = $this->getRequest()->getParam('id');
        if (!is_null($typeId)) {
            $this->_coreRegistry->registry('current_item_type')->load($typeId);
        }
        return $this;
    }

    /**
     * Initialize general settings for action
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_GoogleShopping::catalog_googleshopping_types'
        )->_addBreadcrumb(
            __('Catalog'),
            __('Catalog')
        )->_addBreadcrumb(
            __('Google Content'),
            __('Google Content')
        );
        return $this;
    }

    /**
     * Get store object, basing on request
     *
     * @return \Magento\Store\Model\Store
     */
    public function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        if ($storeId == 0) {
            $defaultStore = $storeManager->getDefaultStoreView();
            if (!$defaultStore) {
                $allStores = $storeManager->getStores();
                if (isset($allStores[0])) {
                    $defaultStore = $allStores[0];
                }
            }
            return $defaultStore;
        }
        return $storeManager->getStore($storeId);
    }

    /**
     * Check access to this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_GoogleShopping::types');
    }
}
