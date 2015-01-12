<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Adminhtml sales transactions controller
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Transactions extends \Magento\Backend\App\Action
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
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Initialize payment transaction model
     *
     * @return \Magento\Sales\Model\Order\Payment\Transaction|bool
     */
    protected function _initTransaction()
    {
        $txn = $this->_objectManager->create(
            'Magento\Sales\Model\Order\Payment\Transaction'
        )->load(
            $this->getRequest()->getParam('txn_id')
        );

        if (!$txn->getId()) {
            $this->messageManager->addError(__('Please correct the transaction ID and try again.'));
            $this->_redirect('sales/*/');
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $txn->setOrderUrl($this->getUrl('sales/order/view', ['order_id' => $orderId]));
        }

        $this->_coreRegistry->register('current_transaction', $txn);
        return $txn;
    }

    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'fetch':
                return $this->_authorization->isAllowed('Magento_Sales::transactions_fetch');
                break;
            default:
                return $this->_authorization->isAllowed('Magento_Sales::transactions');
                break;
        }
    }
}
