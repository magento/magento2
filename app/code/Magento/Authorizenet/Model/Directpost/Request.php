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
namespace Magento\Authorizenet\Model\Directpost;

/**
 * Authorize.net request model for DirectPost model.
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Request extends \Magento\Framework\Object
{
    /**
     * @var string
     */
    protected $_transKey = null;

    /**
     * Return merchant transaction key.
     * Needed to generate sign.
     *
     * @return string
     */
    protected function _getTransactionKey()
    {
        return $this->_transKey;
    }

    /**
     * Set merchant transaction key.
     * Needed to generate sign.
     *
     * @param string $transKey
     * @return $this
     */
    protected function _setTransactionKey($transKey)
    {
        $this->_transKey = $transKey;
        return $this;
    }

    /**
     * Generates the fingerprint for request.
     *
     * @param string $merchantApiLoginId
     * @param string $merchantTransactionKey
     * @param string $amount
     * @param string $currencyCode
     * @param string $fpSequence An invoice number or random number.
     * @param string $fpTimestamp
     * @return string The fingerprint.
     */
    public function generateRequestSign(
        $merchantApiLoginId,
        $merchantTransactionKey,
        $amount,
        $currencyCode,
        $fpSequence,
        $fpTimestamp
    ) {
        return hash_hmac(
            "md5",
            $merchantApiLoginId . "^" . $fpSequence . "^" . $fpTimestamp . "^" . $amount . "^" . $currencyCode,
            $merchantTransactionKey
        );
    }

    /**
     * Set Authorizenet data to request.
     *
     * @param \Magento\Authorizenet\Model\Directpost $paymentMethod
     * @return $this
     */
    public function setConstantData(\Magento\Authorizenet\Model\Directpost $paymentMethod)
    {
        $this->setXVersion('3.1')->setXDelimData('FALSE')->setXRelayResponse('TRUE');

        $this->setXTestRequest($paymentMethod->getConfigData('test') ? 'TRUE' : 'FALSE');

        $this->setXLogin(
            $paymentMethod->getConfigData('login')
        )->setXType(
            'AUTH_ONLY'
        )->setXMethod(
            \Magento\Authorizenet\Model\Authorizenet::REQUEST_METHOD_CC
        )->setXRelayUrl(
            $paymentMethod->getRelayUrl()
        );

        $this->_setTransactionKey($paymentMethod->getConfigData('trans_key'));
        return $this;
    }

    /**
     * Set entity data to request
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Authorizenet\Model\Directpost $paymentMethod
     * @return $this
     */
    public function setDataFromOrder(
        \Magento\Sales\Model\Order $order,
        \Magento\Authorizenet\Model\Directpost $paymentMethod
    ) {
        $payment = $order->getPayment();

        $this->setXFpSequence($order->getQuoteId());
        $this->setXInvoiceNum($order->getIncrementId());
        $this->setXAmount($payment->getBaseAmountAuthorized());
        $this->setXCurrencyCode($order->getBaseCurrencyCode());
        $this->setXTax(
            sprintf('%.2F', $order->getBaseTaxAmount())
        )->setXFreight(
            sprintf('%.2F', $order->getBaseShippingAmount())
        );

        //need to use strval() because NULL values IE6-8 decodes as "null" in JSON in JavaScript, but we need "" for null values.
        $billing = $order->getBillingAddress();
        if (!empty($billing)) {
            $this->setXFirstName(
                strval($billing->getFirstname())
            )->setXLastName(
                strval($billing->getLastname())
            )->setXCompany(
                strval($billing->getCompany())
            )->setXAddress(
                strval($billing->getStreet(1))
            )->setXCity(
                strval($billing->getCity())
            )->setXState(
                strval($billing->getRegion())
            )->setXZip(
                strval($billing->getPostcode())
            )->setXCountry(
                strval($billing->getCountry())
            )->setXPhone(
                strval($billing->getTelephone())
            )->setXFax(
                strval($billing->getFax())
            )->setXCustId(
                strval($billing->getCustomerId())
            )->setXCustomerIp(
                strval($order->getRemoteIp())
            )->setXCustomerTaxId(
                strval($billing->getTaxId())
            )->setXEmail(
                strval($order->getCustomerEmail())
            )->setXEmailCustomer(
                strval($paymentMethod->getConfigData('email_customer'))
            )->setXMerchantEmail(
                strval($paymentMethod->getConfigData('merchant_email'))
            );
        }

        $shipping = $order->getShippingAddress();
        if (!empty($shipping)) {
            $this->setXShipToFirstName(
                strval($shipping->getFirstname())
            )->setXShipToLastName(
                strval($shipping->getLastname())
            )->setXShipToCompany(
                strval($shipping->getCompany())
            )->setXShipToAddress(
                strval($shipping->getStreet(1))
            )->setXShipToCity(
                strval($shipping->getCity())
            )->setXShipToState(
                strval($shipping->getRegion())
            )->setXShipToZip(
                strval($shipping->getPostcode())
            )->setXShipToCountry(
                strval($shipping->getCountry())
            );
        }

        $this->setXPoNum(strval($payment->getPoNumber()));

        return $this;
    }

    /**
     * Set sign hash into the request object.
     * All needed fields should be placed in the object fist.
     *
     * @return $this
     */
    public function signRequestData()
    {
        $fpTimestamp = time();
        $hash = $this->generateRequestSign(
            $this->getXLogin(),
            $this->_getTransactionKey(),
            $this->getXAmount(),
            $this->getXCurrencyCode(),
            $this->getXFpSequence(),
            $fpTimestamp
        );
        $this->setXFpTimestamp($fpTimestamp);
        $this->setXFpHash($hash);
        return $this;
    }
}
