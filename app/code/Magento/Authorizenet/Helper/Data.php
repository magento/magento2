<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Authorizenet\Model\Directpost;
use Magento\Authorizenet\Model\Authorizenet;

/**
 * Authorize.net Data Helper
 *
 * @api
 * @since 100.0.2
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Allowed currencies
     *
     * @var array
     */
    protected $allowedCurrencyCodes = ['USD'];

    /**
     * Transaction statuses key to value map
     *
     * @var array
     */
    protected $transactionStatuses = [
        'authorizedPendingCapture' => 'Authorized/Pending Capture',
        'capturedPendingSettlement' => 'Captured/Pending Settlement',
        'refundSettledSuccessfully' => 'Refund/Settled Successfully',
        'refundPendingSettlement' => 'Refund/Pending Settlement',
        'declined' => 'Declined',
        'expired' => 'Expired',
        'voided' => 'Voided',
        'FDSPendingReview' => 'FDS - Pending Review',
        'FDSAuthorizedPendingReview' => 'FDS - Authorized/Pending Review'
    ];

    /**
     * Fraud filter actions key to value map
     *
     * @var array
     */
    protected $fdsFilterActions = [
        'decline ' => 'Decline',
        'hold' => 'Hold',
        'authAndHold' => 'Authorize and Hold',
        'report' => 'Report Only'
    ];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory
    ) {
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * Set secure url checkout is secure for current store.
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _getUrl($route, $params = [])
    {
        $params['_type'] = \Magento\Framework\UrlInterface::URL_TYPE_LINK;
        if (isset($params['is_secure'])) {
            $params['_secure'] = (bool)$params['is_secure'];
        } elseif ($this->storeManager->getStore()->isCurrentlySecure()) {
            $params['_secure'] = true;
        }
        return parent::_getUrl($route, $params);
    }

    /**
     * Retrieve save order url params
     *
     * @param string $controller
     * @return array
     */
    public function getSaveOrderUrlParams($controller)
    {
        $route = [];
        switch ($controller) {
            case 'onepage':
                $route['action'] = 'saveOrder';
                $route['controller'] = 'onepage';
                $route['module'] = 'checkout';
                break;

            case 'sales_order_create':
            case 'sales_order_edit':
                $route['action'] = 'save';
                $route['controller'] = 'sales_order_create';
                $route['module'] = 'admin';
                break;

            default:
                break;
        }

        return $route;
    }

    /**
     * Retrieve redirect iframe url
     *
     * @param array $params
     * @return string
     */
    public function getRedirectIframeUrl($params)
    {
        return $this->_getUrl('authorizenet/directpost_payment/redirect', $params);
    }

    /**
     * Retrieve place order url
     *
     * @param array $params
     * @return  string
     */
    public function getSuccessOrderUrl($params)
    {
        return $this->_getUrl('checkout/onepage/success', $params);
    }

    /**
     * Update all child and parent order's edit increment numbers.
     * Needed for Admin area.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    public function updateOrderEditIncrements(\Magento\Sales\Model\Order $order)
    {
        if ($order->getId() && $order->getOriginalIncrementId()) {
            $collection = $order->getCollection();
            $quotedIncrId = $collection->getConnection()->quote($order->getOriginalIncrementId());
            $collection->getSelect()->where(
                "original_increment_id = {$quotedIncrId} OR increment_id = {$quotedIncrId}"
            );

            foreach ($collection as $orderToUpdate) {
                $orderToUpdate->setEditIncrement($order->getEditIncrement());
                $orderToUpdate->save();
            }
        }
    }

    /**
     * Converts a lot of messages to message
     *
     * @param  array $messages
     * @return string
     */
    public function convertMessagesToMessage($messages)
    {
        return implode(' | ', $messages);
    }

    /**
     * Return message for gateway transaction request
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param string $requestType
     * @param string $lastTransactionId
     * @param \Magento\Framework\DataObject $card
     * @param bool|float $amount
     * @param bool|string $exception
     * @param bool|string $additionalMessage
     * @return bool|string
     */
    public function getTransactionMessage(
        $payment,
        $requestType,
        $lastTransactionId,
        $card,
        $amount = false,
        $exception = false,
        $additionalMessage = false
    ) {
        $message[] = __('Credit Card: xxxx-%1', $card->getCcLast4());
        if ($amount) {
            $message[] = __('amount %1', $this->formatPrice($payment, $amount));
        }
        $operation = $this->getOperation($requestType);
        if (!$operation) {
            return false;
        } else {
            $message[] = $operation;
        }
        $message[] = ($exception) ? '- ' . __('failed.') : '- ' . __('successful.');
        if ($lastTransactionId !== null) {
            $message[] = __('Authorize.Net Transaction ID %1.', $lastTransactionId);
        }
        if ($additionalMessage) {
            $message[] = $additionalMessage;
        }
        if ($exception) {
            $message[] = $exception;
        }
        return implode(' ', $message);
    }

    /**
     * Return operation name for request type
     *
     * @param  string $requestType
     * @return \Magento\Framework\Phrase|bool
     */
    protected function getOperation($requestType)
    {
        switch ($requestType) {
            case Authorizenet::REQUEST_TYPE_AUTH_ONLY:
                return __('authorize');
            case Authorizenet::REQUEST_TYPE_AUTH_CAPTURE:
                return __('authorize and capture');
            case Authorizenet::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
                return __('capture');
            case Authorizenet::REQUEST_TYPE_CREDIT:
                return __('refund');
            case Authorizenet::REQUEST_TYPE_VOID:
                return __('void');
            default:
                return false;
        }
    }

    /**
     * Format price with currency sign
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return string
     */
    protected function formatPrice($payment, $amount)
    {
        return $payment->getOrder()->getBaseCurrency()->formatTxt($amount);
    }

    /**
     * Get payment method step html
     *
     * @param \Magento\Framework\App\ViewInterface $view
     * @return string
     */
    public function getPaymentMethodsHtml(\Magento\Framework\App\ViewInterface $view)
    {
        $layout = $view->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_paymentmethod');
        $layout->generateXml();
        $layout->generateElements();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * Get direct post relay url
     *
     * @param null|int|string $storeId
     * @return string
     */
    public function getRelayUrl($storeId = null)
    {
        $baseUrl = $this->storeManager->getStore($storeId)
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
        return $baseUrl . 'authorizenet/directpost_payment/response';
    }

    /**
     * Get allowed currencies
     *
     * @return array
     */
    public function getAllowedCurrencyCodes()
    {
        return $this->allowedCurrencyCodes;
    }

    /**
     * Get translated filter action label
     *
     * @param string $key
     * @return \Magento\Framework\Phrase|string
     */
    public function getFdsFilterActionLabel($key)
    {
        return isset($this->fdsFilterActions[$key]) ? __($this->fdsFilterActions[$key]) : $key;
    }

    /**
     * Get translated transaction status label
     *
     * @param string $key
     * @return \Magento\Framework\Phrase|string
     */
    public function getTransactionStatusLabel($key)
    {
        return isset($this->transactionStatuses[$key]) ? __($this->transactionStatuses[$key]) : $key;
    }

    /**
     * Gateway error response wrapper
     *
     * @param string $text
     * @return \Magento\Framework\Phrase
     */
    public function wrapGatewayError($text)
    {
        return __('Gateway error: %1', $text);
    }
}
