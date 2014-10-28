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
namespace Magento\Authorizenet\Helper;

/**
 * Authorize.net Data Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper implements HelperInterface
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_orderFactory = $orderFactory;
    }

    /**
     * Set secure url checkout is secure for current store.
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    protected function _getUrl($route, $params = array())
    {
        $params['_type'] = \Magento\Framework\UrlInterface::URL_TYPE_LINK;
        if (isset($params['is_secure'])) {
            $params['_secure'] = (bool)$params['is_secure'];
        } elseif ($this->_storeManager->getStore()->isCurrentlySecure()) {
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
        $route = array();
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
        switch ($params['controller_action_name']) {
            case 'onepage':
                $route = 'authorizenet/directpost_payment/redirect';
                break;

            default:
                $route = 'authorizenet/directpost_payment/redirect';
                break;
        }

        return $this->_getUrl($route, $params);
    }

    /**
     * Retrieve place order url on front
     *
     * @return  string
     */
    public function getPlaceOrderFrontUrl()
    {
        return $this->_getUrl('authorizenet/directpost_payment/place');
    }

    /**
     * Retrieve place order url
     *
     * @param array $params
     * @return  string
     */
    public function getSuccessOrderUrl($params)
    {
        $param = array();
        switch ($params['controller_action_name']) {
            case 'onepage':
                $route = 'checkout/onepage/success';
                break;

            default:
                $route = 'checkout/onepage/success';
                break;
        }

        return $this->_getUrl($route, $param);
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
     * @param \Magento\Payment\Model\Info $payment
     * @param string $requestType
     * @param string $lastTransactionId
     * @param \Magento\Framework\Object $card
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
            $message[] = __('amount %1', $this->_formatPrice($payment, $amount));
        }
        $operation = $this->_getOperation($requestType);
        if (!$operation) {
            return false;
        } else {
            $message[] = $operation;
        }
        $message[] = ($exception) ? '- ' . __('failed.') : '- ' . __('successful.');
        if (!is_null($lastTransactionId)) {
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
     * @return bool|string
     */
    protected function _getOperation($requestType)
    {
        switch ($requestType) {
            case \Magento\Authorizenet\Model\Authorizenet::REQUEST_TYPE_AUTH_ONLY:
                return __('authorize');
            case \Magento\Authorizenet\Model\Authorizenet::REQUEST_TYPE_AUTH_CAPTURE:
                return __('authorize and capture');
            case \Magento\Authorizenet\Model\Authorizenet::REQUEST_TYPE_PRIOR_AUTH_CAPTURE:
                return __('capture');
            case \Magento\Authorizenet\Model\Authorizenet::REQUEST_TYPE_CREDIT:
                return __('refund');
            case \Magento\Authorizenet\Model\Authorizenet::REQUEST_TYPE_VOID:
                return __('void');
            default:
                return false;
        }
    }

    /**
     * Format price with currency sign
     * @param  \Magento\Payment\Model\Info $payment
     * @param float $amount
     * @return string
     */
    protected function _formatPrice($payment, $amount)
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
     * Get direct post rely url
     *
     * @param null|int|string $storeId
     * @return string
     */
    public function getRelyUrl($storeId = null)
    {
        return $this->_storeManager->getStore(
            $storeId
        )->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_LINK
        ) . 'authorizenet/directpost_payment/response';
    }
}
