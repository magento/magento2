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
namespace Magento\Sales\Block\Adminhtml\Transactions;

/**
 * Adminhtml transaction detail
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Detail extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Transaction model
     *
     * @var \Magento\Sales\Model\Order\Payment\Transaction
     */
    protected $_txn;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Add control buttons
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_txn = $this->_coreRegistry->registry('current_transaction');
        if (!$this->_txn) {
            return;
        }

        $backUrl = $this->_txn->getOrderUrl() ? $this->_txn->getOrderUrl() : $this->getUrl('sales/*/');
        $this->buttonList->add(
            'back',
            array('label' => __('Back'), 'onclick' => "setLocation('{$backUrl}')", 'class' => 'back')
        );

        if ($this->_authorization->isAllowed(
            'Magento_Sales::transactions_fetch'
        ) && $this->_txn->getOrderPaymentObject()->getMethodInstance()->canFetchTransactionInfo()
        ) {
            $fetchUrl = $this->getUrl('sales/*/fetch', array('_current' => true));
            $this->buttonList->add(
                'fetch',
                array('label' => __('Fetch'), 'onclick' => "setLocation('{$fetchUrl}')", 'class' => 'button')
            );
        }
    }

    /**
     * Retrieve header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __(
            "Transaction # %1 | %2",
            $this->_txn->getTxnId(),
            $this->formatDate(
                $this->_txn->getCreatedAt(),
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM,
                true
            )
        );
    }

    /**
     * Render block html
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->setTxnIdHtml($this->escapeHtml($this->_txn->getTxnId()));

        $this->setParentTxnIdUrlHtml(
            $this->escapeHtml($this->getUrl('sales/transactions/view', array('txn_id' => $this->_txn->getParentId())))
        );

        $this->setParentTxnIdHtml($this->escapeHtml($this->_txn->getParentTxnId()));

        $this->setOrderIncrementIdHtml($this->escapeHtml($this->_txn->getOrder()->getIncrementId()));

        $this->setTxnTypeHtml($this->escapeHtml($this->_txn->getTxnType()));

        $this->setOrderIdUrlHtml(
            $this->escapeHtml($this->getUrl('sales/order/view', array('order_id' => $this->_txn->getOrderId())))
        );

        $this->setIsClosedHtml($this->_txn->getIsClosed() ? __('Yes') : __('No'));

        $createdAt = strtotime(
            $this->_txn->getCreatedAt()
        ) ? $this->formatDate(
            $this->_txn->getCreatedAt(),
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM,
            true
        ) : __(
            'N/A'
        );
        $this->setCreatedAtHtml($this->escapeHtml($createdAt));

        return parent::_toHtml();
    }
}
