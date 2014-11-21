<?php
/**
 *
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
namespace Magento\Ogone\Controller\Api;

use \Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class Placeform extends \Magento\Ogone\Controller\Api
{
    /**
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Sales\Model\OrderFactory $salesOrderFactory
     * @param OrderSender $orderSender
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        OrderSender $orderSender,
        \Magento\Sales\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        parent::__construct(
            $context,
            $transactionFactory,
            $salesOrderFactory,
            $orderSender
        );
    }

    /**
     * Load place from layout to make POST on Ogone
     *
     * @return void
     */
    public function execute()
    {
        $lastIncrementId = $this->_getCheckout()->getLastRealOrderId();
        if ($lastIncrementId) {
            $order = $this->_salesOrderFactory->create()->loadByIncrementId($lastIncrementId);
            if ($order->getId()) {
                $order->setState(
                    \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT,
                    \Magento\Ogone\Model\Api::PENDING_OGONE_STATUS,
                    __('Start Ogone Processing')
                );
                $order->save();

                $this->_getApi()->debugOrder($order);
            }
        }

        $this->_getCheckout()->getQuote()->setIsActive(false);
        $this->quoteRepository->save($this->_getCheckout()->getQuote());
        $this->_getCheckout()->setOgoneQuoteId($this->_getCheckout()->getQuoteId());
        $this->_getCheckout()->setOgoneLastSuccessQuoteId($this->_getCheckout()->getLastSuccessQuoteId());
        $this->_getCheckout()->clearQuote();

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
