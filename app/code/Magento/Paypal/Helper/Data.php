<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Helper;

use Magento\Paypal\Model\Billing\Agreement\MethodInterface;

/**
 * Paypal Data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const HTML_TRANSACTION_ID =
        '<a target="_blank" href="https://www.%1$s.paypal.com/cgi-bin/webscr?cmd=_view-a-trans&id=%2$s">%2$s</a>';

    /**
     * Cache for shouldAskToCreateBillingAgreement()
     *
     * @var bool
     */
    protected static $_shouldAskToCreateBillingAgreement = null;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * @var \Magento\Paypal\Model\Billing\AgreementFactory
     */
    protected $_agreementFactory;

    /**
     * @var array
     */
    private $methodCodes;

    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     */
    private $configFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @param array $methodCodes
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        \Magento\Paypal\Model\ConfigFactory $configFactory,
        array $methodCodes
    ) {
        $this->_paymentData = $paymentData;
        $this->_agreementFactory = $agreementFactory;
        $this->methodCodes = $methodCodes;
        $this->configFactory = $configFactory;
        parent::__construct($context);
    }

    /**
     * Check whether customer should be asked confirmation whether to sign a billing agreement
     *
     * @param \Magento\Paypal\Model\Config $config
     * @param int $customerId
     * @return bool
     */
    public function shouldAskToCreateBillingAgreement(\Magento\Paypal\Model\Config $config, $customerId)
    {
        if (null === self::$_shouldAskToCreateBillingAgreement) {
            self::$_shouldAskToCreateBillingAgreement = false;
            if ($customerId && $config->shouldAskToCreateBillingAgreement()) {
                if ($this->_agreementFactory->create()->needToCreateForCustomer($customerId)) {
                    self::$_shouldAskToCreateBillingAgreement = true;
                }
            }
        }
        return self::$_shouldAskToCreateBillingAgreement;
    }

    /**
     * Retrieve available billing agreement methods
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @param \Magento\Quote\Model\Quote|null $quote
     * @return MethodInterface[]
     */
    public function getBillingAgreementMethods($store = null, $quote = null)
    {
        $result = [];
        foreach ($this->_paymentData->getStoreMethods($store, $quote) as $method) {
            if ($method instanceof MethodInterface) {
                $result[] = $method;
            }
        }
        return $result;
    }

    /**
     * Get HTML representation of transaction id
     *
     * @param string $methodCode
     * @param string $txnId
     * @return string
     */
    public function getHtmlTransactionId($methodCode, $txnId)
    {
        if (in_array($methodCode, $this->methodCodes)) {
            /** @var \Magento\Paypal\Model\Config $config */
            $config = $this->configFactory->create()->setMethod($methodCode);
            $sandboxFlag = ($config->getValue('sandboxFlag') ? 'sandbox' : '');
            return sprintf(self::HTML_TRANSACTION_ID, $sandboxFlag, $txnId);
        }
        return $txnId;
    }
}
