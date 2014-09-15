<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                array('controller_action' => $this)
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
        $this->_title->add(__('Google Content Attributes'));

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
        if ($storeId == 0) {
            return $this->_objectManager->get('Magento\Framework\StoreManagerInterface')->getDefaultStoreView();
        }
        return $this->_objectManager->get('Magento\Framework\StoreManagerInterface')->getStore($storeId);
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
