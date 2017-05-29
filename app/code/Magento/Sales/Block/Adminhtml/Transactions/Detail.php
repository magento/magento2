<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Adminhtml\Transactions;

use Magento\Sales\Api\OrderPaymentRepositoryInterface;

/**
 * Adminhtml transaction detail
 *
 * @api
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
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;

    /**
     * @var OrderPaymentRepositoryInterface
     */
    protected $orderPaymentRepository;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Sales\Api\OrderPaymentRepositoryInterface $orderPaymentRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        OrderPaymentRepositoryInterface $orderPaymentRepository,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->adminHelper = $adminHelper;
        $this->orderPaymentRepository = $orderPaymentRepository;
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
            ['label' => __('Back'), 'onclick' => "setLocation('{$backUrl}')", 'class' => 'back']
        );

        $fetchTransactionAllowed = $this->_authorization->isAllowed('Magento_Sales::transactions_fetch');
        $canFetchTransaction = $this->orderPaymentRepository->get($this->_txn->getPaymentId())
            ->getMethodInstance()
            ->canFetchTransactionInfo();

        if ($fetchTransactionAllowed && $canFetchTransaction) {
            $fetchUrl = $this->getUrl('sales/*/fetch', ['_current' => true]);
            $this->buttonList->add(
                'fetch',
                ['label' => __('Fetch'), 'onclick' => "setLocation('{$fetchUrl}')", 'class' => 'button']
            );
        }
    }

    /**
     * Retrieve header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __(
            "Transaction # %1 | %2",
            $this->_txn->getTxnId(),
            $this->formatDate(
                $this->_txn->getCreatedAt(),
                \IntlDateFormatter::MEDIUM,
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
        $this->setTxnIdHtml($this->adminHelper->escapeHtmlWithLinks(
            $this->_txn->getHtmlTxnId(),
            ['a']
        ));

        $this->setParentTxnIdUrlHtml(
            $this->escapeHtml($this->getUrl('sales/transactions/view', ['txn_id' => $this->_txn->getParentId()]))
        );

        $this->setParentTxnIdHtml($this->escapeHtml($this->_txn->getParentTxnId()));

        $this->setOrderIncrementIdHtml($this->escapeHtml($this->_txn->getOrder()->getIncrementId()));

        $this->setTxnTypeHtml($this->escapeHtml(__($this->_txn->getTxnType())));

        $this->setOrderIdUrlHtml(
            $this->escapeHtml($this->getUrl('sales/order/view', ['order_id' => $this->_txn->getOrderId()]))
        );

        $this->setIsClosedHtml($this->_txn->getIsClosed() ? __('Yes') : __('No'));

        $createdAt = strtotime(
            $this->_txn->getCreatedAt()
        ) ? $this->formatDate(
            $this->_txn->getCreatedAt(),
            \IntlDateFormatter::MEDIUM,
            true
        ) : __(
            'N/A'
        );
        $this->setCreatedAtHtml($this->escapeHtml($createdAt));

        return parent::_toHtml();
    }
}
