<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Transparent;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\Adapter;
use Magento\Payment\Model\Method\TransparentInterface;
use Magento\Checkout\Model\Session;
use Magento\Payment\Model\Config;
use Magento\Framework\View\Element\Template\Context;

/**
 * Transparent form block
 *
 * @api
 */
class Form extends \Magento\Payment\Block\Form\Cc
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var string
     */
    protected $_template = 'Magento_Payment::transparent/form.phtml';

    /**
     * @param Context $context
     * @param Config $paymentConfig
     * @param Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->shouldRender()) {
            return $this->processHtml();
        }

        return '';
    }

    /**
     * Checks whether block should be rendered
     * basing on TransparentInterface presence in checkout session
     *
     * @return bool
     */
    protected function shouldRender()
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quote) {
            $payment = $quote->getPayment();
            return $payment && $payment->getMethodInstance() instanceof TransparentInterface;
        }

        return false;
    }

    /**
     * Initializes method
     *
     * @return void
     */
    protected function initializeMethod()
    {
        $this->setData(
            'method',
            $this->checkoutSession
                ->getQuote()
                ->getPayment()
                ->getMethodInstance()
        );
    }

    /**
     * Parent rendering wrapper
     *
     * @return string
     */
    protected function processHtml()
    {
        $this->initializeMethod();
        return parent::_toHtml();
    }

    /**
     * Get type of request
     *
     * @return bool
     */
    public function isAjaxRequest()
    {
        return $this->getRequest()->getParam('isAjax');
    }

    /**
     * Get delimiter for date
     *
     * @return string
     */
    public function getDateDelim()
    {
        return $this->getMethodConfigData('date_delim');
    }

    /**
     * Get map of cc_code, cc_num, cc_expdate for gateway
     * Returns json formatted string
     *
     * @return string
     */
    public function getCardFieldsMap()
    {
        $keys = ['cccvv', 'ccexpdate', 'ccnum'];
        $ccfields = array_combine($keys, explode(',', $this->getMethodConfigData('ccfields')));
        return json_encode($ccfields);
    }

    /**
     * Retrieve place order url on front
     *
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->_urlBuilder->getUrl(
            $this->getMethodConfigData('place_order_url'),
            [
                '_secure' => $this->getRequest()->isSecure()
            ]
        );
    }

    /**
     * Retrieve gateway url
     *
     * @return string
     */
    public function getCgiUrl()
    {
        return (bool)$this->getMethodConfigData('sandbox_flag')
            ? $this->getMethodConfigData('cgi_url_test_mode')
            : $this->getMethodConfigData('cgi_url');
    }

    /**
     * Retrieve config data value by field name
     *
     * @param string $fieldName
     * @return mixed
     */
    public function getMethodConfigData($fieldName)
    {
        $method = $this->getMethod();
        if ($method instanceof TransparentInterface) {
            return $method->getConfigInterface()->getValue($fieldName);
        }
        return $method->getConfigData($fieldName);
    }

    /**
     * Returns transparent method service
     *
     * @return TransparentInterface
     * @throws LocalizedException
     */
    public function getMethod()
    {
        $method = parent::getMethod();

        if (!$method instanceof TransparentInterface && !$method instanceof Adapter) {
            throw new LocalizedException(
                __('We cannot retrieve the transparent payment method model object.')
            );
        }
        return $method;
    }
}
