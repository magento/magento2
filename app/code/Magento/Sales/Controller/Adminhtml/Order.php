<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Adminhtml sales orders controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Order extends \Magento\Backend\App\Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var string[]
     */
    protected $_publicActions = ['view', 'index'];

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $_translateInline;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_translateInline = $translateInline;
        parent::__construct($context);
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_Sales::sales_order'
        )->_addBreadcrumb(
            __('Sales'),
            __('Sales')
        )->_addBreadcrumb(
            __('Orders'),
            __('Orders')
        );
        return $this;
    }

    /**
     * Initialize order model instance
     *
     * @return \Magento\Sales\Model\Order|false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($id);

        if (!$order->getId()) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_redirect('sales/*/');
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);
        return $order;
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'hold':
                $aclResource = 'Magento_Sales::hold';
                break;
            case 'unhold':
                $aclResource = 'Magento_Sales::unhold';
                break;
            case 'email':
                $aclResource = 'Magento_Sales::email';
                break;
            case 'cancel':
                $aclResource = 'Magento_Sales::cancel';
                break;
            case 'view':
                $aclResource = 'Magento_Sales::actions_view';
                break;
            case 'addcomment':
                $aclResource = 'Magento_Sales::comment';
                break;
            case 'creditmemos':
                $aclResource = 'Magento_Sales::creditmemo';
                break;
            case 'reviewpayment':
                $aclResource = 'Magento_Sales::review_payment';
                break;
            case 'address':
            case 'addresssave':
                $aclResource = 'Magento_Sales::actions_edit';
                break;
            default:
                $aclResource = 'Magento_Sales::sales_order';
                break;
        }
        return $this->_authorization->isAllowed($aclResource);
    }
}
